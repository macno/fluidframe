<?php
/**
 * Fluidframe - Fluidware Web Framework
 * Copyright (C) 2011, Fluidware
 * 
 * @author: Michele Azzolari michele@fluidware.it
 * 
 */

if (!defined('FLUIDFRAME')) {
    exit(1);
}

/**
 * Table Definition for config
 */

class Config extends Managed_DataObject
{

    public $__table = 'config';                          // table name
    public $section;                         // varchar(32)  primary_key not_null
    public $setting;                         // varchar(32)  primary_key not_null
    public $value;                           // varchar(255)

    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'section' => array('type' => 'varchar', 'length' => 32, 'not null' => true, 'default' => '', 'description' => 'configuration section'),
                'setting' => array('type' => 'varchar', 'length' => 32, 'not null' => true, 'default' => '', 'description' => 'configuration setting'),
                'value' => array('type' => 'varchar', 'length' => 255, 'description' => 'configuration value'),
            ),
            'primary key' => array('section', 'setting'),
        );
    }

    const settingsKey = 'config:settings';

    static function staticGet($k, $v = null) {
        return parent::staticGet(__CLASS__,$k, $v);
    }
    
    static function loadSettings()
    {
        try {
            $settings = self::_getSettings();
            if (!empty($settings)) {
                self::_applySettings($settings);
            }
        } catch (Exception $e) {
            common_error($e->getTraceAsString());
            return;
        }
    }

    static function _getSettings()
    {

        $settings = array();

        $config = new Config();

        $config->find();

        while ($config->fetch()) {
            $settings[] = array($config->section, $config->setting, $config->value);
        }

        $config->free();

        return $settings;
    }

    static function _applySettings($settings)
    {
        global $config;

        foreach ($settings as $s) {
            list($section, $setting, $value) = $s;
            $config[$section][$setting] = $value;
        }
    }

    function pkeyGet($kv)
    {
        return Memcached_DataObject::pkeyGet('Config', $kv);
    }

    static function save($section, $setting, $value)
    {
        $result = null;

        $config = Config::pkeyGet(array('section' => $section,
                                        'setting' => $setting));

        if (!empty($config)) {
            $orig = clone($config);
            $config->value = $value;
            $result = $config->update($orig);
        } else {
            $config = new Config();

            $config->section = $section;
            $config->setting = $setting;
            $config->value   = $value;

            $result = $config->insert();
        }

        return $result;
    }

    
}
