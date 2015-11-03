<?php 
if (!defined('FLUIDFRAME')) {
	exit(1);
}

$i18n = false;

function _i18n($context, $key, $deflt,$html = false) {
    global $i18n, $_lang;
    
    if($i18n === false) {

        $i = file_get_contents(INSTALLDIR.'/i18n/'.$_lang.'.json');
        $i18n = json_decode($i,true);
        if($_lang != common_config('site','language')) {
            // Load default language as fallback..
            $id = file_get_contents(INSTALLDIR.'/i18n/'.common_config('site','language').'.json');
            $i18nd = json_decode($id,true);
            $i18n = array_replace_recursive ($i18nd,$i18n);
        }
    }
    if(isset($i18n[$context]) && isset($i18n[$context][$key])) {
        return $i18n[$context][$key]['out'];
    }
    return $deflt;
}
