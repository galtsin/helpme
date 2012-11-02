<?php

class Manager_TarifficationController extends App_Zend_Controller_Action
{
    /**
     * Загрузить список доступных ЛК с привязкой по компаниям
     * HTML Context
     */
    public function indexAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Узнать по какой роли стоит осуществлять поиск
        // Мы знаем URL-адрес
        $pageRole = $access->getRole('ADM_TARIFF'); // TODO: Как то нужно узнавать!
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user']);

        $accessColl = new HM_Model_Account_Access_Collection();
        $accessColl->setType('LINE')
            ->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory'));

        $data = array();

        foreach($user->getRoles() as $roleIdentifier => $companies) {
            if($access->getAcl()->inheritsRole($roleIdentifier, $pageRole->get('code')) || $roleIdentifier === $pageRole->get('code')) {
                foreach($companies as $company){
                    $accessColl->resetFilters();
                    $accessColl->setAccessFilter($user, $pageRole, $company)->getCollection();
                    $data[] = array('company' => $company, 'lines' => $accessColl->getIdsIterator());
                }
            }

        }
        $this->view->assign('data', $data);
    }

    /**
     * Список тарифов на ЛК
     */
    public function _linesAction()
    {
        $data = array();

        // line => tariffs
        $line = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory')
            ->restore(11);
        if($line instanceof HM_Model_Counseling_Structure_Line) {
            $tariffColl = new HM_Model_Billing_Tariff_Collection();
            $tariffColl->addEqualFilter('line', $line->getData('id'));
            $data[] = array('line' => $line->getData(), 'tariffs' => $tariffColl->getCollection()->getDataIterator());
        }
        $this->view->assign('data', $data);
    }

    public function linesAction()
    {
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $pageRole = 'ADM_LINE';
        $admin = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);

        $accessColl = new HM_Model_Account_Access_Collection();
        $tariffColl = new HM_Model_Billing_Tariff_Collection();
        $accessColl->setType('LINE')
            ->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory'))
            ->setRestrictionByInheritanceFromRole($pageRole);

        $accessColl->addEqualFilter('possibility', $admin->getPossibilities())
            ->getCollection();

        $lines = array();
        foreach($accessColl->getObjectsIterator() as $line) {
            if(!array_key_exists($line->getData('id'), $lines)) {
                $lines[$line->getData('id')] = array(
                    'line'      => $line,
                    'tariffs'   => array()
                );
            }
            $tariffColl->resetFilters();
            $tariffColl->clear();
            $lines[$line->getData('id')]['tariffs'] = $tariffColl->addEqualFilter('line', $line->getData('id'))->getCollection()->getObjectsIterator();
        }
        $this->view->assign('chainLineWithTariffs', $lines);
    }

    /**
     * TODO: Пример для подражаения последний
     * Добавить тариф на ЛК
     */
    public function addTariffAction()
    {
        $request = $this->getRequest();
        $line = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory')
            ->restore($request->getParam('line'));
        if($line instanceof HM_Model_Counseling_Structure_Line) {
            if($request->isPost()){
                if(array_key_exists('tariff', $request->getPost())) {
                    // Предпроверка данных
                    $validate = new App_Zend_Controller_Action_Helper_Validate('tariff');
                    $filterInput = new Zend_Filter_Input($validate->getFilters(), $validate->getValidators());
                    $filterInput->setData($request->getPost('tariff'));

                    if($filterInput->isValid()){
                        $tariff = new HM_Model_Billing_Tariff();
                        $tariff->getData()
                            ->set('line', $line->getData('id'))
                            ->set('name', $filterInput->getEscaped('name'));
                        if($tariff->save()) {
                            $this->setAjaxResult($tariff->getData('id'));
                        }
                    } else {
                        $this->addAjaxError($filterInput->getMessages(), 'tariff');
                    }
                }
            } else {
                // Какие то параметры для вывода в шаблоне
                $this->view->line = $line->getData();
            }
        }
    }

    /**
     * Отредактировать тариф
     */
    public function editTariffInfoAction()
    {
        $request = $this->getRequest();
        $tariff = App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Tariff_Factory')
            ->restore($request->getParam('tariff'));
        if($tariff instanceof HM_Model_Billing_Tariff){
            if($request->isPost()){
                if(array_key_exists('tariff', $request->getPost())) {
                    // Предпроверка данных
                    $validate = new App_Zend_Controller_Action_Helper_Validate('tariff');
                    $filterInput = new Zend_Filter_Input($validate->getFilters(), $validate->getValidators());
                    $filterInput->setDefaultEscapeFilter(new Zend_Filter_StringTrim());
                    $filterInput->setData($request->getPost('tariff'));

                    if($filterInput->isValid()){
                        // Сохранить результаты
                        foreach($filterInput->getEscaped() as $key => $value) {
                            if($key != 'id'){
                                $tariff->getData()->set($key, $value);
                            }
                        }
                        if($tariff->save()) {
                            $this->setAjaxResult($tariff->getData('id'));
                        }
                    } else {
                        $this->addAjaxError($filterInput->getMessages(), 'tariff');
                    }
                }
            } else{
                // Форма на редактирование
                $this->view->assign('data', $tariff);
            }
        }
    }

    /**
     * Удалить тариф
     */
    public function removeTariffAction()
    {
        $request = $this->getRequest();
        $tariff = App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Tariff_Factory')
            ->restore($request->getParam('tariff'));
        if($tariff instanceof HM_Model_Billing_Tariff){
            if($request->isPost()){
                if(false == $tariff->getData('used')) {
                    if($tariff->remove()) {
                        $this->setAjaxResult($request->getParam('tariff'));
                    } else {
                        // TODO: Выдать сообщение об ошибки
                    }
                }
            }
        }
    }
}