<?php
/**
 * Fluidframe v2 - Fluidware Web Framework
 * Copyright (C) 2015, Fluidware
 * 
 * @author: Michele Azzolari michele@fluidware.it
 * 
 */

if (!defined('FLUIDFRAME')) { exit(1); }

class ServerException extends Exception {
    public function __construct($message = null, $code = 400) {
        parent::__construct($message, $code);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
