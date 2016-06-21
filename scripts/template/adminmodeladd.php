<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class Admin%model%addAction extends AuthAction {

    var $obj,
        $inputError,
        $field;

    function prepare($args) {
        parent::prepare($args);
        $this->renderOptions['form_action']=common_get_route('admin%model%add');
/* PREPAREFIELDS */
        return true;
    }

    function handle() {
        parent::handle();
        $jsfile = 'js/admin%model%.js';
        if(file_exists($jsfile)){
            $this->renderOptions['jsfile']='/'.$jsfile;
        }
        if ($this->isPost ()) {
            // devo validare i dati per l'inserimento
            $validationRules = array();
            foreach( %MODEL%::getAdminTableStruct() as $fieldName=>$keys){
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
            $this->inputError = %MODEL%::validateData($validationRules);

            $this->obj = new %MODEL%();
            foreach ($this->field as $fieldName=>$value){
                $this->obj->$fieldName = $value;
            }
            if(!empty($this->inputError)){
                $this->renderOptions['inputError'] = $this->inputError;
/* ERRORFIELDS */
                $this->render ( 'admin%model%form', $this->renderOptions );
            }else{
                $this->obj->created = $this->obj->modified = common_sql_now();
                if($this->obj->insert()){
                    common_redirect( common_get_route('admintablelist', array(
                        'model' => '%model%'
                    )));
                }else{
                    common_debug("ERRORE IN UPDATE %MODEL%: ". print_r($this->obj,true));
                    throw new ServerException( 'Impossibile aggiornare il %MODEL%.' );
                }
            }
        }
        $this->render ( 'admin%model%form', $this->renderOptions );
    }
}
