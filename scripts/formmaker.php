#!/usr/bin/env php
<?php

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 'fs:n:p:b: ';
$longoptions = array('force', 'schema=','plural=','single=','baseaction=');

$helptext = <<<END_OF_CHECKSCHEMA_HELP
php formmaker.php [options]

            -s --schema=     Class name to be formified
            -n --single=    Single form
            -p --plural=    Plural form
            -b --baseaction= Base action
            -f --force=      Force to overwrite files

END_OF_CHECKSCHEMA_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

if (have_option('s', 'schema')) {
    $cls = trim(get_option_value('s', 'schema'));
} 

if(empty($cls)) {
     print "You must be supply a valid class name\n";
    exit(1);
}

if (have_option('n', 'single')) {
    $single = trim(get_option_value('n', 'single'));
}

if (have_option('p', 'plural')) {
    $plural = trim(get_option_value('p', 'plural'));
}

if (have_option('b', 'baseaction')) {
    $baseAction = trim(get_option_value('b', 'baseaction'));
}

$force = false;
if (have_option('f', 'force')) {
    $force = true;
    // $force = (trim(get_option_value('f', 'force')) == 'yes') ? true : false;
}

if(empty($single)) {
    print "You must be supply a valid single name\n";
    exit(1);
}

if(empty($plural)) {
    print "You must be supply a valid plural name\n";
    exit(1);
}


$schema = call_user_func(array($cls, 'schemaDef'));


if(!$schema) {
    print "Unable to load schema\n";
    exit(2);
}


$obj = new $cls;

// print_r($obj->getAdminTableStruct());

// Generazione della form in JADE
$status = <<<STATUS
            .checkbox
                label
                    input#status(type="checkbox", name="status", value="1",
                        checked=this.role_status == 1)
                    | Status

STATUS;
$fields = $obj->getAdminTableStruct();
$form = <<<FORM
extends ../layouts/adminlayout.jade

block content
    #page-wrapper
        form(method="POST")
            input#id(type="hidden", value="#{this.role_id}")

FORM;
foreach($fields as $field=>$attributes){
    if(($field != 'id')&&($field != 'status')){
        $rules=$attributes['rules'];
        $required=($rules['required']) ? ', required' : '';
        $label = ucfirst($field);
        $form .= <<<FIELD
            .form-group(class=this.inputError['$field'] ? 'has-error' : '')
                label(for="$field") $label
                input#$field.form-control(type="text", name="$field",
                    value="#{this.${cls}_$field}" $required)
                span.text-danger(class=this.inputError['$field'] ? '' : 'hidden')
                    | #{this.inputError['$field']}

FIELD;
    }
    if($field == 'status'){
        $form .= $status;
    }
}
$form .= <<<ENDFORM
            if this.role_id
                .checkbox
                    label
                        input#remove(type="checkbox", name="remove")
                        | Cancella ?
            button#save.btn.btn-default Salva
            button#cancel.btn.btn-default Annulla

block pageJavascript
    if this.jsfile
        script(src=this.jsfile)
    script(src="/js/adminaddedit.js")

ENDFORM;

$fileJade = INSTALLDIR .'/viewsrc/jade-sbadmin2/pages/admin'. $cls .'form.jade';
if((!file_exists($fileJade))||($force)){
    file_put_contents($fileJade, $form);
}

// Generazione del template JAVASCRIPT
$fileJS = INSTALLDIR .'/js/admin'. $cls .'.js';
if((!file_exists($fileJS))||($force)){
    $js = file_get_contents(INSTALLDIR . '/scripts/template/adminmodel.js');
    file_put_contents($fileJS, $js);
}

// Generazione Action admin<model>add
$className = ucfirst($cls);
$lowerClassName = strtolower($cls);
foreach($fields as $field=>$attributes){
    if(($field != 'id')&&($field != 'status')){
        $prepareFields .= "        ".'$this->field[\''.$field.'\'] = $this->trimmed(\''.$field.'\');'."\n";
        $errorFields .= "                ".'$this->renderOptions[\''.$cls.'_'.$field.'\'] = $this->obj->'.$field.';'."\n";
    }
    if($field == 'status'){
        $prepareFields .= "        ".'$this->field[\''.$field.'\'] = (int) $this->trimmed(\''.$field.'\');'."\n";
        $errorFields .= "                ".'$this->renderOptions[\''.$cls.'_'.$field.'\'] = $this->obj->'.$field.';'."\n";
    }
}
$fileAdd = INSTALLDIR .'/action/admin'. $cls .'add.php';
if((!file_exists($fileAdd))||($force)){
    $adminmodeladd = file_get_contents(INSTALLDIR . '/scripts/template/adminmodeladd.php');
    $adminmodeladd = str_replace('/* PREPAREFIELDS */', $prepareFields, $adminmodeladd);
    $adminmodeladd = str_replace('/* ERRORFIELDS */', $errorFields, $adminmodeladd);
    $adminmodeladd = str_replace('%MODEL%', $className, $adminmodeladd);
    $adminmodeladd = str_replace('%model%', $lowerClassName, $adminmodeladd);
    file_put_contents($fileAdd, $adminmodeladd);
}

// Generazione Action admin<model>edit
$prepareFields = '';
$renderFields = '';
$singleUc = ucfirst($single);
foreach($fields as $field=>$attributes){
    if($field == 'id'){
        $prepareFields .= "        ".'$this->field[\''.$field.'\'] = (int) $this->trimmed(\''.$field.'\');'."\n";
        $renderFields .= "                ".'$this->renderOptions[\''.$cls.'_'.$field.'\'] = $this->obj->'.$field.';'."\n";
    }
    if(($field != 'id')&&($field != 'status')){
        $prepareFields .= "        ".'$this->field[\''.$field.'\'] = $this->trimmed(\''.$field.'\');'."\n";
        $renderFields .= "                ".'$this->renderOptions[\''.$cls.'_'.$field.'\'] = $this->obj->'.$field.';'."\n";
    }
    if($field == 'status'){
        $prepareFields .= "        ".'$this->field[\''.$field.'\'] = (int) $this->trimmed(\''.$field.'\');'."\n";
        $renderFields .= "                ".'$this->renderOptions[\''.$cls.'_'.$field.'\'] = $this->obj->'.$field.';'."\n";
    }
}
$fileEdit = INSTALLDIR .'/action/admin'. $cls .'edit.php';
if((!file_exists($fileEdit))||($force)){
    $adminmodeledit = file_get_contents(INSTALLDIR . '/scripts/template/adminmodeledit.php');
    $adminmodeledit = str_replace('/* PREPAREFIELDS */', $prepareFields, $adminmodeledit);
    $adminmodeledit = str_replace('/* RENDERFIELDS */', $renderFields, $adminmodeledit);
    $adminmodeledit = str_replace('%MODEL%', $className, $adminmodeledit);
    $adminmodeledit = str_replace('%model%', $lowerClassName, $adminmodeledit);
    $adminmodeledit = str_replace('%SINGLE%', $singleUc, $adminmodeledit);
    file_put_contents($fileEdit, $adminmodeledit);
}
