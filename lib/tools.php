<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}

/**
 * Check for value in $config[$main][$sub]
 *
 * @param unknown_type $main
 * @param unknown_type $sub
 */
function common_config($main, $sub) {
    global $config;
    return (array_key_exists ( $main, $config ) && array_key_exists ( $sub, $config [$main] )) ? $config [$main] [$sub] : false;
}
function common_config_default($main, $sub, $dflt = false) {
    $ret = common_config ( $main, $sub );
    return ($ret === FALSE) ? $dflt : $ret;
}
function common_template($type) {
    global $template;
    return array_key_exists ( $type, $template ) ? $template [$type] : false;
}
function attr($name, $value = true, $escaped = true) {
    if (! empty ( $value )) {
        echo " $name=\"$value\"";
    }
}
function attrs() {
    $args = func_get_args ();
    $attrs = array ();
    foreach ( $args as $arg ) {
        foreach ( $arg as $key => $value ) {
            if ($key == 'class') {
                if (! isset ( $attrs [$key] ))
                    $attrs [$key] = array ();
                $attrs [$key] = array_merge ( $attrs [$key], is_array ( $value ) ? $value : explode ( ' ', $value ) );
            } else {
                $attrs [$key] = $value;
            }
        }
    }
    foreach ( $attrs as $key => $value ) {
        if ($key == 'class') {
            attr_class ( $value );
        } else {
            attr ( $key, $value );
        }
    }
}
function attr_class() {
    $classes = array ();
    $args = func_get_args ();
    foreach ( $args as $arg ) {
        if (empty ( $arg ) || is_array ( $arg ) && count ( $arg ) == 0)
            continue;
        $classes = array_merge ( $classes, is_array ( $arg ) ? $arg : array (
                $arg
        ) );
    }
    $classes = array_filter ( $classes );
    if (count ( $classes ) > 0)
        attr ( 'class', join ( ' ', $classes ) );
}
function add() {
    $result = '';
    $args = func_get_args ();
    $concat = false;
    foreach ( $args as $arg ) {
        if ($concat || is_string ( $arg )) {
            $concat = true;
            $result .= $arg;
        } elseif (is_numeric ( $arg )) {
            if ($result === '')
                $result = 0;
            $result += $arg;
        }
    }
    return $result;
}
function common_get_route($action, $args=null, $params=null, $fragment=null, $addSession=true) {

    $r = Router::get();
    try {
        $path = $r->build($action, $args, $params, $fragment);
    } catch(Exception $e) {
        common_error('Unable to build path:'.$e->getMessage());
        return false;
    }

    $url = common_path($path);

    return $url;
}
function common_redirect($url, $code = 307) {
    static $status = array (
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            307 => "Temporary Redirect"
    );

    header ( 'HTTP/1.1 ' . $code . ' ' . $status [$code] );
    header ( "Location: $url" );

    echo '<a href="' . $url . '">' . $url . '</a>';
    exit ();
}
function common_language() {
    $_default_language = common_config ( 'site', 'language' );

    // Otherwise, find the best match for the languages requested by the
    // user's browser...
    if (common_config ( 'site', 'langdetect' )) {
        $httplang = isset ( $_SERVER ['HTTP_ACCEPT_LANGUAGE'] ) ? $_SERVER ['HTTP_ACCEPT_LANGUAGE'] : null;
        if (! empty ( $httplang )) {
            $language = client_prefered_language ( $httplang );
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
    if ($logger)
        $logger->addDebug ( $message );
}

function common_log( $level, $message = false) {
    global $logger;
    // add records to the log
    if ($logger) {
        switch($level) {
        	case LOG_DEBUG:
        	    $logger->addDebug($message);
        	    break;

        	case LOG_WARNING:
        	    $logger->addWarning($message);
        	    break;

        	case LOG_ERR:
        	    $logger->addError($message);
        	    break;
        	case LOG_CRIT:
        	    $logger->addCritical($message);
        	    break;

        	default:
        	    $logger->addDebug($message);

        }

    }
}
function common_error($message) {
    global $logger;
    // add records to the log
    if ($logger)
        $logger->addError ( $message );
}
function common_sql_now() {
    return common_sql_date ( time () );
}
function common_sql_date($datetime) {
    return strftime ( '%Y-%m-%d %H:%M:%S', $datetime );
}
function common_string_to_date($str) {
    $adatetime = explode ( " ", $str );
    $date = $adatetime [0];
    $time = $adatetime [1];
    $adate = explode ( "/", $date );
    $atime = explode ( ":", $time );
    // common_debug(print_r($adate,true). "\n" . print_r($atime,true));
    return mktime ( $atime [0], $atime [1], (isset ( $atime [2] ) ? $atime [2] : '00'), $adate [0], $adate [1], $adate [2] );
}

// Common tools for account / password / user sessions

$_cur = false;
function common_logged_in() {
    return (! is_null ( common_current_user () ));
}
function common_current_user() {
    global $_cur;

    if ($_cur === false) {

        if (isset ( $_COOKIE [session_name ()] ) || isset ( $_GET [session_name ()] ) || (isset ( $_SESSION ['userid'] ) && $_SESSION ['userid'])) {
            common_ensure_session ();
            $id = isset ( $_SESSION ['userid'] ) ? $_SESSION ['userid'] : false;
            if ($id) {
                $user = Account::staticGet ( $id );
                if ($user) {
                    $_cur = $user;
                    return $_cur;
                }
            }
        }

        // that didn't work; try to remember; will init $_cur to null on failure
        $_cur = common_remembered_user ();
    }

    return $_cur;
}
function common_set_user($user) {
    global $_cur;
    if (is_null ( $user ) && common_have_session ()) {
        $_cur = null;
        unset ( $_SESSION ['userid'] );
        return true;
    } else if (is_string ( $user )) {
        $email = $user;
        $user = Account::staticGet ( 'email', $email );
    } else if (! ($user instanceof Account)) {
        return false;
    }

    if ($user) {
        common_ensure_session ();
        $_SESSION ['userid'] = $user->id;
        $_cur = $user;
        return $_cur;
    }
    return false;
}
function common_check_user($email, $password) {
    // empty $email always unacceptable
    common_debug('Utente con mail ' . $email . ' password vuota: ' . empty($password));
    if (empty ( $email )) {
        return false;
    }

    $authenticatedUser = false;

    $user = Account::staticGet ( 'email', $email );
    if (! empty ( $user )) {
        if (! empty ( $password )) {
            // never allow login with blank password
            if (0 == strcmp ( common_munge_password ( $password, $user->id ), $user->password )) {
                // internal checking passed
                $authenticatedUser = $user;
            } else {
                common_debug('Nessun utente con mail ' . $email . ' password non matchano');
            }
        } else {
            common_debug('Utente con mail ' . $email . ' con password vuota');
        }
    } else {
        common_debug('Nessun utente con mail ' . $email);
    }

    return $authenticatedUser;
}
function common_munge_password($password, $id) {
    if (is_object ( $id ) || is_object ( $password )) {
        $e = new Exception ();
        common_error ( __METHOD__ . ' object in param to common_munge_password ' . str_replace ( "\n", " ", $e->getTraceAsString () ) );
    }

    return sha1 ( $password . $id );
}
function common_real_login($real = true) {
    common_ensure_session ();
    $_SESSION ['real_login'] = $real;
}
function common_is_real_login() {
    return common_logged_in () && $_SESSION ['real_login'];
}

define ( 'REMEMBERME', 'rememberme' );
define ( 'REMEMBERME_EXPIRY', 30 * 24 * 60 * 60 ); // 30 days
function common_rememberme($user = null) {
    if (! $user) {
        $user = common_current_user ();
        if (! $user) {
            return false;
        }
    }

    $rm = new Remember_me ();

    $rm->code = common_good_rand ( 16 );
    $rm->account_id = $user->id;

    // Wrap the insert in some good ol' fashioned transaction code

    $rm->query ( 'BEGIN' );

    $result = $rm->insert ();

    if (! $result) {
        common_log_db_error ( $rm, 'INSERT', __FILE__ );
        return false;
    }

    $rm->query ( 'COMMIT' );

    $cookieval = $rm->account_id . ':' . $rm->code;

    // common_log(LOG_INFO, 'adding rememberme cookie "' . $cookieval . '" for ' . $user->nickname);

    common_set_cookie ( REMEMBERME, $cookieval, time () + REMEMBERME_EXPIRY );

    return true;
}
function common_remembered_user() {
    $user = null;

    $packed = isset ( $_COOKIE [REMEMBERME] ) ? $_COOKIE [REMEMBERME] : null;

    if (! $packed) {
        return null;
    }

    list ( $id, $code ) = explode ( ':', $packed );

    if (! $id || ! $code) {
        common_log ( LOG_WARNING, 'Malformed rememberme cookie: ' . $packed );
        common_forgetme ();
        return null;
    }

    $rm = Remember_me::staticGet ( $code );

    if (! $rm) {
        common_log ( LOG_WARNING, 'No such remember code: ' . $code );
        common_forgetme ();
        return null;
    }

    if ($rm->account_id != $id) {
        common_log ( LOG_WARNING, 'Rememberme code for wrong user: ' . $rm->account_id . ' != ' . $id );
        common_forgetme ();
        return null;
    }

    $user = Account::staticGet ( $rm->account_id );

    if (! $user) {
        common_log ( LOG_WARNING, 'No such user for rememberme: ' . $rm->user_id );
        common_forgetme ();
        return null;
    }

    // successful!
    $result = $rm->delete ();

    if (! $result) {
        common_log_db_error ( $rm, 'DELETE', __FILE__ );
        common_log ( LOG_WARNING, 'Could not delete rememberme: ' . $code );
        common_forgetme ();
        return null;
    }

    common_log ( LOG_INFO, 'logging in ' . $user->email . ' using rememberme code ' . $rm->code );

    common_set_user ( $user );
    common_real_login ( false );

    // We issue a new cookie, so they can log in
    // automatically again after this session

    common_rememberme ( $user );

    return $user;
}

/**
 * must be called with a valid user!
 */
function common_forgetme() {
    common_set_cookie ( REMEMBERME, '', 0 );
}
function common_get_returnto() {
    common_ensure_session ();
    return (array_key_exists ( 'returnto', $_SESSION )) ? $_SESSION ['returnto'] : null;
}

function common_set_returnto($url) {
    common_ensure_session ();
    $_SESSION ['returnto'] = $url;
}

function common_set_cookie($key, $value, $expiration=0)
{
    $path = common_config('site', 'path');
    $server = common_config('site', 'server');

    if ($path && ($path != '/')) {
        $cookiepath = '/' . $path . '/';
    } else {
        $cookiepath = '/';
    }
    return setcookie($key,
            $value,
            $expiration,
            $cookiepath,
            $server,
            common_config('site', 'ssl')=='always');
}

function common_ensure_session() {
    $c = null;
    if (array_key_exists ( session_name (), $_COOKIE )) {
        $c = $_COOKIE [session_name ()];
    }
    if (! common_have_session ()) {
        if (common_config ( 'sessions', 'handle' )) {
            Session::setSaveHandler ();
        }
        if (array_key_exists ( session_name (), $_GET )) {
            $id = $_GET [session_name ()];
        } else if (array_key_exists ( session_name (), $_COOKIE )) {
            $id = $_COOKIE [session_name ()];
        }
        if (isset ( $id )) {
            session_id ( $id );
        }
        @session_start ();
        if (! isset ( $_SESSION ['started'] )) {
            $_SESSION ['started'] = time ();
            if (! empty ( $id )) {
                common_log ( LOG_WARNING, 'Session cookie "' . $_COOKIE [session_name ()] . '" ' . ' is set but started value is null' );
            }
        }
    }
}
function common_have_session() {
    return (0 != strcmp ( session_id (), '' ));
}
function common_session_token() {
    common_ensure_session ();
    if (! array_key_exists ( 'token', $_SESSION )) {
        $_SESSION ['token'] = common_good_rand ( 64 );
    }
    return $_SESSION ['token'];
}
function common_good_rand($bytes) {
    // XXX: use random.org...?
    if (@file_exists ( '/dev/urandom' )) {
        return common_urandom ( $bytes );
    } else { // FIXME: this is probably not good enough
        return common_mtrand ( $bytes );
    }
}
function common_urandom($bytes) {
    $h = fopen ( '/dev/urandom', 'rb' );
    // should not block
    $src = fread ( $h, $bytes );
    fclose ( $h );
    $enc = '';
    for($i = 0; $i < $bytes; $i ++) {
        $enc .= sprintf ( "%02x", (ord ( $src [$i] )) );
    }
    return $enc;
}
function common_mtrand($bytes) {
    $enc = '';
    for($i = 0; $i < $bytes; $i ++) {
        $enc .= sprintf ( "%02x", mt_rand ( 0, 255 ) );
    }
    return $enc;
}

function common_path($relative)
{
    $pathpart =  '';

    if (common_config('site', 'ssl') === 'always') {
        $proto = 'https';
        if (common_config('site', 'server')) {
            $serverpart = common_config('site', 'server');
        } else {
            // using current..
            $serverpart = $_SERVER['HTTP_HOST'];
//             common_log(LOG_ERR, 'Site server not configured, unable to determine site name.');
        }
    } else {
        $proto = 'http';
        if (common_config('site', 'server')) {
            $serverpart = common_config('site', 'server');
        } else {
            // using current..
            $serverpart = $_SERVER['HTTP_HOST'];
            // common_log(LOG_ERR, 'Site server not configured, unable to determine site name.');

        }
    }

    return $proto.'://'.$serverpart.'/'.$pathpart.$relative;
}

function common_slugify($text) {
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

    // trim
    $text = trim($text, '-');

    // transliterate
    if (function_exists('iconv'))
    {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }

    // lowercase
    $text = strtolower($text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    if (empty($text))
    {
        return 'n-a';
    }

    return $text;
}

function common_log_objstring(&$object)
{
    if (is_null($object)) {
        return "null";
    }
    if (!($object instanceof DB_DataObject)) {
        return "(unknown)";
    }
    $arr = $object->toArray();
    $fields = array();
    foreach ($arr as $k => $v) {
        if (is_object($v)) {
            $fields[] = "$k='".get_class($v)."'";
        } else {
            $fields[] = "$k='$v'";
        }
    }
    $objstring = $object->tableName() . '[' . implode(',', $fields) . ']';
    return $objstring;
}

function common_log_db_error(&$object, $verb, $filename=null) {
    $objstr = common_log_objstring($object);
    $last_error = &PEAR::getStaticProperty('DB_DataObject','lastError');
    if (is_object($last_error)) {
        $msg = $last_error->message;
    } else {
        $msg = 'Unknown error (' . var_export($last_error, true) . ')';
    }
    common_log(LOG_ERR, $msg . '(' . $verb . ' on ' . $objstr . ')', $filename);
}
