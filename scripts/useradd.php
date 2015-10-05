#!/usr/bin/env php
<?php

define ( 'INSTALLDIR', realpath ( dirname ( __FILE__ ) . '/..' ) );

$shortoptions = 'u:e:p:';
$longoptions = array (
        'username=',
        'email=',
        'password=' 
);

$helptext = <<<END_OF_CHECKSCHEMA_HELP
php useradd.php [options]

-u --username=     Username
-e --email=        Email
-p --password=     Password

END_OF_CHECKSCHEMA_HELP;

require_once INSTALLDIR . '/scripts/commandline.inc';

$fullname = trim ( get_option_value ( 'u', 'username' ) );
$email = trim ( get_option_value ( 'e', 'email' ) );
$password = trim ( get_option_value ( 'p', 'password' ) );

if (empty ( $fullname )) {
    echo ('username required');
    exit ();
}
if (empty ( $email )) {
    echo ('email required');
    exit ();
}
if (empty ( $password )) {
    echo ('password required');
    exit ();
}

$role = Role::staticGet('name','ADMIN');

if(!$role) {
    echo "Role ADMIN does not exist. Please execute initMenu first";
    exit (1);
}
$profile = new Account ();
$profile->fullname = $fullname;
$profile->email = $email;
$profile->status = 1;
$profile->role_id = $role->id;
$profile->created = common_sql_now ();
$profile_id = $profile->insert ();

if (! $profile_id) {
    common_log_db_error ( $profile, 'INSERT', __FILE__ );
    
    exit ();
}

$profile = Account::staticGet($profile_id);
if($profile) {
    $orig = clone ($profile);
    $profile->password = common_munge_password ( $password, $profile_id );
    $profile->update ( $orig );
}

echo ("Done!\n");