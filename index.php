<?php
define ( 'FLUIDFRAME', true );
define ( 'INSTALLDIR', dirname ( __FILE__ ) );

require_once (INSTALLDIR . '/lib/core.php');

try {
    Fluidframe::init ();
    $_lang = common_language ();
} catch ( Exception $e ) {
    common_error ( $e->getTraceAsString () );
    $error = new ErrorAction ( 'en' );
    $error->setErrorMessage ( 500, $e->getMessage () );
    $error->handle ();
}

// get the incoming request URL path
$path = parse_url ( $_SERVER ['REQUEST_URI'], PHP_URL_PATH );

// get the route based on the path and server
$route = $router->match ( $path, $_SERVER );

if (empty ( $route )) {
    $error = new ErrorAction ( $_lang );
    $error->setErrorMessage ( 404, 'Unkown page' );
    $error->handle ();
}

// does the route indicate an action?
if (isset ( $route->params ['action'] )) {
    // take the action class directly from the route
    $action = $route->params ['action'];
} else {
    // use a default action class
    $action = 'index';
}

$action_class = ucfirst ( $action ) . 'Action';

if (! class_exists ( $action_class )) {
    $error = new ErrorAction ( $_lang );
    $error->setErrorMessage ( 400, 'Unkown action &lt;' . $action_class . '&gt;' );
    $error->handle ();
}

$actionClass = new $action_class ( $_lang );
try {
    
    $args = array_merge ( $route->params, $_REQUEST );
    
    if ($actionClass->prepare ( $args )) {
        $actionClass->handle ();
    }
} catch ( ClientException $e ) {
    $error = new ErrorAction ( $_lang );
    $error->setErrorMessage ( $e->getCode (), 'ClientException:' . $e->getMessage () );
    $error->handle ();
} catch ( ServerException $e ) {
    $error = new ErrorAction ( $_lang );
    $error->setErrorMessage ( $e->getCode (), 'ServerException: ' . $e->getMessage () );
    $error->handle ();
} catch ( FluidframeException $e ) {
    $error = new ErrorAction ( $_lang );
    $error->setErrorMessage ( 500, 'FluidframeException: ' . $e->getMessage () );
    $error->handle ();
} catch ( Exception $e ) {
    $error = new ErrorAction ( $_lang );
    $error->setErrorMessage ( 500, 'Exception: ' . $e->getMessage () );
    $error->handle ();
}


