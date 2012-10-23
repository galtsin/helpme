<?php

class Manager_PossibilityController extends App_Zend_Controller_Action
{
    /**
     * Получить список менеджеров, ролями которых текущий Менеджер может управлять
     */
    public function managersAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Роль текущей страницы
        $pageRole = 'ADM_COMPANY';
        $userColl = new HM_Model_Account_User_Collection();

        if($userColl->load($account['user']) instanceof HM_Model_Account_User) {
            // Определить роли текущего Менеджера, где он является Администратором Компании или выше
            // и получить список компаний, в которых он может назначать права другим Менеджерам
            $allowedCompanies = array();
            foreach($userColl->load($account['user'])->getRoles() as $userRoleIdentifier => $companies) {
                if($access->getAcl()->inheritsRole($userRoleIdentifier, 'ADM_COMPANY') || $userRoleIdentifier === 'ADM_COMPANY') {
                    $allowedCompanies = array_merge($allowedCompanies, $companies);
                }
            }
            if(count($allowedCompanies) > 0) {
                // Получаем список Менеджеров, ролями которых текущий Менеджер может управлять
                $allowedManagers = array();
                $possibilityColl = new HM_Model_Account_Access_Possibility_Collection();
                foreach(array_unique($allowedCompanies) as $company) {
                    $possibilityColl->addEqualFilter('company', $company);
                }
                $possibilityColl->getCollection();
                foreach($possibilityColl->getObjectsIterator() as $possibility) {
                    $allowedManagers[] = $possibility->getData('user');
                }
                $this->view->assign('managers', array_unique($allowedManagers));
                $this->view->assign('companies', array_unique($allowedCompanies));
            }
        }
    }

    /**
     * Получить панель управления отдельным Менеджером
     */
    public function getManagerBoardAction()
    {

    }

    /**
     * Получить те роли Менеджера, от имени которых он может назначать права другим Менеджерам
     */
    public function getManagerAllowedRoles(HM_Model_Account_User $user, App_Core_Model_Data_Store $role)
    {

    }

    public function getManagerAction()
    {
        $request = $this->getRequest();
        $access = HM_Model_Account_Access::getInstance();

        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);

        $manager = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($request->getParam('manager'));

        if($manager instanceof HM_Model_Account_User) {
            // Получить список компаний от имени которых пользователь может назначать права другим
            $companiesAllowed = array();
            foreach($user->getRoles() as $roleIdentifier => $companies) {
                if($access->getAcl()->inheritsRole($roleIdentifier, 'ADM_COMPANY') || $roleIdentifier === 'ADM_COMPANY') {
                    $companiesAllowed = array_merge($companies);
                }
            }
            $data = array();
            $data['manager'] = $manager->getData('id');
            foreach($manager->getRoles() as $roleIdentifier => $companies) {
                foreach($companies as $company) {
                    if(in_array($company, $companiesAllowed)){
                        $data['roles'][$roleIdentifier][] = $company;
                    }
                }
            }
            $this->view->assign('data', $data);
        }
    }

    /**
     * Добавление нового менеджера
     */
    public function addManagerAction()
    {
        $request = $this->getRequest();
        $access = HM_Model_Account_Access::getInstance();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);

        if($user instanceof HM_Model_Account_User) {
            $companiesAllowed = array();
            foreach($user->getRoles() as $roleIdentifier => $companies) {
                if($access->getAcl()->inheritsRole($roleIdentifier, 'ADM_COMPANY') || $roleIdentifier === 'ADM_COMPANY') {
                    $companiesAllowed = array_merge($companies);
                }
            }

            if($request->isPost()) {
                $userColl = new HM_Model_Account_User_Collection();
                $userColl->addEqualFilter('email', $request->getPost('account'))
                    ->getCollection();
                if(count($userColl->getObjectsIterator()) > 0) {
                    $user = current($userColl->getObjectsIterator());
                    if($user instanceof HM_Model_Account_User && in_array($request->getPost('company'), $companiesAllowed)) {
                        $possibility = new HM_Model_Account_Access_Possibility();
                        $possibility->getData()
                            ->set('user', $user->getData('id'))
                            ->set('company', $request->getPost('company'))
                            ->set('role', $access->getRole('USER')->getId());
                        $this->setAjaxStatus('ok');
                        if($possibility->save()) {
                            $this->setAjaxResult($possibility->getData('id'));
                        }
                    }
                }


            } else {
                $this->view->assign('companies', $companiesAllowed);
            }

        }


    }

    public function addManagerRole(){}
    public function removeManagerRole(){}

    public function editPossibilityObjectsAction()
    {

    }

    public function getManagerAllowedRolesAction()
    {
        $companyColl = new HM_Model_Billing_Company_Collection();
        $allowedRoles = $this->getManagerAllowedRoles2();
        foreach($allowedRoles as $companies){
            foreach($companies as $company) {
                $companyColl->load($company);
            }
        }
    }

    /**
     * Получить доступные менеджеру роли
     */
    public function getManagerAllowedRoles2()
    {
        $access = HM_Model_Account_Access::getInstance();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);

        $allowedRoles = array();
        $index = array();
        foreach($user->getRoles() as $roleIdentifier => $companies) {
            foreach($access->getRoles() as $role){
                if($access->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier === $role->get('code')) {
/*                    if(!array_key_exists($role->get('id'), $allowedRoles)) {
                        $allowedRoles[$role->get('id')] = $companies;
                    }*/
                    if(!in_array($role->get('id'), $index)) {
                        $allowedRoles[] = array(
                            'roleIdentifier'    => $role->get('id'),
                            'companies'         => $companies
                        );
                        $index[] = $role->get('id');
                    }
                }
            }
        }
        return $allowedRoles;
    }
}