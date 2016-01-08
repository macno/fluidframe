<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdminsavetranslationapiAction extends AuthAction {

    var $lang,
        $context,
        $tkey,
        $in,
        $code,
        $html,
        $out;
    function prepare($args) {
        global $isApi;
        $isApi = true;
        parent::prepare($args);
        $this->lang = $args['lang'];
        $this->context = $args['context'];
        $this->tkey = $args['key'];
        $this->in = $args['in'];
        $this->code = $args['code'];
        $this->out = $args['out'];
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
            'error'=>"Translations not found"
        ));
    }

    function API(){
        $i = file_get_contents(INSTALLDIR.'/i18n/'.$this->lang.'.json');
        $l = json_decode($i,true);
        if($l === null) {
            // echo "There's an error parsing " . $this->lang.".json\n";
            return array(
                'status'=>404,
                'error'=>"Translations " . $this->lang." not loaded"
            );
        }
        switch($this->code){
            case 'testo': $l[$this->context][$this->tkey]['out']=$this->out;
                        break;
            case 'html': $l[$this->context][$this->tkey]['out']=$this->out;
                        break;
            case 'markdown':
                            $l[$this->context][$this->tkey]['out']=Parsedown::instance()->text($this->in);
                            break;
        }
        $l[$this->context][$this->tkey]['in']=$this->in;
        $l[$this->context][$this->tkey]['code']=$this->code;
        $l[$this->context][$this->tkey]['tbt']=false;
        file_put_contents(INSTALLDIR.'/i18n/'.$this->lang.'.json', json_encode($l,JSON_PRETTY_PRINT));
        return array(
            'status'=>200,
            'data'=>array(
                'in'=>$this->in,
                'out'=>$this->out,
                'tbt'=>false
            )
        );
    }

}
