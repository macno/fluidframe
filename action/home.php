<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class HomeAction extends Action {
    function prepare($args) {
        parent::prepare($args);
        return true;
    }
    function handle() {
        $this->render ( 'home', array () );
    }
    function getHreflangs() {
        $hreflangs = array ();
        $langs = common_config ( 'site', 'langs' );
        common_debug('Langs:' . print_r($langs,true));
        foreach ( $langs as $key => $lang ) {
            
            $hreflangs [] = array('lang'=>$key,
                    'href'=>
                    common_get_route ( 'home', array (
                    'lang' => $key )
            ) );
        }
        
        return  $hreflangs;
    }
}
