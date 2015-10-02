<?php
/*
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2010, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Wrapper for Memcached_DataObject which knows its own schema definition.
 * Builds its own damn settings from a schema definition.
 *
 * @author Brion Vibber <brion@status.net>
 */
abstract class Managed_DataObject extends DB_DataObject
{
    /**
     * The One True Thingy that must be defined and declared.
     */
    public static abstract function schemaDef();

    /**
     * get/set an associative array of table columns
     *
     * @access public
     * @return array (associative)
     */
    function table()
    {
        // Hack for PHP 5.2 not supporting late static binding
        //$table = static::schemaDef();
        $table = call_user_func(array(get_class($this), 'schemaDef'));
        return array_map(array($this, 'columnBitmap'), $table['fields']);
    }
    
    static function staticGet($class, $k, $v = null) {
    
        $obj = new $class ();
        if(empty($v)) {
            $v = $k;
            $k = 'id';
        }
        $obj->{$k} = $v;
        if($obj->find(true)) {
            return $obj;
        }
        return null;
    }
    
    /**
     * get/set an  array of table primary keys
     *
     * Key info is pulled from the table definition array.
     * 
     * @access private
     * @return array
     */
    function keys()
    {
        return array_keys($this->keyTypes());
    }

    /**
     * Get a sequence key
     *
     * Returns the first serial column defined in the table, if any.
     *
     * @access private
     * @return array (column,use_native,sequence_name)
     */

    function sequenceKey()
    {
        $table = call_user_func(array(get_class($this), 'schemaDef'));
        foreach ($table['fields'] as $name => $column) {
            if ($column['type'] == 'serial' || $column['type'] == 'bigserial' ) {
                // We have a serial/autoincrement column.
                // Declare it to be a native sequence!
                return array($name, true, false);
            }
        }

        // No sequence key on this table.
        return array(false, false, false);
    }

    /**
     * Return key definitions for DB_DataObject and Memcache_DataObject.
     *
     * DB_DataObject needs to know about keys that the table has; this function
     * defines them.
     *
     * @return array key definitions
     */

    function keyTypes()
    {
        $table = call_user_func(array(get_class($this), 'schemaDef'));
        $keys = array();

        if (!empty($table['unique keys'])) {
            foreach ($table['unique keys'] as $idx => $fields) {
                foreach ($fields as $name) {
                    $keys[$name] = 'U';
                }
            }
        }

        if (!empty($table['primary key'])) {
            foreach ($table['primary key'] as $name) {
                $keys[$name] = 'K';
            }
        }
        return $keys;
    }

    /**
     * Build the appropriate DB_DataObject bitfield map for this field.
     *
     * @param array $column
     * @return int
     */
    function columnBitmap($column)
    {
        $type = $column['type'];

        // For quoting style...
        $intTypes = array('int',
                          'integer',
                          'float',
                          'serial',
                          'numeric');
        if (in_array($type, $intTypes)) {
            $style = DB_DATAOBJECT_INT;
        } else {
            $style = DB_DATAOBJECT_STR;
        }

        // Data type formatting style...
        $formatStyles = array('blob' => DB_DATAOBJECT_BLOB,
                              'text' => DB_DATAOBJECT_TXT,
                              'date' => DB_DATAOBJECT_DATE,
                              'time' => DB_DATAOBJECT_TIME,
                              'datetime' => DB_DATAOBJECT_DATE | DB_DATAOBJECT_TIME,
                              'timestamp' => DB_DATAOBJECT_MYSQLTIMESTAMP);

        if (isset($formatStyles[$type])) {
            $style |= $formatStyles[$type];
        }

        // Nullable?
        if (!empty($column['not null'])) {
            $style |= DB_DATAOBJECT_NOTNULL;
        }

        return $style;
    }

    function links()
    {
        $links = array();

        $table = call_user_func(array(get_class($this), 'schemaDef'));

        foreach ($table['foreign keys'] as $keyname => $keydef) {
            if (count($keydef) == 2 && is_string($keydef[0]) && is_array($keydef[1]) && count($keydef[1]) == 1) {
                if (isset($keydef[1][0])) {
                    $links[$keydef[1][0]] = $keydef[0].':'.$keydef[1][1];
                }
            }
        }
        return $links;
    }

    /**
     * Return a list of all primary/unique keys / vals that will be used for
     * caching. This will understand compound unique keys, which
     * Memcached_DataObject doesn't have enough info to handle properly.
     *
     * @return array of strings
     */
    function _allCacheKeys()
    {
        $table = call_user_func(array(get_class($this), 'schemaDef'));
        $ckeys = array();

        if (!empty($table['unique keys'])) {
            $keyNames = $table['unique keys'];
            foreach ($keyNames as $idx => $fields) {
                $val = array();
                foreach ($fields as $name) {
                    $val[$name] = self::valueString($this->$name);
                }
                $ckeys[] = self::multicacheKey($this->tableName(), $val);
            }
        }

        if (!empty($table['primary key'])) {
            $fields = $table['primary key'];
            $val = array();
            foreach ($fields as $name) {
                $val[$name] = self::valueString($this->$name);
            }
            $ckeys[] = self::multicacheKey($this->tableName(), $val);
        }
        return $ckeys;
    }
}