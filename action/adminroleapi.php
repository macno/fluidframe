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
        echo '{
            "data":[
                {
                    "id": "1",
                    "name": "nome 1",
                    "description": "descrizione 1",
                    "status": "status 1",
                    "created": "created 1",
                    "modified": "modified 1"
                },
                {
                    "id": "2",
                    "name": "nome 2",
                    "description": "descrizione 2",
                    "status": "status 2",
                    "created": "created 2",
                    "modified": "modified 2"
                }
            ]}';
    }
}
