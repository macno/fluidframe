<?php

if (!defined('FLUIDFRAME')) {
    exit(1);
}


/**
 * Check for value in $config[$main][$sub]
 *
 * @param unknown_type $main
 * @param unknown_type $sub
 */
function common_config($main, $sub)
{
    global $config;
    return (array_key_exists($main, $config) &&
            array_key_exists($sub, $config[$main])) ? $config[$main][$sub] : false;
}

function common_config_default($main, $sub,$dflt=false) {
    $ret = common_config($main, $sub);
    return ($ret === FALSE ) ? $dflt : $ret ;
}


function common_template($type) {
    global $template;
    return array_key_exists($type, $template)  ? $template[$type] : false;
}

function attr($key, $val,$bool ) {
    echo " $key=\"$val\"";
}

function common_get_route($action, $params = array() ) {
    global $router;
    $path = $router->generate($action,$params);
    $href = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    return $href;
}

function common_redirect($url, $code=307) {
    static $status = array(
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            307 => "Temporary Redirect");

    header('HTTP/1.1 '.$code.' '.$status[$code]);
    header("Location: $url");

    echo '<a href="'.$url.'">'.$url.'</a>';
    exit;
}

function common_language() {
    $_default_language=common_config('site','language');

    // Otherwise, find the best match for the languages requested by the
    // user's browser...
    if (common_config('site', 'langdetect')) {
        $httplang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
        if (!empty($httplang)) {
            $language = client_prefered_language($httplang);
            if ($language) {
                return $language;
            }
        }
    }

    // Finally, if none of the above worked, use the site's default...
    return $_default_language;
}

function common_debug($message) {
    global $logger;
    // add records to the log
    if($logger)
        $logger->addDebug($message);
}
function common_error($message) {
    global $logger;
    // add records to the log
    if($logger)
        $logger->addError($message);
}
