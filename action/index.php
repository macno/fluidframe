<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class IndexAction extends Action {
    function prepare($args) {
        parent::prepare ( $args );
        common_debug ( 'IndexAction -> redirect -> ' . $this->lang);
        common_redirect ( common_get_route ( 'home', array (
                'lang' => $this->lang 
        ) ), 303 );
        return true;
    }
}
