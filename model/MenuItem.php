<?php

if (!defined('FLUIDFRAME')) {
    exit(1);
}

/**
 * Table Definition for MenuItem
 */

class MenuItem extends Managed_DataObject
{

    public $__table = 'menuitem';
    public $id;
    public $name;
    public $action;
    public $params;
    public $status;
    public $created;
    public $modified;
    public static function schemaDef() {
        $def = array (
                'description' => 'MenuItems',
                'fields' => array (
                        'id' => array (
                                'type' => 'serial',
                                'not null' => true,
                                'description' => 'unique identifier'
                        ),
                        'name' => array (
                                'type' => 'varchar',
                                'length' => 64,
                                'not null' => true,
                                'description' => 'MenuItem name.'
                        ),
                        'action' => array (
                                'type' => 'varchar',
                                'length' => 255,
                                'not null' => true,
                                'description' => 'MenuItem href',
                                'collate' => 'utf8_general_ci'
                        ),
                        'params' => array (
                                'type' => 'varchar',
                                'length' => 255,
                                'description' => 'MenuItem params',
                                'collate' => 'utf8_general_ci'
                        ),
                        'status' => array(
                                'type'=>'tinyint',
                                'length'=>1,
                                'not null' => true,
                                'description'=>'MenuItem status'
                        ),
                        'created' => array (
                                'type' => 'datetime',
                                'not null' => true,
                                'description' => 'date this record was created'
                        ),
                        'modified' => array (
                                'type' => 'timestamp',
                                'not null' => true,
                                'description' => 'date this record was modified'
                        )
                ),
                'primary key' => array (
                        'id'
                )
        );
    
        return $def;
    }
    static function staticGet($k, $v = null) {
        return parent::staticGet(__CLASS__,$k, $v);
    }
    
}