<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class AdminroleaddAction extends AuthAction {

    var $obj,
        $inputError,
        $field;

    function prepare($args) {
        parent::prepare($args);
        $this->field['name'] = $this->trimmed('name');
        $this->field['description'] = $this->trimmed('description');
        $this->field['status'] = (int) $this->trimmed('status');

        return true;
    }

    function handle() {
        parent::handle();
        $jsfile = 'javascripts/adminrole.js';
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
                                if(($rule == 'required')&&($extra)){
                                    $validationRules[$fieldName][$rule] =
                                        $this->field[ $fieldName ];
                                }
                            }
                        }
                    }
                }
            }
            $this->inputError = Role::validateData($validationRules);

            $this->obj = new Role();
            foreach ($this->field as $fieldName=>$value){
                $this->obj->$fieldName = $value;
            }
            if(!empty($this->inputError)){
                $this->renderOptions['inputError'] = $this->inputError;
                $this->renderOptions['role_name'] = $this->obj->name;
                $this->renderOptions['role_description'] = $this->obj->description;
                $this->renderOptions['role_status'] = $this->obj->status;

                $this->render ( 'adminroleform', $this->renderOptions );
            }else{
                $this->obj->created = $this->obj->modified = common_sql_now();
                if($this->obj->insert()){
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
