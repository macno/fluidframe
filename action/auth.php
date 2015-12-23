<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AuthAction extends Sbadmin2Action {
    function prepare($args) {
        parent::prepare($args);
        if(!common_logged_in()){
            $html ='<div id="error-body" class="row">';
            $html .= '<div class="col-xs-12">';
            $html .= '<h1>Not logged in</h1>';
            $html .= 'Please <strong><a href="'. common_get_route('login',array('lang'=>$this->lang)) .'">log in</a></strong>';
            $html .= '</div>';
            $html .= '</div>';
            throw new ClientException($html,401);
        }
        return true;
    }
}
