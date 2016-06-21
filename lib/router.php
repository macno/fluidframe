<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}

/**
 * URL Router
 *
 * Cheap wrapper around Net_URL_Mapper
 */
class Router {
    /**
     * 
     * @var URLMapper
     */
    var $m = null;
    
    static $inst = null;
    const REGEX_TAG = '[^\/]+'; // [\pL\pN_\-\.]{1,64} better if we can do unicode regexes
    static function get() {
        if (! Router::$inst) {
            Router::$inst = new Router ();
        }
        return Router::$inst;
    }

    /**
     * Clear the global singleton instance for this class.
     * Needed to ensure reset when switching site configurations.
     */
    static function clear() {
        Router::$inst = null;
    }
    function __construct() {
        if (empty ( $this->m )) {
            $this->m = $this->initialize ();
        }
    }

    /**
     * Create a unique hashkey for the router.
     *
     * The router's url map can change based on the version of the software
     * you're running and the plugins that are enabled. To avoid having bad routes
     * get stuck in the cache, the key includes a list of plugins and the software
     * version.
     *
     * There can still be problems with a) differences in versions of the plugins and
     * b) people running code between official versions, but these tend to be more
     * sophisticated users who can grok what's going on and clear their caches.
     *
     * @return string cache key string that should uniquely identify a router
     */
    static function cacheKey() {
        $parts = array (
                'router'
        );

        // Many router paths depend on this setting.
        if (common_config ( 'singleuser', 'enabled' )) {
            $parts [] = '1user';
        } else {
            $parts [] = 'multi';
        }

        return Cache::codeKey ( implode ( ':', $parts ) );
    }
    function initialize() {
        $m = new URLMapper ();
        return $m;
    }
    
    function getMapper() {
        return $this->m;
    }
    
    function map($path) {
        try {
            $match = $this->m->match ( $path );
        } catch ( Exception $e ) {
            common_error ( "Problem getting route for $path - " . $e->getMessage () );
            // TRANS: Client error on action trying to visit a non-existing page.
            
            $error = new ErrorAction ( 'en' );
            $error->setErrorMessage ( 404,  _i18n( 'SERVER','NOT_FOUND', 'Pagina non trovata.') );
            $error->handle ();
            
            return;
        }

        return $match;
    }
    function build($action, $args = null, $params = null, $fragment = null) {
        $action_arg = array (
                'action' => $action
        );

        if ($args) {
            $args = array_merge ( $action_arg, $args );
        } else {
            $args = $action_arg;
        }

        try {
            $url = $this->m->generate ( $args, $params, $fragment );
        } catch ( Exception $ex ) {
            common_debug ( $ex->getTraceAsString () );
            throw $ex;
        }
        // Due to a bug in the Net_URL_Mapper code, the returned URL may
        // contain a malformed query of the form ?p1=v1?p2=v2?p3=v3. We
        // repair that here rather than modifying the upstream code...

        $qpos = strpos ( $url, '?' );
        if ($qpos !== false) {
            $url = substr ( $url, 0, $qpos + 1 ) . str_replace ( '?', '&', substr ( $url, $qpos + 1 ) );

            // @fixme this is a hacky workaround for http_build_query in the
            // lower-level code and bad configs that set the default separator
            // to &amp; instead of &. Encoded &s in parameters will not be
            // affected.
            $url = substr ( $url, 0, $qpos + 1 ) . str_replace ( '&amp;', '&', substr ( $url, $qpos + 1 ) );
        }

        return $url;
    }
}

require_once INSTALLDIR .'/config/route.php';


$router = Router::get();

Route::setRoutes($router->getMapper());

