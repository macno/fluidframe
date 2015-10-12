<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AuthAction extends Sbadmin2Action {
    function prepare($args) {
        parent::prepare($args);
        if(!common_logged_in()){
            throw new ClientException('Not logged in.');
        }
        return true;
    }
}
