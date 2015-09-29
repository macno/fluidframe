<?php

if (!defined('FLUIDFRAME')) {
    exit(1);
}

/**
 * Table Definition for Menu_menuitem
 */

class Menu_menuitem extends Managed_DataObject
{

    public $__table = 'menu_menuitem';                          // table name
    public $id;
    public $menu_id;
    public $menuitem_id;
    public $sub_menu_id;
    public $weight;
    public $created;
    public $modified;
    public static function schemaDef() {
        $def = array (
                'description' => 'Menu -> Menuitems',
                'fields' => array (
                        'id' => array (
                                'type' => 'serial',
                                'not null' => true,
                                'description' => 'unique identifier'
                        ),
                        'menu_id' => array (
                                'type' => 'int',
                                'length' => 11,
                                'description' => 'Menu id.'
                        ),
                        'menuitem_id' => array (
                                'type' => 'int',
                                'length' => 11,
                                'description' => 'Menuitem id.',
                                'not null'=>false
                        ),
                        'sub_menu_id' => array (
                                'type' => 'int',
                                'length' => 11,
                                'description' => 'Menuitem id.',
                                'not null'=>false
                        ),
                        'weight' => array(
                                'type'=>'int',
                                'length'=>3,
                                'description'=>'menu position',
                                'not null'=>true,
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
                        'uq_menu_menuitem_ids' => array (
                                'menu_id',
                                'menuitem_id',
                                'sub_menu_id'
                        )
                ),
                
                'foreign keys' => array(
                        'fk_menu_menuitem_menuitem_id' => array('menuitem', array('menuitem_id' => 'id')),
                        'fk_menu_menuitem_menu_id' => array('menu', array('menu_id' => 'id')),
                        'fk_menu_menuitem_sub_menu_id' => array('menu', array('sub_menu_id' => 'id')),
                ),
        );
    
        return $def;
    }
}