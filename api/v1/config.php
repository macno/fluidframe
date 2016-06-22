<?php
/**
 * Fluidframe - Fluidware Web Framework
 * Copyright (C) 2015-2016, Fluidware
 *
 * @author: Michele Azzolari michele@fluidware.it
 *
 */
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class ConfigApiAction extends ApiAction {
    function prepare($args) {
        return true;
    }
    function get() {
        echo json_encode ( array (
                'id' => common_config ( 'site', 'code' ),
                'version'=>SITE_VERSION
        ) );
    }
}