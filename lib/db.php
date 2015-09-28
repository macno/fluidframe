<?php
$options = &PEAR::getStaticProperty ( 'DB_DataObject', 'options' ); // Maybe it's useless...

$options = array (
        'database' => 'MAYBE IT\'S BETTER YOU SET THIS IN YOUR CONFIG.PHP',
        'schema_location' => INSTALLDIR . '/model',
        'class_location' => INSTALLDIR . '/model',
        'require_prefix' => 'model/',
        'class_prefix' => '',
        'mirror' => null,
        'utf8' => true,
        'db_driver' => 'MDB2',
        'quote_identifiers' => false,
        'type' => 'mysql',
        'schemacheck' => 'script', // 'runtime' or 'script'
        'log_queries' => false, // true to log all DB queries
        'log_slow_queries' => 0,
        'debug' => 2,
        'dont_die'=>true,
        'result_buffering' => false 
);

$options = array_merge ( $options, $dbOptions );

//set PEAR error handling to use regular PHP exceptions
function PEAR_ErrorToPEAR_Exception($err) {
    //DB_DataObject throws error when an empty set would be returned
    //That behavior is weird, and not how the rest of StatusNet works.
    //So just ignore those errors.
    if ($err->getCode() == DB_DATAOBJECT_ERROR_NODATA) {
        return;
    }

    $msg      = $err->getMessage();
    $userInfo = $err->getUserInfo();

    // Log this; push the message up as an exception

    common_error("PEAR Error: $msg ($userInfo)");

    // HACK: queue handlers get kicked by the long-query killer, and
    // keep the same broken connection. We die here to get a new
    // process started.

    if (php_sapi_name() == 'cli' && preg_match('/nativecode=2006/', $userInfo)) {
        common_error( "Lost DB connection; dying.");
        exit(100);
    }

    if ($err->getCode()) {
        throw new PEAR_Exception($msg, $err, $err->getCode());
    } else {
        throw new PEAR_Exception($msg, $err);
    }
}

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'PEAR_ErrorToPEAR_Exception');

function testDb() {
    $dbTester = new DBTester();
    try {
        $dbTester->test();
    } catch ( Exception $e ) {
        throw new FluidframeException('Invalid Database setup: ' . $e->getMessage());
    }
}



class DBTester {
    
    function test() {
        
            $config = new Config ();
            $config->limit ( 1 );
            $config->find ( true );
            $config->free ();
    }
    
}

// testDb(); // It breaks first checkschema!

Config::loadSettings ();