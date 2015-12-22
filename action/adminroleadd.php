<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdminroleaddAction extends AuthAction {

    var $role,
        $inputError,
        $field;

    function prepare($args) {
        parent::prepare($args);
        $this->field['name'] = $this->trimmed('name');
        $this->field['description'] = $this->trimmed('description');
        $this->field['status'] = ($this->trimmed('status', 0) == '1') ? 1 : 0;
        // common_debug("Fields: ".print_r($this->field,true));
        return true;
    }

    function handle() {
        parent::handle();
        $jsfile = 'js/adminrole.js';
        if(file_exists($jsfile)){
            $this->renderOptions['jsfile']='/'.$jsfile;
        }
        if ($this->isPost ()) {
            // devo validare i dati per l'inserimento
            foreach( Role::getAdminTableStruct() as $fieldName=>$keys){
                if($fieldName != 'id'){
                    foreach( $keys as $key=>$rules){
                        if($key == "rules"){
                            foreach( $rules as $rule=>$extra ){
                                // gestione campi richiesti
                                if($rule == 'required'){
                                    $validationRules[$fieldName][$rule] =
                                        $this->field[ $fieldName ];
                                }
                                // common_debug("validationRule: ".print_r($validationRules[$fieldName],true));
                            }
                        }
                    }
                }
            }
            $this->inputError = Role::validateData($validationRules);
            // common_debug("inputError: ".print_r($this->inputError,true));

            $this->role = new Role();
            foreach ($this->field as $fieldName=>$value){
                $this->role->$fieldName = $value;
            }
            if(!empty($this->inputError)){
                $this->renderOptions['inputError'] = $this->inputError;
                $this->renderOptions['role_name']= $this->role->name;
                $this->renderOptions['role_description']= $this->role->description;
                $this->renderOptions['role_status']= $this->role->status;
                // common_debug("renderOptions: ".print_r($this->renderOptions,true));
                $this->render ( 'adminroleform', $this->renderOptions );
            }else{
                $this->role->created = $this->role->modified = common_sql_now();
                if($this->role->insert()){
                    common_redirect( common_get_route('admintablelist', array(
                        'model' => 'role'
                    )));
                }else{
                    common_debug("Qualcosa è andato storto");
                }
            }
        }
        $this->render ( 'adminroleform', $this->renderOptions );
    }
}
