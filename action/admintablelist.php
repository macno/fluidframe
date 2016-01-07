<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdmintablelistAction extends AuthAction {

    function prepare($args) {
        parent::prepare($args);
        return true;
    }

    function handle() {
        parent::handle();

        $model = ucfirst($this->trimmed('model'));
        if(class_exists($model)){
            $tableCols = $model::getAdminTableStruct();

            $tableColsJson = array();

            foreach ($tableCols as $tableName=>$tableCol) {
                $tableColsJson[] = array(
                        'data'=>$tableName,
                        'title'=>(isset($tableCol['i18n']))
                            ? $tableCol['i18n']
                            : $tableName,
                        'visible'=>(isset($tableCol['visible']))
                            ? $tableCol['visible']
                            : true
                );
            }


            $this->renderOptions['tableStruct'] = json_encode($tableColsJson);
            $this->renderOptions['model'] = $this->trimmed('model');
            // common_debug("tableColsJson: ".print_r($tableColsJson,true));

            $this->render ( 'admintablelist', $this->renderOptions );
        }else{
            common_debug("Errore");
            $html ='<div id="error-body" class="row">';
            $html .= '<div class="col-xs-12">';
            $html .= '<h1>Errore</h1>';
            $html .= 'Tabella non esistente o non editabile';
            $html .= '</div>';
            $html .= '</div>';
            throw new ClientException($html,401);
        }
    }

    function getJavascripts(){
        return array(
            "/bower_components/datatables/media/js/jquery.dataTables.min.js",
            "/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"
        );
    }

}
