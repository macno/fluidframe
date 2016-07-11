<?php

abstract class DataTable_DataObject extends Managed_DataObject {
    var $colAliases,
        $foreignTables;
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
            $qryWhere = " where (".$qryWhere." )";
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
            'is'=>"=",
            'not'=>"!=",
            'like'=>"",
            'eq'=>"",
            'gt'=>"",
            'lt'=>"",
            'gte'=>"",
            'lte'=>""
        );
        $columnAlias = $this->getColumnAlias();
        $thisSchema = $this->schemaDef();
        $operatorParams = explode(":",$operator);
        $sql = '';
        $tableStruct = static::getAdminTableStruct();
        switch (count($operatorParams)){
            // in caso di una stringa semplica la vado a cercare in tutti i campi testuali che siano searchable
            case 1: $cnt = 0; foreach($tableStruct as $colName=>$col){
                        if(isset($col['searchable']) && ($col['searchable'])){
                            if(!isset($col['queryable']) || ($col['queryable']==true)){
                                // Il campo fa parte della tabella principale
                                $tableName=$this->__table;
                                $schemaDef=$thisSchema;
                                $insideName = $colName;
                            }else{
                                list($tableName,$insideName) = explode('|',$colName);
                                $schemaDef = call_user_func(array(ucfirst($tableName),'schemaDef'));
                            }
                            if(($schemaDef['fields'][$insideName]['type'] == 'varchar') ||
                                ($schemaDef['fields'][$insideName]['type'] == 'text')){
                                $sql .= ($cnt++ == 0) ? ' ' : ' OR ';
                                $sql .= 'lower('.$tableName.'.'.$insideName .') like \'%'. strtolower($operatorParams[0]) .'%\'';
                            }
                        }
                    }
                    break;
            // in caso di <colonna>:<valore> faccio una ricerca sulla colonna con l'operatore di default per il valore
            case 2: $colName=$operatorParams[0]; $value=$operatorParams[1];
                    $searchable = false;
                    $colFound = false;
                    // la colonna è "searchable" ?
                    if(isset($tableStruct[$colName])){
                        if(isset($tableStruct[$colName]['searchable']) && ($tableStruct[$colName]['searchable'])){
                            $searchable = true;
                        }
                        $colFound = true;
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
                    }else if(!$colFound){
                        if(isset($operators[$colName])){
                            if($operators[$colName]!=""){
                                $operator=$operators[$colName];
                            }else{
                                $operator=$colName;
                            }
                            if(isset($columnAlias[$value])){
                                $colName=$columnAlias[$value][0];
                                $value=$columnAlias[$value][1];
                            }else{
                                $colName=$value;
                                // in questo caso $value assume un valore di default
                                $value = 1;
                            }
                            if($schemaDef['fields'][$colName]['type'] == 'tinyint'){
                                $sql .= $colName . ' ' . $operator . ' ' .$value;
                            }
                        }
                    }
                    break;
            // in caso di <colonna>:<operatore>:<valore> faccio una ricerca sulla colonna con l'operatore per il valore
            case 3: $colName=$operatorParams[0]; $operator=$operatorParams[1]; $value=$operatorParams[2];
                    $searchable = false;
                    // la colonna è "searchable" ?
                    if(isset($tableStruct[$colName])){
                        if(isset($tableStruct[$colName]['searchable']) && ($tableStruct[$colName]['searchable'])){
                            if(isset($operators[$operator])){
                                if($operators[$operator]!=""){
                                    $operator=$operators[$operator];
                                }
                                if($operator == "like"){
                                    $value = "%".$value."%";
                                }
                                switch ($schemaDef['fields'][$colName]['type']){
                                    case 'text':
                                    case 'varchar' :$sql .= 'lower('.$colName .') '. $operator .' \''. strtolower($value) .'\'';
                                                    break;
                                    case 'tinyint' :$sql .= $colName .' '. $operator .' '. $value;
                                                    break;
                                }
                            }
                        }
                    }
                    break;
        }
        return $sql;
    }

    static function getAdminTableStruct(){
    }

    function getTableData($args){
        $tableParams=$this->parse_args($args);

        $qry = "select count(*) as conta from ".$this->__table;
        $this->query($qry);
        $this->fetch();
        $recordsTotal=$this->conta;

        $tableCols = static::getAdminTableStruct();
        $schemaDef = static::schemaDef();
        $this->foreignTables = array();
        if(isset($schemaDef['foreign keys'])){
            foreach($schemaDef['foreign keys'] as $fkey=>$fdata){
                $this->foreignTables[$fdata[0]]=$fdata[1];
            }
        }
        // common_debug("FT: ".print_r($this->foreignTables,true));

        $sqlCols = array();

        $qryFromArray=array($this->__table => true);
        $qryWhereJoinArray=array();
        $this->colAliases=array();
        foreach ($tableCols as $colName=>$tableCol) {
            if(!isset($tableCol['queryable']) || ($tableCol['queryable']==true)){
                $colFullName=$this->__table.'.'.$colName;
                $colAlias=str_replace('.','_',$colFullName);
                $sqlCols[] = $colFullName .' as '. $colAlias;
                $this->colAliases[$colName]=$colAlias;
            }else{
                list($tableName) = explode("|",$colName);
                $colFullName=str_replace('|','.',$colName);
                $colAlias=str_replace('|','_',$colName);
                $sqlCols[] = $colFullName .' as '. $colAlias;
                $this->colAliases[$colName]=$colAlias;
                $qryFromArray[$tableName]=true;
                $localCols=array_keys($this->foreignTables[$tableName]);
                $localCol=$localCols[0];
                $remoteCol=$this->foreignTables[$tableName][$localCol];
                $tmpJoin=$this->__table.'.'.$localCol.' = '.$tableName.'.'.$remoteCol;
                $qryWhereJoinArray[$tmpJoin]=true;
            }
        }
        $qryFrom=implode(', ',array_keys($qryFromArray));
        $qryWhereJoin=implode(', ',array_keys($qryWhereJoinArray));

        $qry = "select " . implode(",", $sqlCols) . " from ".$qryFrom;

        $qryWhere="";
        if(!empty($tableParams['search'])){
            $qryWhere = $this->searchToSQL($tableParams['search']);
            $qry .= $qryWhere;
            $qryFiltered = "select count(*) as conta from ".$this->__table;
            $qryFiltered .= $qryWhere;
            $qryFiltered .= $qryWhereJoin;
            $this->query($qryFiltered);
            $this->fetch();
            $recordsFiltered=$this->conta;
        }else{
            $recordsFiltered=$recordsTotal;
        }
        $qry .= (($qryWhere == '') ? (($qryWhereJoin == '') ? '' : ' where ') : (($qryWhereJoin == '') ? '' :' AND ' . $qryWhereJoin));

        $qry .= " order by";
        $cnt=0;
        foreach($tableParams['order'] as $order){
            $qry .= ($cnt++ == 0) ? ' ' : ', ';
            $qry .= $this->colAliases[$tableParams['columns'][(int)$order['column']]['data']] . " " . $order['dir'];
        }
        $qry .= " limit ".$tableParams['length'] . " offset ". $tableParams['start'] ;

        // common_debug("SQL: ".$qry);
        try {
            $this->query($qry);
        } catch (Exception $ex) {
            $this->serverError($ex->getMessage());
        }
        $objs = array();
        while($this->fetch()){
            $row = array();
            foreach ($this->colAliases as $colName=>$colAlias) {
                $row[$colName] = $this->{$colAlias};
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

    function validateData($validationRules){
        $result = array(
            // 'fieldId'=> 'errorMessage'
        );
        foreach($validationRules as $fieldName=>$rules){
            foreach( $rules as $rule=>$extra ){
                if($rule == 'required'){
                    if($extra === ""){
                        $result[$fieldName]="Campo obbligatorio";
                        continue;
                    }
                }
                if($rule == 'unique'){
                    $obj->$fieldName = $extra;
                    if($obj->find()){
                        $result[$fieldName]="Esiste un altro elemento con lo stesso valore";
                    }
                }
            }
        }
        return $result;
    }
    static function doValidateData($fields, $orig = false){
        $result = array(
            // 'fieldId'=> 'errorMessage'
        );
        $validationRules = array();
        foreach( static::getAdminTableStruct() as $fieldName=>$keys){
            if($fieldName != 'id'){
                foreach( $keys as $key=>$rules){
                    if($key == "rules"){
                        foreach( $rules as $rule=>$extra ){
                            // gestione campi richiesti
                            if(($rule == 'required')&&($extra)){
                                $validationRules[$fieldName][$rule] =
                                    $fields[ $fieldName ];
                            }
                            if(($rule == 'unique')&&($extra)){
                                $validationRules[$fieldName][$rule] =
                                    $fields[ $fieldName ];
                            }
                        }
                    }
                }
            }
        }
        foreach($validationRules as $fieldName=>$rules){
            foreach( $rules as $rule=>$extra ){
                if($rule == 'required'){
                    if($extra === ""){
                        $result[$fieldName]="Campo obbligatorio";
                        continue;
                    }
                }
                if($rule == 'unique'){
                    $obj = new static();
                    $obj->$fieldName = $extra;
                    if($obj->find(true)){
                        if($orig){
                            $pk = $orig->schemaDef()['primary key'][0];
                            if($orig->$pk != $obj->$pk){
                                $result[$fieldName]="Esiste un altro elemento con lo stesso valore";
                            }
                        }else{
                            $result[$fieldName]="Esiste un altro elemento con lo stesso valore";
                        }
                    }
                    $obj->free();
                }
            }
        }
        return $result;
    }
}
