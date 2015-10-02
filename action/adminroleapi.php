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
        $qry = "select * from role";
        try {
            $role->query($qry);
        } catch (Exception $ex) {
            $this->serverError($ex->getMessage());
        }
        $roles = array();
        while($role->fetch()){
            $roles[]=array(
                'id'=>$role->id,
                'name'=>$role->name,
                'description'=>$role->description,
                'status'=>$role->status,
                'created'=>$role->created,
                'modified'=>$role->modified
            );
        }
        echo json_encode(array('data'=>$roles));
    }
}
