<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdminroleeditAction extends AuthAction {

    var $obj,
        $inputError,
        $field;

    function prepare($args) {
        parent::prepare($args);
        $this->field['id'] = (int) $this->trimmed('id');
        $this->field['name'] = $this->trimmed('name');
        $this->field['description'] = $this->trimmed('description');
        $this->field['status'] = (int) $this->trimmed('status');

        $this->obj = Role::staticGet ( 'id', $this->field['id'] );
        if (! $this->obj) {
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
        $jsfile = 'javascripts/adminrole.js';
        if(file_exists($jsfile)){
            $this->renderOptions['jsfile']='/'.$jsfile;
        }
        if ($this->isPost ()) {
            // ho ricevuto i dati per l'aggiornamento/cancellazione
            if($this->trimmed( 'remove' ) == 'on'){
                $this->obj->delete();
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
                            if(($rule == 'required')&&($extra)){
                                $validationRules[$fieldName][$rule] =
                                    $this->field[ $fieldName ];
                            }
                        }
                    }
                }
            }
            $this->inputError = Role::validateData($validationRules);

            foreach ($this->field as $fieldName=>$value){
                $this->obj->$fieldName = $value;
            }
            if(!empty($this->inputError)){
                $this->renderOptions['inputError'] = $this->inputError;
                $this->renderOptions['role_id'] = $this->obj->id;
                $this->renderOptions['role_name'] = $this->obj->name;
                $this->renderOptions['role_description'] = $this->obj->description;
                $this->renderOptions['role_status'] = $this->obj->status;

                $this->render ( 'adminroleform', $this->renderOptions );
            }else{
                $this->obj->modified = common_sql_now();
                if($this->obj->update()){
                    common_redirect( common_get_route('admintablelist', array(
                        'model' => 'role'
                    )));
                }
            }
        } else {
            // Preparo i campi da visualizzare
                $this->renderOptions['role_id'] = $this->obj->id;
                $this->renderOptions['role_name'] = $this->obj->name;
                $this->renderOptions['role_description'] = $this->obj->description;
                $this->renderOptions['role_status'] = $this->obj->status;

            $this->render ( 'adminroleform', $this->renderOptions );
        }
    }
}
