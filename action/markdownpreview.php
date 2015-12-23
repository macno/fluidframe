<?php
/**
 * Fluidframe - Fluidware Web Framework
 * Copyright (C) 2011, Fluidware
 * 
 * @author: Michele Azzolari michele@fluidware.it
 * 
 */

if (!defined('FLUIDFRAME')) {
    exit(1);
}

class MarkdownPreviewAction extends Action {

    function prepare($args){
        parent::prepare($args);
        return true;
    }

    function API() {
        return Parsedown::instance()->text($this->trimmed('data'));
    }

    function handle() {
        parent::handle();
        header('Content-Type: text/plain; charset=utf-8');
        $result = $this->API($model);
        if(!empty($result)){
            echo $result;
        }
    }


}

