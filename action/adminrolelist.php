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
        
        $tableCols = Role::getAdminTableStruct();
        
        $tableColsJson = array();
        
        foreach ($tableCols as $tableCol) {
            if(!isset($tableCol['visible']) || $tableCol['visible']) {
                $tableColsJson[] = array(
                        'data'=>$tableCol['name'],
                        'title'=>$tableCol['i18n']
                );
            }
        }
        
        
        $this->renderOptions['tableStruct'] = json_encode($tableColsJson);
        
        $this->render ( 'adminrolelist', $this->renderOptions );
    }

    function getJavascripts(){
        return array(
            "/bower_components/datatables/media/js/jquery.dataTables.min.js",
            "/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"
        );
    }

}
