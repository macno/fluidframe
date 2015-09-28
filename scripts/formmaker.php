#!/usr/bin/env php
<?php

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 's:n:p:b:';
$longoptions = array('schema=','plural=','single=','baseaction=');

$helptext = <<<END_OF_CHECKSCHEMA_HELP
php formmaker.php [options]

            -s --schema=     Class name to be formified
            -n --single=    Single form
            -p --plural=    Plural form
            -b --baseaction= Base action

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


require_once INSTALLDIR.'/utils/classHelper.php';

$obj = new $cls;

$classHelper = new classHelper($obj->__table, $schema['fields'], $baseAction, $single, $plural);
$classHelper->createAll();


