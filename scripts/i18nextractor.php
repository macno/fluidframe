#!/usr/bin/env php
<?php
define ( 'INSTALLDIR', realpath ( dirname ( __FILE__ ) . '/..' ) );


$dirs2scan = array('action','model','view');

require_once INSTALLDIR . '/scripts/commandline.inc';

$i18ns = array();
$sources = array();
// Load current languages
foreach (common_config('site', 'langs') as $lang=>$desc) {
    $i = file_get_contents(INSTALLDIR.'/i18n/'.$lang.'.json');
    $l = json_decode($i,true);
    if($l === null) {
        echo "There's an error parsing " . $lang.".json\n";
    } else {
        $i18ns[$lang] = $l;
    }
}

foreach ($dirs2scan as $dir) {
    _scandir(INSTALLDIR.DIRECTORY_SEPARATOR.$dir);
}


function _scandir($dir) {
    $cdir = scandir($dir);
    foreach ($cdir as $key => $value) {
        if (!in_array($value,array(".",".."))) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                _scandir($dir . DIRECTORY_SEPARATOR . $value);
            } else {
                _parseFile($dir . DIRECTORY_SEPARATOR . $value);
            }
        }
    }
}

function _parseFile($file) {
    $source = file_get_contents($file);
    
    $func = '_i18n';
    $p = 0;
    while( ($p = strpos($source, $func,$p)) !== false) {
        $p = $p + strlen($func);
        $infunc = false;
        $inarg = false;
        $issingle = false;
        $isdouble = false;
        $args = array();
        $arg = '';
        while(true) {
            $c = $source[$p];
            if(!$infunc) {
                if($c == '(') {
                    $infunc = true;
                }
            } else {
                if(!$inarg) {
                    if($c == ' ' || $c == ',') {
                        $p++;
                        continue;
                    }
                    if($c == ')') {
                        // Finished...
                        checkString($args);
                        break;
                    } else if($c == '\'') {
                        $issingle = true;
                        $inarg = true;
                        $arg = '';
                    } else if($c == "\"") {
                        $isdouble = true;
                        $inarg = true;
                        $arg = '';
                    } else {
                        $p = $p-1;
                        $arg = '';
                        $inarg = true;
                    }
                } else {
                    $skip = false;
                    if($c == '\'' && $issingle) {
                        if($source[$p-1]!= '\\') {
                            $skip = true;
                            $inarg = false;
                            $issingle = false;
                            $args[] = $arg;
                        }
                    } else if($c == "\""  && $isdouble) {
                        if($source[$p-1]!= '\\') {
                            $skip = true;
                            $inarg = false;
                            $isdouble = false;
                            $args[] = $arg;
                        }
                    } else if( ($c == ',' || $c == ' ') && !$issingle && !$isdouble) {
                        
                        $skip = true;
                        $inarg = false;
                        if(trim($arg) != '')
                            $args[] = $arg;
                    }
                    if(!$skip) {
                        $arg .=$c;
                    }
                }
            }
            $p++;
        }
        
    }
}


function checkString($arg) {
    global $i18ns;
    global $sources;
    if(!isset($sources[$arg[0]]))
        $sources[$arg[0]]=array();
    $sources[$arg[0]][$arg[1]]=$arg[2];
    foreach (common_config('site', 'langs') as $lang=>$desc) { 
        if(isset($i18ns[$lang][$arg[0]]) && isset($i18ns[$lang][$arg[0]][$arg[1]])) {
            // is to be translated?
            if(isset($i18ns[$lang][$arg[0]][$arg[1]]['tbt']) && $i18ns[$lang][$arg[0]][$arg[1]]['tbt']) {
                // Update it by law
                $i18ns[$lang][$arg[0]][$arg[1]]['in']=$arg[2];
                $i18ns[$lang][$arg[0]][$arg[1]]['out']=$arg[2];
                $i18ns[$lang][$arg[0]][$arg[1]]['html']=(isset($arg[3]) ? $arg[3] : false);
            }
        } else {
            
            if(!isset($i18ns[$lang][$arg[0]])) {
                $i18ns[$lang][$arg[0]] = array();
            }
            $i18ns[$lang][$arg[0]][$arg[1]] = array(
                    'tbt'=>true, // ToBeTranslated
                    'html'=>(isset($arg[3]) ? $arg[3] : false),
                    'in'=>$arg[2],
                    'out'=>$arg[2]
            );
        }
    }
}

file_put_contents(INSTALLDIR.'/i18n/sources.json',json_encode($sources,JSON_PRETTY_PRINT));

foreach (common_config('site', 'langs') as $lang=>$desc) {
    file_put_contents(INSTALLDIR.'/i18n/'.$lang.'.json',json_encode($i18ns[$lang],JSON_PRETTY_PRINT));
}
