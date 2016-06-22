<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class Fluidframe {

    static $isApi = false;

    static private $configFile = '/config/config.php';
    static private $templateFile = '/config/template.php';

    static function init() {

        Fluidframe::loadConfig();
        Fluidframe::loadTemplate();

        Fluidframe::initLogger();

        Fluidframe::initDb();
    }

    private static function initLogger() {
        global $logger;

        $logLevel = (int)common_config('log', 'level',0);

        // create a log channel
        $logger = new Logger(common_config('site', 'code'));


        if($logLevel < Logger::DEBUG) {
            // It means no logging..
            $logLevel = Logger::EMERGENCY*2;
        }
        $logger->pushHandler(new StreamHandler(common_config('log', 'file'), $logLevel));
    }

    private static function loadTemplate() {
        global $template;

        if(!file_exists(INSTALLDIR.Fluidframe::$templateFile)) {
            throw new FluidframeException('Template definition not found');
        }
        include INSTALLDIR.Fluidframe::$templateFile;

    }

    private static function loadConfig() {

        Fluidframe::loadConfigFile();

    }

    private static function loadConfigFile() {
        global $config;

        if(!file_exists(INSTALLDIR.Fluidframe::$configFile)) {
            throw new FluidframeException('Configuration not found');
        }
        include INSTALLDIR.Fluidframe::$configFile;
    }

    private static function initDb() {
        global $config;
        $database = common_config('db','database');
        if(empty($database)) {
            common_debug('No database configured.');
            return;
        }

        $dbOptions = $config['db'];

        require_once INSTALLDIR.'/lib/db.php';

    }
}
