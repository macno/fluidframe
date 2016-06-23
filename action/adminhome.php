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

        $langs = common_config ( 'site', 'langs' );
        $this->renderOptions['langs'] = array_keys($langs);

        $this->render ( 'adminhome', $this->renderOptions );
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

    function getJavascripts() {
        return [
            "/javascripts/set.js",
            "/bower_components/markitup/markitup/jquery.markitup.js",
        ];
    }

    function getStylesheets() {
        return [
            "/bower_components/markitup/markitup/skins/markitup/style.css",
            "/stylesheets/markdown.css",
            "/stylesheets/markitup.css"
        ];
    }
}
