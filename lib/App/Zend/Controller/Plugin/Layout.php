<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Плагин для подключения персональных шаблонов (layer) для модулей
 */
class App_Zend_Controller_Plugin_Layout extends Zend_Controller_Plugin_Abstract
{
    // TODO: http://stackoverflow.com/questions/3880294/module-specific-controller-plugins-in-zend-framework
    public function preDispatch()
    {
        if(file_exists(APPLICATION_PATH . '/layouts/core/' . $this->getRequest()->getModuleName() . '.phtml')) {
            Zend_Layout::getMvcInstance()->setLayout($this->getRequest()->getModuleName());
        }
    }
}
