<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}

use Aura\Router\RouterFactory;

$router_factory = new RouterFactory;
$router = $router_factory->newInstance();

require_once INSTALLDIR .'/config/route.php';

Route::setRoutes($router) ;

// $router->add('redirect', '/redirect/{code}');
// $router->addPost('post', '/post/{type}');
