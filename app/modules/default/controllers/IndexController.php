<?php

class Default_IndexController extends App_Zend_Controller_Action
{
    public function indexAction()
    {

    }

    public function testAction()
    {
        /*        $acl = new Zend_Acl();
        $acl->addRole('guest');
        $acl->addRole('user', 'guest');
        $acl->addResource('page');
        $acl->allow('guest', 'page', 'read');
        $acl->allow('user', 'page', 'write');
        Zend_Debug::dump($acl->isAllowed('user', 'page', 'write'));

        $validatorChain = new Zend_Validate();
        $validatorChain->addValidator(new Zend_Validate_StringLength(6, 12), true)
            ->addValidator(new Zend_Validate_Alnum(), true);
        Zend_Debug::dump($validatorChain->isValid('Hello4_'));*/


        $validateConfig = Zend_Registry::get('validate');


        /*        Zend_Debug::dump(array_merge(array(Zend_Filter_Input::DEFAULT_VALUE => 'dsf'), array(array('Alnum', array('allowWhiteSpace' => true)))));
                Zend_Debug::dump(array(
                Zend_Filter_Input::DEFAULT_VALUE => 'dsf',
                array('Alnum', array('allowWhiteSpace' => true))
            ));*/


        //Zend_Debug::dump(Zend_Json::encode(array('data' => array('count' => 10, 'items' => $b))));



        // 1. Проверить доступ к функции и получить роли
        // 2. Получить список доступных ресурсов Possibility
        // 3. Проставить режимы записи/чтения объектов

        // Определить какой компании принадлежит ресурс - параметр company_owner
        // user + company_owner + currentFunctionRole

        /*        $line = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory')
                    ->restore($this->getRequest()->getParam('line'));
                if($line instanceof HM_Model_Counseling_Structure_Line) {
                    $accessColl = new HM_Model_Account_Access_Collection();
                    $accessColl->setType('LINE');
                    $accessColl->setAccessFilter($user, $pageRole, $line->getData('company_owner'))->getCollection();
                    $possibility = current($accessColl->getPossibilities());
                    if($possibility->has($line->getData('id'))){
                        echo "Объект доступен пользователю";
                    }
                    // Расставить режимы на чтение/запись
                    $possibility->setPrivileges($line);
                }*/

        //$this->_helper->viewRenderer->setNoRender(true);
        //$this->_helper->layout->disableLayout();

    }

    public function ajaxAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        sleep(1);
        $company = HM_Model_Billing_Company::load(12);
        $this->setAjaxData(array($company->getData()->toArray()));
        //$this->getResponse()->setHttpResponseCode(403);
        //echo Zend_Json::encode(array(1,2));
    }

}

