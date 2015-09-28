<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class Fluidframe {
    
    static private $configFile = '/config/config.php';
    static private $templateFile = '/config/template.php';
    
    static function init() {
        
        Fluidframe::loadConfig();
        Fluidframe::loadTemplate();
        
        Fluidframe::initLogger();
    }
    
    private static function initLogger() {
        global $logger;

        // create a log channel
        $logger = new Logger(common_config('site', 'code'));
        $logger->pushHandler(new StreamHandler(common_config('site', 'logfile'), Logger::DEBUG));

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
}