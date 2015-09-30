<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class Sbadmin2Action extends Action {
    protected $renderOptions = array ();
    function prepare($args) {
        parent::prepare ( $args );
        
        $this->renderOptions ['sidebar'] = $this->menu;
    }
    function handle() {
    }
}