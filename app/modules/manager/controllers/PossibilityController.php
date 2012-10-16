<?php

class Manager_PossibilityController extends App_Zend_Controller_Action
{
    public function managersAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Определить роли, пользователя, где он является Администратором Компании
        $pageRole = $access->getRole('ADM_COMPANY');
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);

        $roles = $user->getRoles();
        $companies = $roles['ADM_COMPANY'];

        $possibility = new HM_Model_Account_Access_Possibility_Collection();
        foreach($companies  as $company) {
            $possibility->addEqualFilter('company', $company);
        }

        $managers = $index = array();
        foreach($possibility->getCollection()->getDataIterator() as $data) {
            // группировать по пользователю
            if(!in_array($data->get('user'), $index)) {
                $index[] = $data->get('user');
                $managers[$data->get('user')] = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
                    ->restore($data->get('user'))->getData();
            }
        }
        $data = array(
            'managers' => $managers
        );
        $this->view->assign('data', $data);

        $po = new HM_Model_Account_Access_Possibility();
        $po->getData()
            ->set('user', 35)
            ->set('role', 1)
            ->set('company', 12);
        //Zend_Debug::dump($po->save());
        //Zend_Debug::dump($po->getData());
    }

    public function getManagerAction()
    {

    }
}