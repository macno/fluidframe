<?php 
if (!defined('FLUIDFRAME')) {
	exit(1);
}

$__t = array();

function _t($str,$context='GENERIC') {
	global $_default_language, $_lang, $__t;
	$strid = md5($str);
	if(empty($context)) {
	    $context = 'GENERIC';
	}
	if(isset($__t[$context][$strid])) {
		return $__t[$context][$strid];
	}

	$gettext = new Gettext();
	$gettext->context=$context;
	$gettext->lang=$_lang;
	$gettext->str = $strid;
	if(!$gettext->find(true)) {
		// Add new line.
		// Notify?
		$gettext->created=common_sql_now();
		$gettext->translation=  $str ;
		$gettext->translated= ($_default_language == $_lang) ? 1 : 0;
		$gettext->original_text = $str;
		$gettext_id = $gettext->insert();
		if(!$gettext_id) {
		    throw new FluidframeException('Unable to insert translation row');
		}
	}
	$__t[$context][$strid]=$gettext->translation;
	return $gettext->translation;
}

function _tl($str,$context='GENERIC', $lang) {
 global $_default_language, $__t;
 $strid = md5($str);

 if(isset($__t[$context][$strid])) {
  return $__t[$context][$strid];
 }

 $gettext = new Gettext();
 $gettext->context=$context;
 $gettext->lang=$lang;
 $gettext->str = $strid;
 if(!$gettext->find(true)) {
  // Add new line.
  // Notify?
  $gettext->created=common_sql_now();
  $gettext->translation=($_default_language == $lang) ? $str : '*'.$str.'*';
  $gettext->insert();
 }
 $__t[$context][$strid]=$gettext->translation;
 return $gettext->translation;
}

