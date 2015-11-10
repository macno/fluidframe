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

class Translation
{
    private function parse_args($args){
        $tableParams = array();
        $tableParams['draw']=$args['draw'];
        $tableParams['columns']=$args['columns'];
        $tableParams['order']=$args['order'];
        $tableParams['start']=$args['start'];
        $tableParams['length']=$args['length'];
        $tableParams['search']=$args['search']['value'];
        // common_debug("tableParams: ".print_r($tableParams,true));
        return $tableParams;
    }

    static function getAdminTableStruct() {
        return array (
                'lang' => array (
                        'i18n' => _i18n('ADMIN', 'lang', 'lingua'),
                        'selectable'=> true,
                        'searchable'=> true,
                        'sortable' => true
                ) ,
                'context' => array (
                        'i18n' => _i18n('ADMIN', 'context', 'contesto'),
                        'selectable'=> true,
                        'searchable'=> true,
                        'sortable' => true
                ) ,
                'key' => array (
                        'i18n' => _i18n('ADMIN', 'key', 'chiave'),
                        'searchable'=> true,
                        'sortable' => false,
                        'visible' => false
                ) ,
                'src' => array (
                        'i18n' => _i18n('ADMIN', 'src', 'testo originale'),
                        'searchable'=> true,
                        'sortable' => false,
                        'visible' => true
                ) ,
                'html' => array (
                        'i18n' => _i18n('ADMIN', 'html', 'HTML'),
                        'selectable'=> true,
                        'searchable'=> true,
                        'sortable' => true,
                        'visible' => false
                ) ,
                'tbt' => array (
                        'i18n' => _i18n('ADMIN', 'tbt', 'da tradurre'),
                        'selectable'=> true,
                        'searchable'=> true,
                        'sortable' => true
                ) ,
                'in' => array (
                        'i18n' => _i18n('ADMIN', 'src', 'testo inserito'),
                        'searchable'=> true,
                        'sortable' => false,
                        'visible' => false
                ) ,
                'out' => array (
                        'i18n' => _i18n('ADMIN', 'out', 'testo finale'),
                        'searchable'=> true,
                        'sortable' => true,
                        'visible' => true
                ) 
        );
    }

    private function array_orderby() {
        $args = func_get_args();
        $data = array_shift($args);
        $args=$args[0];
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
                }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    private function visible_cols(){
        $keys = array();
        foreach(static::getAdminTableStruct() as $key=>$vals){
            if(!isset($vals['visible']) || $vals['visible']) {
                $keys[]=$key;
            }
        }
        return $keys;
    }

    function getTranslationData($args){
        $tableParams=$this->parse_args($args);

        $i18nFull = array();
        foreach (common_config('site', 'langs') as $lang=>$desc) {
            $i = file_get_contents(INSTALLDIR.'/i18n/'.$lang.'.json');
            $l = json_decode($i,true);
            if($l === null) {
                echo "There's an error parsing " . $lang.".json\n";
            } else {
                $i18nFull[$lang] = $l;
            }
        }

        $srcData = array();
        $i = file_get_contents(INSTALLDIR.'/i18n/sources.json');
        $l = json_decode($i,true);
        if($l === null) {
            echo "There's an error parsing sources.json\n";
        } else {
            $srcData = $l;
        }

        $i18nData = array ();
        $recordsTotal = 0;
        foreach($i18nFull as $lang=>$langData){
            foreach($langData as $context=>$keyvals){
                foreach($keyvals as $key=>$vals){
                    $tmp=array(
                        'lang'=> $lang,
                        'context'=> $context,
                        'key'=> $key,
                        'src'=>$srcData[$context][$key]
                    );
                    $vals=array_merge($tmp,$vals);
                    $found=true;
                    foreach($tableParams['columns'] as $col){
                        if($col['search']['value']!=''){
                            switch($col['data']){
                                case 'lang':
                                case 'context': if($col['search']['value']!=$vals[$col['data']]){
                                                    $found = false;
                                                }
                                                break;
                                case 'html':
                                case 'tbt': if(($col['search']['value'] === 'true' ? true : false)!=$vals[$col['data']]){
                                                $found = false;
                                            }
                                            break;
                                case 'src':
                                case 'out': if(strpos($vals[$col['data']],$col['search']['value'])===false){
                                                $found = false;
                                            }
                                            break;
                            }
                        }
                        if(!$found){
                            break;
                        }
                    }
                    if($found){
                        // $vals['in']=htmlentities($vals['in']);
                        $i18nData[]=array_merge($vals,array('rowId'=>$vals['lang'].'-'.$vals['context'].'-'.$vals['key']));
                    }
                    $recordsTotal++;
                }
            }
        }
        $recordsFiltered=count($i18nData);

        $colNames=$this->visible_cols();
        $orderBy=array();
        foreach($tableParams['order'] as $val){
            $orderBy[]=$colNames[$val['column']];
            $orderBy[]=(($val['dir'] === 'asc') ? SORT_ASC : SORT_DESC);
        }
        $i18nData = $this->array_orderby($i18nData, $orderBy);
        $i18nData = array_slice($i18nData,$tableParams['start'],$tableParams['length']);
        // common_debug("i18nData: ".print_r($i18nData,true));
        return array(
            'draw'=>(int)$tableParams['draw'],
            'recordsTotal' => (int)$recordsTotal,
            'recordsFiltered' => (int)$recordsFiltered,
            'data'=>$i18nData
        );
    }
}
