<?php
/**
 * Table Definition for remember_me
 */

class Remember_me extends Managed_DataObject {

    public $__table = 'remember_me';                     // table name
    public $code;                            // varchar(32)  primary_key not_null
    public $user_id;                         // int(4)   not_null
    public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP


    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'code' => array('type' => 'varchar', 'length' => 32, 'not null' => true, 'description' => 'good random code'),
                'account_id' => array('type' => 'int', 'not null' => true, 'description' => 'user who is logged in'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            ),
            'primary key' => array('code'),
            'foreign keys' => array(
                'remember_me_account_id_fkey' => array('account', array('account_id' => 'id')),
            ),
        );
    }
    
    static function staticGet($k, $v = null) {
        return parent::staticGet(__CLASS__,$k, $v);
    }
}
