<?php

class Default_ErrorController extends App_Zend_Controller_Action
{

    public function init()
    {
        // Поддерживается только один контекст JSON
        $this->getHelper('AjaxContext')
            ->addActionContext($this->getRequest()->getActionName(), array('json', 'html'))
            ->setAutoDisableLayout(true)
            ->setSuffix('html', null, true) // Отключить суффикс вида
            ->initContext();

        if(null === $this->getHelper('AjaxContext')->getCurrentContext()){
            $this->_helper->layout->setLayout('empty');
        }
    }

    public function errorAction()
    {
        // Если получаем AJAX-запрос, то отключаем Layout и выводим то что есть

        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->_helper->viewRenderer('404');
                $this->view->message = 'Resource not found';
                //$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
                break;
            case App_Zend_Controller_Plugin_Access::EXCEPTION_ACCESS_DENIED:
                $this->getResponse()->setHttpResponseCode(403);
                $this->_helper->viewRenderer('403');
                $this->view->message = 'Forbidden';
                $this->_helper->getHelper('AjaxContext')->initContext('json');
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }

        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->crit($this->view->message, $errors->exception);
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request   = $errors->request;
    }

    // TODO: Подключить логгирование Zend_Log
    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

