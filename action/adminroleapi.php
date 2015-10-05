<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdminroleapiAction extends Sbadmin2Action {

    function prepare($args) {
        parent::prepare($args);
        return true;
    }

    function handle() {
        parent::handle();
        header('Content-Type: application/json; charset=utf-8');
        $this->API();
    }

    function API(){
        $role = new Role();
        
        $tableCols = Role::getAdminTableStruct();
        
        $sqlCols = array();
        
        foreach ($tableCols as $tableCol) {
            if(!isset($tableCol['visible']) || $tableCol['visible']) {
                $sqlCols[] = $tableCol['name'];
            }
        }
        
        $qry = "select " . implode(",", $sqlCols) . " from ".$role->__table;
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
        echo json_encode(array('data'=>$roles));
    }
}
