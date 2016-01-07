<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdminhomeAction extends AuthAction {
    function prepare($args) {
        parent::prepare($args);
        return true;
    }
    function handle() {
        parent::handle();
        
        $this->render ( 'admin/home', $this->renderOptions );
    }
    function getHreflangs() {
        $hreflangs = array ();
        $langs = common_config ( 'site', 'langs' );
        foreach ( $langs as $key => $lang ) {
            
            $hreflangs [] = array('lang'=>$key,
                    'href'=>
                    common_get_route ( 'adminhome', array (
                    'lang' => $key )
            ) );
        }
        
        return  $hreflangs;
    }
}
