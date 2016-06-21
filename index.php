<?php
define ( 'FLUIDFRAME', true );
define ( 'INSTALLDIR', dirname ( __FILE__ ) );

require_once (INSTALLDIR . '/version.php');

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

function getPath($req) {
    if ((common_config('site', 'fancy') || !array_key_exists('PATH_INFO', $_SERVER))
            && array_key_exists('p', $req)
            ) {
                $path = $req['p'];
                if(substr($path,0,1) == '/') {
                    $path = substr($path,1);
                }
                return $path;
            } else if (array_key_exists('PATH_INFO', $_SERVER)) {
                $path = $_SERVER['PATH_INFO'];
                $script = $_SERVER['SCRIPT_NAME'];
                if (substr($path, 0, mb_strlen($script)) == $script) {
                    return substr($path, mb_strlen($script));
                } else {
                    return $path;
                }
            } else {
                return null;
            }
}

$path = getPath($_REQUEST);
$r = Router::get();

$args = $r->map($path);

if (!$args) {
    $error = new ErrorAction ( $_lang );
    $error->setErrorMessage ( 404, 'Unkown page' );
    $error->handle ();
    return;
}


// do I have a lang in URL?
if(isset($args['lang'])) {
    $__lang = $args['lang'];
    $__all_lang = common_config('site','langs');
    if(isset($__all_lang[$__lang])) {
        $_lang = $__lang;
    }
}

// does the route indicate an action?
if (isset ( $args['action'] )) {
    // take the action class directly from the route
    $action = $args['action'];
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
    
    $args = array_merge($args, $_REQUEST);
    
    
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


