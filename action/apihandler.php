<?php
/**
 * Fluidframe - Fluidware Web Framework
 * Copyright (C) 2015-2016, Fluidware
 *
 * @author: Michele Azzolari michele@fluidware.it
 *
 */
if (! defined ( 'FLUIDFRAME' )) {
    exit ( 1 );
}
class ApihandlerAction extends Action {
    var $version;
    var $apiAction;
    function prepare($args) {
        
        Fluidframe::$isApi = true;
        parent::prepare ( $args );
        
        $this->version = $this->arg ( 'version' );
        $this->apiAction = $this->arg ( 'apiaction' );
        
        // common_debug('#'.$action.'#'.$version.'#'.$apiAction.'#'.$apiClass );
        
        $apiClass = $this->getApiFile ();
        
        $apiAccessControl = common_config ( 'api', 'access-control' );
        if ($apiAccessControl) {
            foreach ( $apiAccessControl as $key => $val ) {
                header ( $key . ': ' . $val );
            }
        }
        
        if (@file_exists ( $apiClass )) {
            include_once ($apiClass);
            
            $action_class = ucfirst ( $this->apiAction ) . 'ApiAction';
            
            if (! class_exists ( $action_class )) {
                $this->clientError ( _ ( 'Unknown action ' . $action_class ), 404 );
            } else {
                
                /**
                 *
                 * @var ApiAction $action_obj
                 */
                $action_obj = new $action_class ();
                
                header ( 'Content-Type: ' . $action_obj->contentType () );
                
                try {
                    if ($action_obj->prepare ( $args )) {
                        switch ($this->getMethod ()) {
                            case 'POST' :
                                $action_obj->post ();
                                break;
                            
                            case 'HEAD' :
                                $action_obj->head ();
                                break;
                            case 'PUT' :
                                $action_obj->put ();
                                break;
                            
                            case 'GET' :
                                $action_obj->get ();
                                break;
                                
                            case 'OPTIONS':
                                $action_obj->options ();
                                break;
                                
                            case 'DELETE':
                                $action_obj->delete();
                                break;
                                
                            default:
                                throw new HttpFluidException(array(501,'Not implemented'));
                        }
                    }
                } catch ( HttpFluidException $hfc ) {
                    $error = new ErrorAction ( $this->lang );
                    $error->setErrorMessage ( $hfc->getStatusCode (), $hfc->getBody () );
                    $error->handle ();
                } catch ( ClientException $cex ) {
                    
                    $error = new ErrorAction ( $this->lang );
                    $error->setErrorMessage ( $cex->getCode (), $cex->getMessage () );
                    $error->handle ();
                } catch ( ServerException $sex ) {
                    
                    $error = new ErrorAction ( $this->lang );
                    $error->setErrorMessage ( $sex->getCode (), $sex->getMessage () );
                    $error->handle ();
                } catch ( Exception $ex ) {
                    $error = new ErrorAction ( $this->lang );
                    $error->setErrorMessage ( 500, $ex->getMessage () );
                    $error->handle ();
                }
            }
        } else {
            $error = new ErrorAction ( $this->lang );
            $error->setErrorMessage ( 404, 'Unkown api action (' . $this->apiAction . ')' );
            $error->handle ();
        }
        
        return false;
    }
    protected function getApiFile() {
        return INSTALLDIR . '/api/' . $this->version . '/' . strtolower ( $this->apiAction ) . '.php';
    }
    function post() {
        return array (
                405,
                'Method Not Allowed' 
        );
    }
}
