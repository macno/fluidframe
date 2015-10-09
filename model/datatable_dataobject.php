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

    function getColumnAlias(){
        return array();
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
        $operators = array(
            'is'=>"",
            'not'=>"",
            'like'=>"",
            'eq'=>"",
            'gt'=>"",
            'lt'=>"",
            'gte'=>"",
            'lte'=>""
        );
        $columnAlias = $this->getColumnAlias();
        $schemaDef = $this->schemaDef();
        $operatorParams = explode(":",$operator);
        $sql = '';
        $tableStruct = static::getAdminTableStruct();
        switch (count($operatorParams)){
            // in caso di una stringa semplica la vado a cercare in tutti i campi testuali che siano searchable
            case 1: $cnt = 0; foreach($tableStruct as $colName=>$col){
                        if(isset($col['searchable']) && ($col['searchable'])){
                            if(($schemaDef['fields'][$colName]['type'] == 'varchar') ||
                                ($schemaDef['fields'][$colName]['type'] == 'text')){
                                $sql .= ($cnt++ == 0) ? ' ' : ' OR ';
                                $sql .= 'lower('.$colName .') like \'%'. strtolower($operatorParams[0]) .'%\'';
                            }
                        }
                    }
                    break;
            // in caso di <colonna>:<valore> faccio una ricerca sulla colonna con l'operatore di default per il valore
            case 2: $colName=$operatorParams[0]; $value=$operatorParams[1];
                    common_debug("colName: ".$colName." value: ".$value);
                    $searchable = false;
                    $colFound = false;
                    if(isset($tableStruct[$colName])){
                        if(isset($tableStruct[$colName]['searchable']) && ($tableStruct[$colName]['searchable'])){
                            $searchable = true;
                        }
                        $colFound = true;
                        break;
                    }

                    if($searchable){
                        common_debug("Searchable.");
                        switch ($schemaDef['fields'][$colName]['type']){
                            case 'text':
                            case 'varchar' :$sql .= 'lower('.$colName .') '. $defaultAction[$schemaDef['fields'][$colName]['type']] .' \'%'. strtolower($operatorParams[1]) .'%\'';
                                            break;
                            case 'tinyint' :$sql .= $colName .' '. $defaultAction[$schemaDef['fields'][$colName]['type']] .' ';
                                            $sql .= $aliasValue[$schemaDef['fields'][$colName]['type']][strtolower($operatorParams[1])];
                                            break;
                        }
                    }else if(!$colFound){
                        common_debug("Operatore?: ".print_r($operators,true));
                        if(isset($operators[$colName])){
                            $operator=$colName;
                            $colName=(isset($columnAlias[$value])) ? $columnAlias[$value] : $value;
                            common_debug("Operatore: ".$operator." Colonna: ".$colName);
                            switch ($operator){
                                case 'is': if($schemaDef['fields'][$colName]['type'] == 'tinyint'){
                                                $sql .= $colName . ' = 1';
                                            }
                                            break;
                                case 'not': if($schemaDef['fields'][$colName]['type'] == 'tinyint'){
                                                $sql .= $colName . ' = 0';
                                            }
                                            break;
                            }
                        }
                    }
                    break;
            // in caso di <colonna>:<operatore>:<valore> faccio una ricerca sulla colonna con l'operatore per il valore
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
        
        foreach ($tableCols as $colName=>$tableCol) {
            if(!isset($tableCol['visible']) || $tableCol['visible']) {
                $sqlCols[] = $colName;
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
