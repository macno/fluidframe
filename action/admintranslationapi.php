<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdmintranslationapiAction extends AuthAction {

    function prepare($args) {
        global $isApi;
        $isApi = true;
        parent::prepare($args);
        return true;
    }

    function handle() {
        parent::handle();
        header('Content-Type: application/json; charset=utf-8');
        $result = $this->API();
        if(!empty($result)){
            echo json_encode($result);
        }else{
            $this->handle404();
        }
    }

    function handle404(){
        header ( "HTTP/1.0 404 Not Found" );
        echo json_encode(array(
            'status'=>404,
            'error'=>"Translations not found"
        ));
    }

    function API(){
        $translation = new Translation();
        return $translation->getTranslationData($this->args);
    }

}
