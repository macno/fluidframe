<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdminroleeditAction extends AuthAction {

    var $role,
        $inputError;

    function prepare($args) {
        parent::prepare($args);
        $id = ( int ) $this->trimmed ( 'id' );
        $this->role = Role::staticGet ( 'id', $id );
        if (! $this->role) {
            $error = new ErrorAction ( $_lang );
            $error->setErrorMessage ( 404, _i18n('ADMIN', 'unknownrole',
                'Ruolo inesistente') );
            $error->handle ();
            return false;
        }
        return true;
    }

    function handle() {
        parent::handle();
        $jsfile = 'js/adminrole.js';
        if(file_exists($jsfile)){
            $this->renderOptions['jsfile']='/'.$jsfile;
        }
        if ($this->isPost ()) {
            // ho ricevuto i dati per l'aggiornamento/cancellazione
            if($this->trimmed('remove') == 'on'){
                $this->role->delete();
                common_redirect( common_get_route('admintablelist', array(
                    'model' => 'role'
                )));
            }
            // se non si tratta di una cancellazione allora devo validare
            foreach( Role::getAdminTableStruct() as $fieldName=>$keys){
                foreach( $keys as $key=>$rules){
                    if($key == "rules"){
                        foreach( $rules as $rule=>$extra ){
                            // gestione campi richiesti
                            if($rule == 'required'){
                                $validationRules[$fieldName][$rule] =
                                    $this->trimmed($fieldName);
                            }
                            // common_debug("validationRule: ".print_r($validationRules[$fieldName],true));
                        }
                    }
                }
            }
            $this->inputError = Role::validateData($validationRules);

            $this->role->name = $this->trimmed('name');
            $this->role->description = $this->trimmed('description');
            $this->role->status = ($this->trimmed('status') == 'on') ? 1 : 0;
            if(!empty($this->inputError)){
                $this->renderOptions['inputError'] = $this->inputError;
                $this->renderOptions['role_name']= $this->role->name;
                $this->renderOptions['role_description']= $this->role->description;
                $this->renderOptions['role_status']= $this->role->status;
                $this->render ( 'adminroleedit', $this->renderOptions );
            }else{
                $this->role->modified = common_sql_now();
                if($this->role->update()){
                    common_redirect( common_get_route('admintablelist', array(
                        'model' => 'role'
                    )));
                }
            }
        } else {
            // Preparo i campi da visualizzare
            $this->renderOptions['role_id']= $this->role->id;
            $this->renderOptions['role_name']= $this->role->name;
            $this->renderOptions['role_description']= $this->role->description;
            $this->renderOptions['role_status']= $this->role->status;
            $this->render ( 'adminroleedit', $this->renderOptions );
        }
    }
}
