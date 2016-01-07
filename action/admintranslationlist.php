<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdmintranslationlistAction extends AuthAction {

    function prepare($args) {
        parent::prepare($args);
        return true;
    }

    function handle() {
        parent::handle();
        
        $translationCols =  Translation::getAdminTableStruct();
        $translationColsJson = array();
        
        foreach ($translationCols as $translationName=>$translationCol) {
            if(!isset($translationCol['visible']) || $translationCol['visible']) {
                $translationColsJson[] = array(
                        'data'=>$translationName,
                        'className'=>$translationName,
                        'title'=>$translationCol['i18n'],
                        'search'=>((isset($translationCol['selectable'])&&($translationCol['selectable'])) ?
                                '<select id="search_'. $translationName .'"><option value=""></option></select>' :
                                '<input type="text" id="search_'. $translationName .'">')
                );
            }
        }
        
        $srcData = array();
        $i = file_get_contents(INSTALLDIR.'/i18n/sources.json');
        $l = json_decode($i,true);
        if($l === null) {
            echo "There's an error parsing sources.json\n";
        } else {
            $srcData = $l;
        }

        $this->renderOptions['translationStruct'] = json_encode($translationColsJson);
        $this->renderOptions['model'] = $this->trimmed('model');
        $this->renderOptions['langs'] = json_encode(array_keys(common_config('site','langs')));
        $this->renderOptions['contexts'] = json_encode(array_keys($srcData));
        
        $this->render ( 'admintranslationlist', $this->renderOptions );
    }

    function getStylesheets(){
        return array(
            "/bower_components/markitup/markitup/skins/markitup/style.css",
            "/bower_components/markitup/markitup/sets/html/style.css",
            "/bower_components/markitup/markitup/sets/markdown/style.css"
        );
    }

    function getJavascripts(){
        return array(
            "/bower_components/datatables/media/js/jquery.dataTables.min.js",
            "/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js",
            "/bower_components/markitup/markitup/jquery.markitup.js",
            "/bower_components/markitup/markitup/sets/markdown/set.js",
            "/bower_components/markitup/markitup/sets/html/set.js"
        );
    }

}
