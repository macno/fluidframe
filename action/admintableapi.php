<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdmintableapiAction extends Sbadmin2Action {


    function prepare($args) {
        parent::prepare($args);
        return true;
    }

    function handle() {
        parent::handle();
        header('Content-Type: application/json; charset=utf-8');
        $model = ucfirst($this->trimmed('model'));
        if(class_exists($model)){
            $result = $this->API($model);
            if(!empty($result)){
                echo json_encode($result);
            }else{
                $this->handle404($model);
            }
        }else{
            $this->handle404($model);
        }
    }

    function handle404($model){
        header ( "HTTP/1.0 404 Not Found" );
        echo json_encode(array(
            'status'=>404,
            'error'=>"Model '$model' not found"
        ));
    }

    function API($model){
        $obj = new $model();
        return $obj->getTableData($this->args);
    }

}
