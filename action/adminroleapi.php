<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdminroleapiAction extends Sbadmin2Action {

    var $tableParams = array();

    function prepare($args) {
        parent::prepare($args);
        $this->tableParams['draw']=$args['draw'];
        $this->tableParams['columns']=$args['columns'];
        $this->tableParams['order']=$args['order'];
        $this->tableParams['start']=$args['start'];
        $this->tableParams['length']=$args['length'];
        $this->tableParams['search']=$args['search']['value'];
        return true;
    }

    function handle() {
        parent::handle();
        header('Content-Type: application/json; charset=utf-8');
        $this->API();
    }

    function searchToSQL(&$obj,$search){
        $defaultAction = array(
            'varchar' => 'like',
            'text' => 'like',
            'tinyint' => '='
        );
        $aliasValue = array(
            'tinyint' => array(
                'active'=> 1,
                'inactive'=> 0
            )
        );
        $schemaDef = $obj->schemaDef();
        $searchParams = explode(":",$search);
        $sql = '';
        switch (count($searchParams)){
            case 1: $cnt = 0; foreach($obj->getAdminTableStruct() as $col){
                        if(isset($col['searchable']) && ($col['searchable'])){
                            if(($schemaDef['fields'][$col['name']]['type'] == 'varchar') ||
                                ($schemaDef['fields'][$col['name']]['type'] == 'text')){
                                $sql .= ($cnt++ == 0) ? ' ' : ' OR ';
                                $sql .= 'lower('.$col['name'] .') like \'%'. strtolower($searchParams[0]) .'%\'';
                            }
                        }
                    }
                    break;
            case 2: $col=$searchParams[0]; $value=$searchParams[1];
                    switch ($schemaDef['fields'][$col]['type']){
                        case 'text':
                        case 'varchar' :$sql .= 'lower('.$col .') '. $defaultAction[$schemaDef['fields'][$col]['type']] .' \'%'. strtolower($searchParams[1]) .'%\'';
                                        break;
                        case 'tinyint' :$sql .= $col .' '. $defaultAction[$schemaDef['fields'][$col]['type']] .' ';
                                        $sql .= $aliasValue[$schemaDef['fields'][$col]['type']][strtolower($searchParams[1])];
                                        break;
                    }
                    break;
            // case 3: 
            //         break;
        }
        common_debug("SQL: ".$sql);
        return $sql;
    }

    function API(){
        // common_debug("Params: ".print_r($this->tableParams['columns'],true));
        $role = new Role();
        
        $tableCols = Role::getAdminTableStruct();
        
        $sqlCols = array();
        
        foreach ($tableCols as $tableCol) {
            if(!isset($tableCol['visible']) || $tableCol['visible']) {
                $sqlCols[] = $tableCol['name'];
            }
        }
        
        $qry = "select count(*) as conta from ".$role->__table;
        $role->query($qry);
        $role->fetch();
        $recordsTotal=$role->conta;

        $qry = "select " . implode(",", $sqlCols) . " from ".$role->__table;
        $qryWhere = '';
        if(!empty($this->tableParams['search'])){
            $qryWhere = $this->searchToSQL($role,$this->tableParams['search']);
            // $qry .= $this->searchToSQL($role,'admin');
            if(strlen($qryWhere) > 0){
                $qry .= " where ";
                $qry .= $qryWhere ;
            }
        }

        $qry .= " order by";
        $cnt=0;
        foreach($this->tableParams['order'] as $order){
            $qry .= ($cnt++ == 0) ? ' ' : ', ';
            $qry .= $this->tableParams['columns'][(int)$order['column']]['data'] . " " . $order['dir'];
        }
        $qry .= " offset ". $this->tableParams['start']*$this->tableParams['length'] ." limit ".$this->tableParams['length'];

        common_debug("SQL: ".$qry);
        try {
            $role->query($qry);
        } catch (Exception $ex) {
            $this->serverError($ex->getMessage());
        }
        $roles = array();
        while($role->fetch()){
            $row = array();
            foreach ($sqlCols as $sqlCol) {
                $row[$sqlCol] = $role->{$sqlCol};
            }
            $roles[]=$row;
        }
        echo json_encode(array(
            'draw'=>(int)$this->tableParams['draw'],
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => 2,
            'data'=>$roles
        ));
    }
}
