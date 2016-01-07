<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class LoginAction extends Sbadmin2Action {
    
    var $loginParams = array();
    
    function prepare($args) {
        parent::prepare($args);
        return true;
    }
    function handle() {
        parent::handle();
        $this->loginParams['loginform_url']=common_get_route('login',array('lang'=>$this->lang));
        if($this->isPost()) {
            $this->checkLogin();
        } else {
            $this->showForm();

        }
        
    }
    
    function showForm($message = null) {
        if(!empty($message)) {
            $this->loginParams['loginform_message']=$message;
        }
        $this->render('login', $this->loginParams);
    }
    
    function checkLogin($user_id = null, $token = null) {
        // XXX: login throttle
        
        // CSRF protection - token set in NoticeForm
//         $token = $this->trimmed ( 'token' );
//         if (! $token || $token != common_session_token ()) {
//             $st = common_session_token ();
//             if (empty ( $token )) {
//                 common_log ( LOG_WARNING, 'No token provided by client.' );
//             } else if (empty ( $st )) {
//                 common_log ( LOG_WARNING, 'No session token stored.' );
//             } else {
//                 common_log ( LOG_WARNING, 'Token = ' . $token . ' and session token = ' . $st );
//             }
            
//             throw new FluidframeException('There was a problem with your session token. ' . 'Try again, please.' );
            
//         }
        
        common_debug('checkingLogin');
        $email = $this->trimmed ( 'email' );
        $password = $this->arg ( 'password' );
        
        $user = common_check_user ( $email, $password );
        
        if (! $user) {
            common_debug('Incorrect username or password.');
            $this->showForm ( 'Incorrect username or password.' );
            return;
        }
        
        // success!
        if (! common_set_user ( $user )) {
            throw new FluidframeException(  'Error setting user. You are probably not authorized.' );
            
        }
        
        common_real_login ( true );
        
        if ($this->boolean ( 'rememberme' )) {
            common_rememberme ( $user );
        }
        
        $url = common_get_returnto ();
        
        if ($url) {
            // We don't have to return to it again
            common_set_returnto ( null );
            
        } else {
            $url = common_get_route('adminhome');
        }
        
        common_redirect ( $url, 303 );
    }
}
