<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}

class TestAction extends Action {

    var $code;
    
	function prepare($args) {
	    parent::prepare($args);
	    $this->code = $this->arg('code');
	    if(empty($this->code)) {
	        throw new ClientException('Missing &lt;code&gt; params',400);
	    }
		return true;
	}

	function handle() {
	    
		$this->render('test',array(
			'code'=>$this->code
		));
	}
	
	function title() {
	    return 'TEST PAGE';
	}
	
}
