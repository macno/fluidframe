<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdminrolelistAction extends Sbadmin2Action {

    function prepare($args) {
        parent::prepare($args);
        return true;
    }

    function handle() {
        parent::handle();
        $this->render ( 'adminrolelist', $this->renderOptions );
    }

    function getJavascripts(){
        return array(
            "/bower_components/datatables/media/js/jquery.dataTables.min.js",
            "/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"
        );
    }

}
