<?php 
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class Route {
    
    static function setRoutes($router) {
        
        $router->add('index', '/');
        $router->add('home', '/{lang}/')
            ->addTokens(array(
            	'lang'=>'[a-z]{2}'
            )
        );
        $router->add('login', '/{lang}/auth/login')
            ->addTokens(array(
                'lang'=>'[a-z]{2}'
            )
        );
        $router->add('logout', '/{lang}/auth/logout')
            ->addTokens(array(
                'lang'=>'[a-z]{2}'
            )
        );
        $router->add('admintableapi', '/admin/api/{model}/datatable')
            ->addTokens(array('model' => ('[a-zA-Z0-9-\s_]+')));
        $router->add('admintablelist', '/admin/{model}')
            ->addTokens(array('model' => ('[a-zA-Z0-9-\s_]+')));
        $router->add('robots', '/robots.txt');
    }
}
