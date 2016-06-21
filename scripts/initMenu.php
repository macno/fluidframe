#!/usr/bin/env php
<?php
define ( 'INSTALLDIR', realpath ( dirname ( __FILE__ ) . '/..' ) );

$helptext = <<<END_OF_CHECKSCHEMA_HELP
php initmenu.php

END_OF_CHECKSCHEMA_HELP;

require_once INSTALLDIR . '/scripts/commandline.inc';

$roles = array (
        array (
                'name' => 'ANON',
                'description' => 'ANONYMOUS ROLE'
        ),
        array (
                'name' => 'ADMIN',
                'description' => 'ADMINISTRATOR ROLE'
        )
);

$menus = array (
        array (
                'name' => 'ANON',
                'description' => 'ANONYMOUS MENU'
        ),
        array (
                'name' => 'ADMIN',
                'description' => 'ADMINISTRATOR MENU'
        )
);

$menuItems = array (
        array (
                'name' => 'LOGIN',
                'action' => 'login',
                'params' => 'lang=$lang'
            ),
        array(
                'name' => 'TRADUZIONI',
                'action' => 'admintranslationlist'
        )
);

$role_menu = array(
        array('ADMIN','ADMIN',100),
        array('ANON','ANON',100),
);

$menu_menuitem = array(
        array('ANON','LOGIN',900),
        array('ADMIN','TRADUZIONI',900),
);

// Clear all

$deletes= array(
        "delete from menu_menuitem",
        "delete from menuitem",
        "delete from role_menu",
        "delete from menu"
);

$config = new Config();
foreach ($deletes as $delete) {
    $config->query($delete);
}

foreach ( $roles as $role ) {
    $dbRole = Role::staticGet('name',$role['name']);
    if(!$dbRole) {
        $dbRole = new Role ();
        $dbRole->name = $role ['name'];
        $dbRole->description = $role ['description'];
        $dbRole->status = 1;
        $dbRole->created = common_sql_now ();
        $roleId = $dbRole->insert ();
        if($role['name'] == 'ANON') {
            Config::save('role', 'anon', $roleId);
        }
    }
}

foreach ( $menus as $menu ) {
    $dbMenu = new Menu ();
    $dbMenu->name = $menu ['name'];
    $dbMenu->description = $menu ['description'];
    $dbMenu->status = 1;
    $dbMenu->created = common_sql_now ();
    try {
        $menuId = $dbMenu->insert ();
    } catch(Exception $e) {

    }
}

foreach ( $menuItems as $menuitem ) {
    $dbMenuItem = new MenuItem ();
    $dbMenuItem->name = $menuitem ['name'];
    $dbMenuItem->action = $menuitem ['action'];
    if(isset($menuitem['params'])){
        $dbMenuItem->params = $menuitem ['params'];
    }
    $dbMenuItem->status = 1;
    $dbMenuItem->created = common_sql_now ();
    try {
        $dbMenuItem->insert ();
    } catch(Exception $e) {

    }
}

foreach ( $role_menu as $rm ) {
    $role = Role::staticGet('name',$rm[0]);
    $menu = Menu::staticGet('name',$rm[1]);

    $roleMenuDb = new Role_menu();
    $roleMenuDb->role_id = $role->id;
    $roleMenuDb->menu_id = $menu->id;
    $roleMenuDb->weight = $rm[2];
    $roleMenuDb->created = common_sql_now();
    try {
        $roleMenuDb->insert();
    } catch(Exception $e) {

    }
}

foreach ( $menu_menuitem as $mmi ) {
    $menu = Menu::staticGet('name',$mmi[0]);
    $menuItem = MenuItem::staticGet('name',$mmi[1]);

    $menuMenuItemDb = new Menu_menuitem();
    $menuMenuItemDb->menu_id = $menu->id;
    $menuMenuItemDb->menuitem_id = $menuItem->id;
    $menuMenuItemDb->weight = $mmi[2];
    $menuMenuItemDb->created = common_sql_now();

    try {
        $menuMenuItemDb->insert();
    } catch(Exception $e) {

    }
}


echo ("Done!\n");
