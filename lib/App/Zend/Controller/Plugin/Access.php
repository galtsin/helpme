<?php
/**
 *
 */
/**
 * Created by JetBrains PhpStorm.
 * User: GaltsinAK
 * Date: 16.03.12 8:41
 */
class App_Zend_Controller_Plugin_Access extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        // Физическая переадресация на другой URL
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');

        // Проверка прав доступа к данной странице
        $currentUrl = $this->getRequest()->getModuleName() . '/' .
            $this->getRequest()->getControllerName() . '/' .
            $this->getRequest()->getActionName();

        if(false == HM_Model_Account_Auth::getInstance()->isAuth() && $currentUrl !== 'account/access/login') {
            $redirector->gotoUrl('account/access/login');
        }
    }
}