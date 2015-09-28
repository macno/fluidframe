<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class RobotsAction extends Action {
    function prepare($params) {
        return true;
    }
    function handle() {
        header ( 'Content-Type: text/plain' );
        echo "User-Agent: *\n";
        if (common_config ( 'site', 'robotsallow' )) {
            echo "Allow: /\n";
        } else {
            echo "Disallow: /\n";
        }
    }
}
