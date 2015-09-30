<?php

if (!defined('FLUIDFRAME')) {
    exit(1);
}

/**
 * Table Definition for Account
 */

class Account extends Managed_DataObject
{

    public $__table = 'account';
    public $id;
    public $email;
    public $password;
    public $language;
    public $timezone;
    public $fullname;
    public $status;
    public $role_id;
    public $created;
    public $modified;
    public static function schemaDef() {
        $def = array (
                'description' => 'Accounts',
                'fields' => array (
                        'id' => array (
                                'type' => 'serial',
                                'not null' => true,
                                'description' => 'unique identifier'
                        ),
                        'email' => array (
                                'type' => 'varchar',
                                'length' => 100,
                                'description' => 'email address for password recovery etc.'
                        ),
                        'fullname' => array (
                                'type' => 'varchar',
                                'length' => 255,
                                'description' => 'display name',
                                'collate' => 'utf8_general_ci'
                        ),
                        'password' => array (
                                'type' => 'varchar',
                                'length' => 255,
                                'description' => 'salted password, can be null for OpenID users'
                        ),
                        'language' => array (
                                'type' => 'varchar',
                                'length' => 50,
                                'description' => 'preferred language'
                        ),
                        'timezone' => array (
                                'type' => 'varchar',
                                'length' => 50,
                                'description' => 'timezone'
                        ),
                        'status' => array(
                                'type'=>'tinyint',
                                'length'=>1,
                                'description'=>'account status'
                        ),
                        'role_id' => array(
                                'type'=>'int',
                                'length'=>12,
                                'description' => 'Account\'s role'
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
                        'uq_account_email' => array (
                                'email'
                        )
                ),
                'foreign keys' => array(
                        'fk_account_role_id' => array('role', array('role_id' => 'id')),
                ),
        );
        return $def;
    }
    

    static function staticGet($k, $v = null) {
        return parent::staticGet(__CLASS__,$k, $v);
    }
    
    
    public function getMenu() {
        /*
         * select m.name, mmi.* from menu m, menu_menuitem mmi, role_menu rm, role r 
         * where r.id = rm.role_id and rm.menu_id = m.id and m.id = mmi.menu_id  and r.name = 'ANON' 
         * order by rm.weight asc, mmi.weight asc;
         * 
         */
    }
}