<?php

if (!defined('FLUIDFRAME')) {
    exit(1);
}

/**
 * Table Definition for Menu
 */

class Menu extends Managed_DataObject
{

    public $__table = 'menu';                          // table name
    public $id;
    public $name;
    public $description;
    public $status;
    public $created;
    public $modified;
    public static function schemaDef() {
        $def = array (
                'description' => 'Menus',
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
                                'description' => 'Menu name.'
                        ),
                        'description' => array (
                                'type' => 'varchar',
                                'length' => 255,
                                'description' => 'Menu description',
                                'collate' => 'utf8_general_ci'
                        ),
                        'status' => array(
                                'type'=>'tinyint',
                                'length'=>1,
                                'description'=>'Menu status',
                                'not null' => true
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