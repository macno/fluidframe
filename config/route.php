<?php 
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class Route {
    
    static function setRoutes($router) {
        
        $router->add('index', '/');
        $router->add('home', '/{lang}/');
        $router->add('login', '/{lang}/auth/login');
        $router->add('logout', '/{lang}/auth/logout');
        $router->add('robots', '/robots.txt');
    }
}