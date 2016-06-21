<?php
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class Admin%model%editAction extends AuthAction {

    var $obj,
        $inputError,
        $field;

    function prepare($args) {
        parent::prepare($args);
/* PREPAREFIELDS */
        $this->renderOptions['form_action']=common_get_route('admin%model%edit', array('id' => $this->field['id'] ));
        $this->obj = %MODEL%::staticGet ( 'id', $this->field['id'] );
        if (! $this->obj) {
            $error = new ErrorAction ( $_lang );
            $error->setErrorMessage ( 404, _i18n('ADMIN', 'unknown%model%',
                '%SINGLE% inesistente') );
            $error->handle ();
            return false;
        }
        return true;
    }

    function handle() {
        parent::handle();
        $jsfile = 'js/admin%model%.js';
        if(file_exists($jsfile)){
            $this->renderOptions['jsfile']='/'.$jsfile;
        }
        if ($this->isPost ()) {
            $this->handlePost();
        } else {
            $this->showForm();
        }
    }

    function handlePost(){
        // ho ricevuto i dati per l'aggiornamento/cancellazione
        if($this->trimmed( 'remove' ) == 'on'){
            $this->obj->delete();
            common_redirect( common_get_route('admintablelist', array(
                'model' => '%model%'
            )));
        }
        // se non si tratta di una cancellazione allora devo validare
        $validationRules = array();
        foreach( %MODEL%::getAdminTableStruct() as $fieldName=>$keys){
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
        $this->inputError = %MODEL%::validateData($validationRules);

        foreach ($this->field as $fieldName=>$value){
            $this->obj->$fieldName = $value;
        }
        if(!empty($this->inputError)){
            $this->renderOptions['inputError'] = $this->inputError;
            $this->showForm();
        }else{
            $this->obj->modified = common_sql_now();
            if($this->obj->update()){
                common_redirect( common_get_route('admintablelist', array(
                    'model' => '%model%'
                )));
            }else{
                common_debug("ERRORE IN UPDATE %MODEL%: ". print_r($this->obj,true));
                throw new ServerException( 'Impossibile aggiornare il %MODEL%.' );
            }
        }
    }

    function showForm(){
        // Preparo i campi da visualizzare
/* RENDERFIELDS */
        $this->render ( 'admin%model%form', $this->renderOptions );
    }
}
