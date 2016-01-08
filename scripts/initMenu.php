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
        ) 
);

$role_menu = array(
        array('ADMIN','ADMIN',100),
        array('ANON','ANON',100),
);

$menu_menuitem = array(
        array('ANON','LOGIN',900),
);

foreach ( $roles as $role ) {
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

foreach ( $menus as $menu ) {
    $dbMenu = new Menu ();
    $dbMenu->name = $menu ['name'];
    $dbMenu->description = $menu ['description'];
    $dbMenu->status = 1;
    $dbMenu->created = common_sql_now ();
    $menuId = $dbMenu->insert ();
}

foreach ( $menuItems as $menuitem ) {
    $dbMenuItem = new MenuItem ();
    $dbMenuItem->name = $menuitem ['name'];
    $dbMenuItem->action = $menuitem ['action'];
    $dbMenuItem->params = $menuitem ['params'];
    $dbMenuItem->status = 1;
    $dbMenuItem->created = common_sql_now ();
    $dbMenuItem->insert ();
}

foreach ( $role_menu as $rm ) {
    $role = Role::staticGet('name',$rm[0]);
    $menu = Menu::staticGet('name',$rm[1]);
    
    $roleMenuDb = new Role_menu();
    $roleMenuDb->role_id = $role->id;
    $roleMenuDb->menu_id = $menu->id;
    $roleMenuDb->weight = $rm[2];
    $roleMenuDb->created = common_sql_now();
    
    $roleMenuDb->insert();
}

foreach ( $menu_menuitem as $mmi ) {
    $menu = Menu::staticGet('name',$mmi[0]);
    $menuItem = MenuItem::staticGet('name',$mmi[1]);
    
    $menuMenuItemDb = new Menu_menuitem();
    $menuMenuItemDb->menu_id = $menu->id;
    $menuMenuItemDb->menuitem_id = $menuItem->id;
    $menuMenuItemDb->weight = $mmi[2];
    $menuMenuItemDb->created = common_sql_now();

    $menuMenuItemDb->insert();
}


echo ("Done!\n");
