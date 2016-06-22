<?php
class ApiAction extends Action {
    function prepare($args) {
        parent::prepare($args);
        global $_lang;
        $_lang=$args['lang'];
        return true;
    }
    function contentType() {
        return 'application/json; charset=utf-8';
    }
    function get() {
        throw new HttpFluidException ( array (
                405,
                'Method Not Allowed'
        ) );
    }
    function post() {
        throw new HttpFluidException ( array (
                405,
                'Method Not Allowed'
        ) );
    }
    function head() {
        throw new HttpFluidException ( array (
                405,
                'Method Not Allowed'
        ) );
    }
    function put() {
        throw new HttpFluidException ( array (
                405,
                'Method Not Allowed'
        ) );
    }
    function delete() {
        throw new HttpFluidException ( array (
                405,
                'Method Not Allowed'
        ) );
    }
    function options() {
        throw new HttpFluidException ( array (
                405,
                'Method Not Allowed'
        ) );
    }
}
