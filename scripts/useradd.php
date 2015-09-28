#!/usr/bin/env php
<?php
/*
 * StatusNet - a distributed open-source microblogging tool
 * Copyright (C) 2008-2011 StatusNet, Inc.
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

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 'u:e:p:';
$longoptions = array('username=','email=','password=');

$helptext = <<<END_OF_CHECKSCHEMA_HELP
php useradd.php [options]

-u --username=     Username
-e --email=        Email
-p --password=     Password

END_OF_CHECKSCHEMA_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';



$fullname = trim(get_option_value('u', 'username'));
$email = trim(get_option_value('e', 'email'));
$password =  trim(get_option_value('p', 'password'));

if(empty($fullname)) {
 echo('username required');
 exit();
}
if(empty($email)) {
 echo('email required');
 exit();
}
if(empty($password)) {
 echo('password required');
 exit();
}
$profile = new Profile();
$profile->fullname = $fullname;
$profile->email = $email;

$profile->created = common_sql_now();
$profile_id = $profile->insert();

if (!$profile_id) {
 common_log_db_error($profile, 'INSERT', __FILE__);
 
 exit();
}

$profile_role = new Profile_role();
$profile_role->profile_id = $profile_id;
$profile_role->role= Profile_role::SUPERADMIN;
$profile_role->created = common_sql_now();
$profile_role->insert();


$pnew = Profile::staticGet($profile_id);
$orig = clone($pnew);
$pnew->password = common_munge_password($password, $profile_id) ;
$pnew->update($orig);

echo ("Done!");