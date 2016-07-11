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
        $jsfile = 'javascripts/admin%model%.js';
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
        $orig = clone($this->obj);
        // ho ricevuto i dati per l'aggiornamento/cancellazione
        if($this->trimmed( 'remove' ) == 'on'){
            $this->obj->delete();
            common_redirect( common_get_route('admintablelist', array(
                'model' => '%model%'
            )));
        }
        // se non si tratta di una cancellazione allora devo validare
        $this->inputError = %MODEL::doValidateData($this->field, $orig);

        foreach ($this->field as $fieldName=>$value){
            $this->obj->$fieldName = $value;
        }
        if(!empty($this->inputError)){
            $this->renderOptions['inputError'] = $this->inputError;
            $this->showForm();
        }else{
            $this->obj->modified = common_sql_now();
            if($this->obj->update($orig) === FALSE){
                common_log_db_error($this->obj, 'UPDATE', __FILE__);
                throw new ServerException( 'Impossibile aggiornare il %MODEL%.' );
            }else{
                common_redirect( common_get_route('admintablelist', array(
                    'model' => '%model%'
                )));
            }
        }
    }

    function showForm(){
        // Preparo i campi da visualizzare
/* RENDERFIELDS */
        $this->render ( 'admin%model%form', $this->renderOptions );
    }
}
