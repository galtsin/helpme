<?php

class Manager_CounselingStructureController extends App_Zend_Controller_Action
{
    /**
     * Загрузить список доступных ЛК с привязкой по компаниям
     */
    public function indexAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user']);

        // Узнать по какой роли стоит осуществлять поиск
        // Мы знаем URL-адрес

        $access = HM_Model_Account_Access::getInstance();
        $access->getRoles();

        $pageRole = 'ADM_TARIFF'; // TODO: Как то нужно узнавать

        // Доработка
        $userRoles = $user->getRoles();

        $coll = new HM_Model_Account_Access_Collection();
        $coll->setType('LINE')
            ->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory'));
        $data = array();
        foreach($userRoles as $role => $companies) {
            foreach($companies as $company){
                $coll->resetFilters();
                $coll->setAccessFilter($user, $access->getRole($pageRole), $company)->getCollection();
                $data[$company] = array_merge($coll->getIdsIterator());
            }
        }
        Zend_Debug::dump($data);

        Zend_Debug::dump(array_unique(array_merge(array(1,2,3,4), array(3,4,5,6))));
        $this->view->data = $data;
    }

    /**
     * Управление ЛК
     */
    public function lineAction()
    {

    }

    /**
     * Упрвление Тарифами
     */
    public function tariffsAction(){}
}