<?php
/**
 * Fluidframe - Fluidware Web Framework
 * Copyright (C) 2012, Fluidware
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

class Gettext extends DataTable_DataObject
{

    public $__table = 'gettext';
    public $id;
    public $context;
    public $str; 
    public $lang;
    public $translation;
    public $original_text;
    public $translated;
    public $created;
    public $modified;
    
    /* Static get */
    static function staticGet($k, $v = null) {
        return parent::staticGet(__CLASS__,$k, $v);
    }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'id' => array('type' => 'serial', 'not null' => true, 'description' => 'unique identifier'),
                'context' => array('type' => 'varchar','length' => 128, 'description' => 'string context'),
                'str' => array('type' => 'varchar','length' => 128, 'not null' => true, 'description' => 'string to be translated'),
                'lang' => array('type' => 'varchar', 'not null' => true, 'length' => 2, 'description' => 'string lang'),
                'translation' => array('type' => 'varchar', 'not null' => true, 'length' => 4000, 'description' => 'translated string'),
                'original_text' => array('type' => 'varchar', 'not null' => true, 'length' => 4000, 'description' => 'original string'),
                'translated' => array('type'=>'tinyint','length'=>'1','description'=>'Stringa tradotta'),
                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified')
            ),
            'primary key' => array('id'),
            'unique keys' => array('lang_str'=>array('lang','context', 'str'))
                
        );
    }
   
    public static function getLastTimestamp() {
        $gettext = new Gettext();
        $qry = "select max(modified) modified from gettext";
        $gettext->query($qry);
        $lastModified = strtotime($gettext->modified);
        $gettext->free();
        return $lastModified;
    }

    static function getAdminTableStruct() {
        return array (
                'id' => array (
                        'visible' => false 
                ) ,
                'context' => array (
                        'i18n' => _t('contesto'),
                        'searchable'=> true,
                        'sortable' => true
                ) ,
                'lang' => array (
                        'i18n' => _t('linguaggio'),
                        'searchable'=> true,
                        'sortable' => true
                ) ,
                'original_text' => array (
                        'i18n' => _t('testo originale'),
                        'searchable'=> true,
                        'sortable' => true
                ) ,
                'translation' => array (
                        'i18n' => _t('testo tradotto'),
                        'searchable'=> true,
                        'sortable' => true
                ) ,
        );
    }

    function getColumnAlias(){
        return array(
            'active'=>'translated'
        );
    }
    
}
