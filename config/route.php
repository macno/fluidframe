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
        // Gestione Traduzione
        $router->add('admintranslationapi', '/admin/api/translation/datatable');
        $router->add('adminsavetranslationapi', '/admin/api/translation/save');
        $router->add('admintranslationlist', '/admin/translation');

        // Preview markdown & html
        $router->add('markdownpreview', '/utils/markdown/preview');
        $router->add('htmlpreview', '/utils/html/preview');
        $router->add('adminconversionapi', '/admin/api/conversion');

        // Gestione Tabelle
        $router->add('admintableapi', '/admin/api/{model}/datatable')
            ->addTokens(array('model' => ('[a-zA-Z0-9-\s_]+')));
        $router->add('admintablelist', '/admin/{model}')
            ->addTokens(array('model' => ('[a-zA-Z0-9-\s_]+')));

        $router->add('adminroleedit', '/admin/role/{rowid}')
            ->addTokens(array('rowid' => ('[0-9]+')));

        $router->add('robots', '/robots.txt');
    }
}
