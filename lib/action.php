<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class Action {
    
    /**
     *
     * @var Account
     */
    var $account = false;
    var $lang = false;
    var $args = false;
    private $renderParams = array ();
    var $menu;
    function __construct($lang = false) {
        global $_cur;
        
        if ($lang)
            $this->setLang ( $lang );
        $this->setHomepage ();
        $this->setSitetitle ();
    }
    function prepare($args) {
        global $_cur;
        $this->args = $args;
        if (isset ( $this->args ['lang'] )) {
            $lang = $this->args ['lang'];
            $langs = common_config ( 'site', 'langs' );
            if (! isset ( $langs [$lang] )) {
                common_redirect ( common_get_route ( 'index' ) );
            }
            $this->setLang ( $lang );
        }
        if (common_logged_in ()) {
            $this->account = $_cur;
            $this->renderParams ['common_actions'] ['logout'] = common_get_route ( 'logout', array (
                    'lang' => $this->lang 
            ) );
            $this->menu = $this->account->getMenu ( $this->lang );
        } else {
            $this->menu = MenuBuilder::build ( MenuBuilder::getAnonRole (), $this->lang );
        }
        return false;
    }
    function handle() {
        $this->renderOptions['otherlang'] = ($this->lang == 'it') ? 'en' : 'it';
    }
    function isPost() {
        return ($_SERVER ['REQUEST_METHOD'] == 'POST');
    }
    function getMethod() {
        return $_SERVER ['REQUEST_METHOD'];
    }
    function title() {
        return '';
    }
    public function render($page, Array $params = array()) {
        $params = array_merge ( $this->renderParams, $params );
        $typekit = array (
                'tkenabled' => (common_config ( 'typekit', 'enabled' ) ? true : false),
                'tkasync' => (common_config ( 'typekit', 'async' ) ? "true" : "false"),
                'tkurl' => common_config ( 'typekit', 'url' ) 
        );
        $params ['renderpage'] = 'page-' . $page;
        $params = array_merge ( $params, $typekit );
        $this->handleHreflangs ( $params );
        $this->handleTitle ( $params );
        $this->handleStylesheets ( $params );
        $this->handleJavascripts ( $params );
        
        $params['siteTitle'] = $siteTitle = common_config ( 'site', 'title', 'NO TITLE' );
        $this->setParams ( $params );
        $tplFile = INSTALLDIR . '/view/' . $page . '.php';
        if (! file_exists ( $tplFile )) {
            throw new FluidframeException ( 'View page not found' );
        }
        require_once $tplFile;
    }
    private function setLang($lang) {
        $this->lang = $lang;
        $this->renderParams ['lang'] = $this->lang;
    }
    private function setSitetitle() {
        $this->renderParams ['site'] ['title'] = common_config ( 'site', 'title' );
    }
    private function setHomepage() {
        $this->renderParams ['homepage'] = common_get_route ( 'home', array (
                'lang' => $this->lang 
        ) );
    }
    private function setParams(&$params) {
        foreach ( $params as $key => $val ) {
            $this->{$key} = $val;
        }
        unset ( $params );
    }
    protected function getJavascripts() {
        return array ();
    }
    protected function getStylesheets() {
        return array ();
    }
    function getHreflangs() {
        return array ();
    }
    private function handleHreflangs(&$params = array()) {
        $params ['hreflangs'] = $this->getHreflangs ();
    }
    private function handleStylesheets(&$params = array()) {
        $siteCss = common_template ( 'stylesheets' );
        $this->prepareStylesheet ( $params, $siteCss );
        $pageCss = $this->getStylesheets ();
        $this->prepareStylesheet ( $params, $pageCss );
    }
    private function prepareStylesheet(&$params = array(), $stylesheets = array()) {
        foreach ( $stylesheets as $css ) {
            if (is_array ( $css )) {
                $params ['stylesheets'] [] = $css;
            } else {
                $params ['stylesheets'] [] = array (
                        'href' => $css,
                        'media' => 'all' 
                );
            }
        }
    }
    protected function handleJavascripts(&$params = array()) {
        $params ['javascripts'] = array_merge ( common_template ( 'javascripts' ), $this->getJavascripts () );
    }
    private function handleTitle(&$params = array()) {
        $title = $this->title ();
        $siteTitle = common_config ( 'site', 'title', 'NO TITLE' );
        if (empty ( $title ))
            $params ['title'] = $siteTitle;
        else
            $params ['title'] = $title . ' | ' . $siteTitle;
    }
    
    // Utilities
    /**
     * Returns query argument or default value if not found
     *
     * @param string $key
     *            requested argument
     * @param string $def
     *            default value to return if $key is not provided
     *            
     * @return boolean is read only action?
     */
    function arg($key, $def = null) {
        if (array_key_exists ( $key, $this->args )) {
            return $this->args [$key];
        } else {
            return $def;
        }
    }
    
    /**
     * Returns trimmed query argument or default value if not found
     *
     * @param string $key
     *            requested argument
     * @param string $def
     *            default value to return if $key is not provided
     *            
     * @return boolean is read only action?
     */
    function trimmed($key, $def = null) {
        $arg = $this->arg ( $key, $def );
        return is_string ( $arg ) ? trim ( $arg ) : $arg;
    }
    function boolean($key, $def = false) {
        $arg = strtolower ( $this->trimmed ( $key ) );
        
        if (is_null ( $arg )) {
            return $def;
        } else if (in_array ( $arg, array (
                'true',
                'yes',
                '1',
                'on' 
        ) )) {
            return true;
        } else if (in_array ( $arg, array (
                'false',
                'no',
                '0' 
        ) )) {
            return false;
        } else {
            return $def;
        }
    }
}
