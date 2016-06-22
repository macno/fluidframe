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

            // File opzionale con parametri custom da passare alla creazione della datatable
            // es. ordinare di default le notizie per data dalla più recente alla più remota
            $jsfile = 'javascripts/admintable'. strtolower($this->trimmed('model')) .'.js';
            if(file_exists($jsfile)){
                $this->renderOptions['jsfile']='/'.$jsfile;
            }

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
        $js = parent::getJavascripts();
        return array_merge($js,array(
            "/bower_components/datatables/media/js/jquery.dataTables.min.js",
            "/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"
        ));
    }

}
