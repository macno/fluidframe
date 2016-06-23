<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class HomeAction extends FrontendAction {
    function prepare($args) {
        parent::prepare($args);
        return true;
    }
    function handle() {
        parent::handle();
        
        $this->render ( 'home', $this->renderOptions );
    }
    function getHreflangs() {
        $hreflangs = array ();
        $langs = common_config ( 'site', 'langs' );
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
