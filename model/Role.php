<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}

/**
 * Table Definition for Role
 */
class Role extends DataTable_DataObject {
    public $__table = 'role'; // table name
    public $id;
    public $name;
    public $description;
    public $status;
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
                        'name' => array (
                                'type' => 'varchar',
                                'length' => 64,
                                'not null' => true,
                                'description' => 'Role name.' 
                        ),
                        'description' => array (
                                'type' => 'varchar',
                                'length' => 255,
                                'description' => 'Role description',
                                'collate' => 'utf8_general_ci' 
                        ),
                        'status' => array (
                                'type' => 'tinyint',
                                'length' => 1,
                                'not null' => true,
                                'description' => 'role status' 
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
    static function getAdminTableStruct() {
        return array (
                'id'=> array (
                        'visible' => false 
                ) ,
                'name'=> array (
                        'i18n' => _i18n('ADMIN', 'name', 'Name'),
                        'searchable'=> true,
                        'sortable' => true
                ) ,
                'description'=> array (
                        'i18n' => _i18n('ADMIN', 'desc', 'Description'),
                        'searchable'=> true,
                        'sortable' => true
                ) ,
                'status'=> array (
                        'i18n' => _i18n('ADMIN', 'status', 'Status'),
                        'searchable'=> true,
                        'sortable' => true
                ) ,
        );
    }

    function getColumnAlias(){
        return array(
            'active'=>array('status',1),
            'inactive'=>array('status',0)
        );
    }

    static function staticGet($k, $v = null) {
        return parent::staticGet(__CLASS__,$k, $v);
    }
    
}
