<?php
/**
 * Fluidframe - Fluidware Web Framework
 * Copyright (C) 2015-2016, Fluidware
 *
 * @author: Michele Azzolari michele@fluidware.it
 *
 */
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class TranslationsApiAction extends ApiAction {
    var $context, $key;
    function prepare($args) {
        parent::prepare($args);
        $this->context = $this->trimmed('context');
        $this->key = $this->trimmed('key');
        return true;
    }
    function get() {
        $ret = [];
        $langs = [];

        $srcData = array();
        $i = file_get_contents(INSTALLDIR.'/i18n/sources.json');
        $l = json_decode($i,true);
        if($l === null) {
            echo json_encode(["error"=> "There's an error parsing sources.json\n" ]);
            return;
        } else {
            $srcData = $l;
        }

        $ret['context'] = $this->context;
        $ret['key'] = $this->key;
        $ret['src'] = $srcData[$this->context][$this->key];
        foreach(common_config('site', 'langs') as $lang => $tmp){
            $i = file_get_contents(INSTALLDIR.'/i18n/'.$lang.'.json');
            $i18n = json_decode($i,true);
            if(isset($i18n[$this->context]) && isset($i18n[$this->context][$this->key])) {
                $langs[$lang]= $i18n[$this->context][$this->key];
                $langs[$lang]['lang']= $lang;
                $langs[$lang]['context']= $this->context;
                $langs[$lang]['key']= $this->key;
                if($lang == common_config('site', 'language')){
                    $ret['html'] = $i18n[$this->context][$this->key]['html'];
                    $ret['code'] = $i18n[$this->context][$this->key]['code'];
                }
            }
        }
        $ret['langs'] = $langs;
        echo json_encode($ret);
    }
}
