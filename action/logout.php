<?php

if (!defined('FLUIDFRAME')) {
	exit(1);
}

/**
 * Logout action class.
 */
class LogoutAction extends Action {

    function prepare($args) {
        parent::prepare($args);
        return true;
    }

    /**
     * Class handler.
     *
     * @param array $args array of arguments
     *
     * @return nothing
     */
    function handle($args) {
        parent::handle($args);
        if (!common_logged_in()) {
            $this->clientError(_('Not logged in.'));
        } else {
            
            $this->logout();
            
            common_redirect(common_get_route('index'), 303);
        }
    }

    function logout() {
        common_set_user(null);
        common_real_login(false); // not logged in
        common_forgetme(); // don't log back in!
    }

}
