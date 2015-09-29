<?php

if (!defined('FLUIDFRAME')) {
    exit(1);
}

/**
 * Table Definition for Role_menu
 */

class Role_menu extends Managed_DataObject
{

    public $__table = 'role_menu';                          // table name
    public $id;
    public $role_id;
    public $menu_id;
    public $weight;
    public $created;
    public $modified;
    public static function schemaDef() {
        $def = array (
                'description' => 'Roles',
                'fields' => array (
                        'id' => array (
                                'type' => 'serial',
                                'not null' => true,
                                'description' => 'unique identifier'
                        ),
                        'role_id' => array (
                                'type' => 'int',
                                'length' => 11,
                                'not null' => true,
                                'description' => 'Role id.'
                        ),
                        'menu_id' => array (
                                'type' => 'int',
                                'length' => 11,
                                'not null' => true,
                                'description' => 'Role id.'
                        ),
                        'weight' => array(
                                'type'=>'int',
                                'length'=>3,
                                'description'=>'menu position',
                                'not null' => true,
                                'default'=>500
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
                ),
                'unique keys' => array (
                        'uq_role_menu_ids' => array (
                                'role_id',
                                'menu_id'
                        )
                ),
                'foreign keys' => array(
                        'fk_role_menu_role_id' => array('role', array('role_id' => 'id')),
                        'fk_role_menu_menu_id' => array('menu', array('menu_id' => 'id')),
                ),
        );
    
        return $def;
    }
}