<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}

use League\HTMLToMarkdown\HtmlConverter;

class AdminconversionapiAction extends AuthAction {

    var $conversion,
        $in;
    function prepare($args) {
        global $isApi;
        $isApi = true;
        parent::prepare($args);
        $this->conversion = $args['conversion'];
        $this->in = $args['in'];
        // common_debug("args: ".print_r($args,true));
        return true;
    }

    function handle() {
        parent::handle();
        header('Content-Type: application/json; charset=utf-8');
        $result = $this->API();
        if(!empty($result)){
            echo json_encode($result);
        }else{
            $this->handle404();
        }
    }

    function handle404(){
        header ( "HTTP/1.0 404 Not Found" );
        echo json_encode(array(
            'status'=>404,
            'error'=>"Conversion not found"
        ));
    }

    function API(){
        $out="";

        switch($this->conversion){
            case 'testo2html': 
                                $out = nl2br($this->in);
                                break;
            case 'testo2markdown': 
                                $out = $this->in;
                                break;
            case 'html2testo': 
                                $out = strip_tags($this->in);
                                break;
            case 'html2markdown': 
                                $converter = new HtmlConverter();
                                $out = $converter->convert($this->in);
                                break;
            case 'markdown2testo': 
                                $out = strip_tags(Parsedown::instance()->text($this->in));
                                break;
            case 'markdown2html': 
                                $out = Parsedown::instance()->text($this->in);
                                break;
        }
        common_debug("Out: ".$out);

        return array(
            'status'=>200,
            'out'=>$out
        );
    }

}
