<?php
if (!defined('FLUIDFRAME')) {
    exit(1);
}

$config = array();
$logger = false;

class fwLoader {
    static function classLoader($cls) {

        if (file_exists(INSTALLDIR.'/model/' . $cls . '.php')) {
            require_once(INSTALLDIR.'/model/' . $cls . '.php');
        } else if (file_exists(INSTALLDIR.'/lib/' . strtolower($cls) . '.php')) {
            require_once(INSTALLDIR.'/lib/' . strtolower($cls) . '.php');
        } else if (mb_substr($cls, -6) == 'Action' &&
            file_exists(INSTALLDIR.'/action/' . strtolower(mb_substr($cls, 0, -6)) . '.php')) {
            require_once(INSTALLDIR.'/action/' . strtolower(mb_substr($cls, 0, -6)) . '.php');
//        } else {
//            Event::handle('Autoload', array(&$cls));
        }
    }
}


spl_autoload_register(array('fwLoader', 'classLoader'), true, true);

require_once('vendor/autoload.php');
require_once('lib/tools.php');
require_once('lib/router.php');
require_once('lib/language.php');
require_once('lib/translator.php');
