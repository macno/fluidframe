<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class Route {

    /**
     *
     * @param URLMapper $router
     */
    static function setRoutes(&$router) {

        $router->connect ( 'robots.txt', array (
                'action' => 'robots'
        ) );

        $router->connect ( '', array (
                'action' => 'index'
        ) );


        // Api
        // generic..
        $router->connect('api/:version/:apiaction', array('action'=>'apihandler', 'version'=>'(v1)', 'apiaction'=>'[a-zA-Z0-9-\s_]+'));



        // Auth
        $router->connect('auth/login', array (
                'action' => 'login'
        ) );
        $router->connect('auth/logout', array (
                'action' => 'logout'
        ) );

        // Admin
        $router->connect('admin/', array (
                'action' => 'adminhome'
        ) );


        // Preview markdown & html
        $router->connect('utils/markdown/preview', array('action'=>'markdownpreview'));
        $router->connect('utils/html/preview', array('action'=>'htmlpreview'));
        $router->connect('admin/api/conversion', array('action'=>'adminconversionapi'));

        // Gestione Traduzione
        $router->connect('admin/api/translation/datatable', array('action'=>'admintranslationapi'));
        $router->connect('admin/api/translation/save', array('action'=>'adminsavetranslationapi'));
        $router->connect('admin/translation', array('action'=>'admintranslationlist'));

        // Gestione Tabelle
        $router->connect('admin/api/:model/datatable', array('action'=>'admintableapi','model' => '[a-zA-Z0-9-\s_]+'));
        $router->connect('admin/:model', array('action'=>'admintablelist','model' => '[a-zA-Z0-9-\s_]+'));

        $router->connect('admin/role/add', array('action'=>'adminroleadd'));
        $router->connect('admin/role/:id', array('action'=>'adminroleedit','id' => '[0-9]+'));

    }
}
