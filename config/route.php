<?php 
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class Route {
    
    static function setRoutes($router) {
        
        $router->add('index', '/');
        $router->add('home', '/{lang}/');
        $router->add('test', '/test/{code}');
        $router->add('robots', '/robots.txt');
    }
}