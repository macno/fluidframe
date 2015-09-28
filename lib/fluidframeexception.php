<?php
/**
 * Fluidframe v2 - Fluidware Web Framework
 * Copyright (C) 2015, Fluidware
 * 
 * @author: Michele Azzolari michele@fluidware.it
 * 
 */

if (!defined('FLUIDFRAME')) { exit(1); }

class FluidframeException extends Exception {
    
    public function __construct($message = null) {
        parent::__construct($message);
    }

    public function __toString() {
        return __CLASS__ . ": {$this->message}\n";
    }
}
