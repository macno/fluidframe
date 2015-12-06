<?php

class MenuBuilder {
    
    public static function getAnonRole() {
        return common_config('role', 'anon');
    }
    
    /**
     * Builds the menu from role_id
     * 
     * @param int $role the role id
     * @return boolean|multitype: false if role_id is empty, otherwise an array 
     */
    static function build($role, $lang = false) {
        
        if(empty($role)) {
            return false;
        }
        
        $menu = array();
        
        $rm = new Role_menu();
        $qry = "select rm.menu_id 
                from role_menu rm, menu m
                where rm.menu_id = m.id
                and m.status = 1 
                and rm.role_id = ".(int)$role."
                order by rm.weight asc";
        
        $rm->query($qry);
        
        while($rm->fetch()) {
            list($submenu,$menu_name) = self::getMenu($rm->menu_id, 0, $lang);
            $menu = array_merge($menu, $submenu);
        }
        $rm->free();
        return $menu;
    }
    
    private static function getMenu($menu_id, $level, $lang = false) {
        if($level >= 3) {
            throw  new FluidframeException('Menu only supports 2 sub-levels');
        }
        
        
        $mi = new MenuItem();
        $qry = "select m.name menu_name, mmi.sub_menu_id sub_menu_id, mi.id, mi.name, mi.action, mi.params , mi.status 
        from menu_menuitem mmi 
        left outer JOIN menuitem mi on (mmi.menuitem_id = mi.id) 
        left outer join menu m on (mmi.menu_id = m.id)
        where m.status = 1
        and mmi.menu_id = ${menu_id}
        order by mmi.weight asc";
        $mi->query($qry);
        
        $menu_name = '';
        $first = true;
        while($mi->fetch()) {
            if($first) {
                $menu_name = $mi->menu_name;
                $first = false;
            }
            if($mi->sub_menu_id != null) {
                
                list($submenu,$menu_name) = self::getMenu($mi->sub_menu_id, $level+1, $lang);
                $menuz = new stdClass();
                $menuz->class = 'menu_item_'.$mi->sub_menu_id;
                $menuz->title = $menu_name;
                $menuz->href = '#';
                $menuz->items = $submenu;
                $menu[] = $menuz;
            } else {
                if($mi->status) {
                    $menuz = new stdClass();
                    $menuz->class = 'menu_item_'.$mi->id;
                    $menuz->title = $mi->name;
                    
                    $params = null;
                    if($lang && !empty($mi->params)) {
                        
                        if(strpos($mi->params, 'lang') !== false) {
                            $params= array('lang'=>$lang);
                        }
                    }
                    $href = common_get_route($mi->action,$params);
                    $menuz->href = empty($href) ? '/' : $href;
                    
                    $menu[] = $menuz;
                }
            }
        }
        $mi->free();
        return array($menu, $menu_name);
    }
    
}
