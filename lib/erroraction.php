<?php
if (!defined('FLUIDFRAME')) {
    exit(1);
}


class ErrorAction extends Action {
    
    var $http_codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
    );
    
    
    var $code;
    var $message;
    
    
    function prepare($params) {
        
        return true;
    }
    
    function setError($code) {
        $this->code = $code;
    }
    function setMessage($message) {
        $this->message = $message;
    }
    function setErrorMessage($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
    function handle() {
        global $isApi;
        if($isApi){
            common_debug("API");
        }else{
            common_debug("NO API");
        }
        if(!isset($this->http_codes[$this->code])) {
            $this->code = 404;
        }
        
        $status_string = $this->code . ' ' . $this->http_codes[$this->code];
    
        header('HTTP/1.1 ' . $status_string, true, $this->code);
        
        $ret = array(
                'code'=>$this->code,
                'status'=>$this->http_codes[$this->code],
                'title'=>$status_string,
                'message'=>$this->message
        );
        if($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($ret);
        } else {
            $this->render('error',$ret);
        }
        exit();
    }

}
