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
        echo json_encode($role->getTableData($this->args));
    }

}
