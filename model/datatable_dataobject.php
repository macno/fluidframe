<?php

abstract class DataTable_DataObject extends Managed_DataObject {
    private function parse_args($args){
        $tableParams = array();
        $tableParams['draw']=$args['draw'];
        $tableParams['columns']=$args['columns'];
        $tableParams['order']=$args['order'];
        $tableParams['start']=$args['start'];
        $tableParams['length']=$args['length'];
        $tableParams['search']=$args['search']['value'];
        return $tableParams;
    }

    private function searchToSQL($search){
        $cnt=0;
        $qryWhere = '';
        foreach(explode(" ",$search) as $searchOperator){
            $searchOperator = trim($searchOperator);
            if($searchOperator != ""){
                $tmpQry = $this->operatorToSQL($searchOperator);
                if($tmpQry != ""){
                    $qryWhere .= ($cnt++ == 0) ? ' ' : ' AND ';
                    $qryWhere .= $tmpQry;
                }
            }
        }
        if(strlen($qryWhere) > 0){
            $qryWhere = " where".$qryWhere;
        }
        return $qryWhere;
    }

    private function operatorToSQL($operator){
        $defaultAction = array(
            'varchar' => 'like',
            'text' => 'like',
            'tinyint' => '='
        );
        $aliasValue = array(
            'tinyint' => array(
                'active'=> 1,
                'on'=> 1,
                'off'=> 0,
                'inactive'=> 0
            )
        );
        $columnAlias = array(
            'is' => 'status'
        );
        $schemaDef = $this->schemaDef();
        $operatorParams = explode(":",$operator);
        $sql = '';
        switch (count($operatorParams)){
            case 1: $cnt = 0; foreach(static::getAdminTableStruct() as $col){
                        if(isset($col['searchable']) && ($col['searchable'])){
                            if(($schemaDef['fields'][$col['name']]['type'] == 'varchar') ||
                                ($schemaDef['fields'][$col['name']]['type'] == 'text')){
                                $sql .= ($cnt++ == 0) ? ' ' : ' OR ';
                                $sql .= 'lower('.$col['name'] .') like \'%'. strtolower($operatorParams[0]) .'%\'';
                            }
                        }
                    }
                    break;
            case 2: $colName=$operatorParams[0]; $value=$operatorParams[1];
                    $colName=(isset($columnAlias[$colName])) ? $columnAlias[$colName] : $colName;
                    $searchable = false;
                    foreach(static::getAdminTableStruct() as $col){
                        if(isset($col['searchable']) && ($col['searchable']) && ($col['name']==$colName)){
                            $searchable = true;
                            break;
                        }
                    }

                    if($searchable){
                        switch ($schemaDef['fields'][$colName]['type']){
                            case 'text':
                            case 'varchar' :$sql .= 'lower('.$colName .') '. $defaultAction[$schemaDef['fields'][$colName]['type']] .' \'%'. strtolower($operatorParams[1]) .'%\'';
                                            break;
                            case 'tinyint' :$sql .= $colName .' '. $defaultAction[$schemaDef['fields'][$colName]['type']] .' ';
                                            $sql .= $aliasValue[$schemaDef['fields'][$colName]['type']][strtolower($operatorParams[1])];
                                            break;
                        }
                    }
                    break;
            // case 3: 
            //         break;
        }
        common_debug("SQL: ".$sql);
        return $sql;
    }

    static function getAdminTableStruct(){
    }

    function getTableData($args){
        $tableParams=$this->parse_args($args);
        $tableCols = static::getAdminTableStruct();
        
        $sqlCols = array();
        
        foreach ($tableCols as $tableCol) {
            if(!isset($tableCol['visible']) || $tableCol['visible']) {
                $sqlCols[] = $tableCol['name'];
            }
        }
        
        $qry = "select count(*) as conta from ".$this->__table;
        $this->query($qry);
        $this->fetch();
        $recordsTotal=$this->conta;

        $qry = "select " . implode(",", $sqlCols) . " from ".$this->__table;
        $qryWhere="";
        if(!empty($tableParams['search'])){
            $qryWhere = $this->searchToSQL($tableParams['search']);
            $qry .= $qryWhere;
            $qryFiltered = "select count(*) as conta from ".$this->__table;
            $qryFiltered .= $qryWhere;
            $this->query($qryFiltered);
            $this->fetch();
            $recordsFiltered=$this->conta;
        }else{
            $recordsFiltered=$recordsTotal;
        }

        $qry .= " order by";
        $cnt=0;
        foreach($tableParams['order'] as $order){
            $qry .= ($cnt++ == 0) ? ' ' : ', ';
            $qry .= $tableParams['columns'][(int)$order['column']]['data'] . " " . $order['dir'];
        }
        $qry .= " offset ". $tableParams['start'] ." limit ".$tableParams['length'];

        common_debug("SQL: ".$qry);
        try {
            $this->query($qry);
        } catch (Exception $ex) {
            $this->serverError($ex->getMessage());
        }
        $objs = array();
        while($this->fetch()){
            $row = array();
            foreach ($sqlCols as $sqlCol) {
                $row[$sqlCol] = $this->{$sqlCol};
            }
            $objs[]=$row;
        }
        return array(
            'draw'=>(int)$tableParams['draw'],
            'recordsTotal' => (int)$recordsTotal,
            'recordsFiltered' => (int)$recordsFiltered,
            'data'=>$objs
        );
    }
}
