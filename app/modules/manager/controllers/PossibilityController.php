<?php

class Manager_PossibilityController extends App_Zend_Controller_Action
{
    /**
     * Good
     * Получить список менеджеров, ролями которых текущий Менеджер может управлять
     */
    public function managersAction()
    {
        // TODO: Проверить, есть ли право у пользователя работать с текущей страницей
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        if(App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user']) instanceof HM_Model_Account_User) {
            // Определить роли текущего Менеджера, где он является Администратором Компании или выше
            // и получить список компаний, в которых он может назначать права другим Менеджерам
            $this->view->assign('possibilities', $this->___g());
        }
    }

    /**
     * Good
     * TODO: Готово. На базе данной функции релизовать дальнейшее функционирование
     * Получить список Менеджеров, с которыми можно производить работу (с их ролями и компаниями)
     * Роль текущего Менеджера должна быть выше или равна роли редактируемого Менеджера и находится в одной и той же компании
     * Можно еще сгруппировать
     * Совпадение окмпании - один к одному
     * Совпадение ролей - через наследование
     * @return array
     */
    private function ___g()
    {
        // Получить текущего пользователяа
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Роль текущей страницы
        $pageRole = 'ADM_COMPANY';
        $userColl = new HM_Model_Account_User_Collection();
        $possibilityColl = new HM_Model_Account_Access_Possibility_Collection();

        $allowedManagers = array();

        if($userColl->load($account['user']) instanceof HM_Model_Account_User) {
            // Определить роли текущего Менеджера, где он является Администратором Компании или выше
            // и получить список компаний, в которых он может назначать права другим Менеджерам
            foreach($userColl->load($account['user'])->getRoles() as $userRoleIdentifier => $companies) {
                if($access->getAcl()->inheritsRole($userRoleIdentifier, $pageRole) || $userRoleIdentifier == $pageRole) {
                    foreach($companies as $company) {
                        $possibilityColl->resetFilters()
                            ->addEqualFilter('company', $company)
                            ->getCollection();
                        foreach($possibilityColl->getObjectsIterator() as $possibility) {
                            // Если роль администрирующего Менеджера выше или равна менеджеру, то он имеет право на управление его ролями
                            if($access->getAcl()->inheritsRole($userRoleIdentifier, $access->getRole($possibility->getData('role'))->get('code'))
                                || $userRoleIdentifier == $access->getRole($possibility->getData('role'))->get('code')) {
                                $allowedManagers[] = array(
                                    'user'   => $possibility->getData('user'),
                                    'role'      => $access->getRole($possibility->getData('role'))->get('code'),
                                    'company'   => $company
                                );
                            }
                        }
                    }
                }
            }
        }

        return $allowedManagers;
    }

    /**
     * Good
     * Получить доступные для редактирования текущим Администратором Роли Менеджера с привязкой к компаниям
     * @param HM_Model_Account_User $manager
     * @return array
     */
    private function _intersection(HM_Model_Account_User $manager)
    {
        // Получить текущего пользователяа
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Роль текущей страницы
        $pageRole = 'ADM_COMPANY';
        $userColl = new HM_Model_Account_User_Collection();

        $allowed = array();

        if($userColl->load($account['user']) instanceof HM_Model_Account_User) {
            foreach($userColl->load($account['user'])->getRoles() as $userRoleIdentifier => $userCompanies) {
                foreach($manager->getRoles() as $managerRoleIdentifier => $managerCompanies) {
                    // Роли
                    if( ($access->getAcl()->inheritsRole($userRoleIdentifier, $pageRole) || $userRoleIdentifier == $pageRole)
                        &&
                        ($access->getAcl()->inheritsRole($userRoleIdentifier, $managerRoleIdentifier) || $userRoleIdentifier == $managerRoleIdentifier)) {
                            if(!array_key_exists($managerRoleIdentifier, $allowed)){
                                 $allowed[$managerRoleIdentifier] = array();
                            }
                            foreach($userCompanies as $company) {
                                if(in_array($company, $managerCompanies) && !in_array($company, $allowed[$managerRoleIdentifier])){
                                     $allowed[$managerRoleIdentifier][] = $company;
                                }
                            }
                    }
                }
            }
        }
        return $allowed;
    }


    /**
     * Good
     * Получить панель управления отдельным Менеджером
     */
    public function getManagerBoardAction()
    {
        $request = $this->getRequest();
        $manager = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($request->getQuery('manager'));
        if($manager instanceof HM_Model_Account_User) {
            // Определить роли текущего Менеджера, где он является Администратором Компании или выше
            // и получить список компаний, в которых он может назначать права другим Менеджерам
            $this->view->assign('possibility', array('user' => $manager->getData('id'), 'roles' => $this->_intersection($manager)));

        }
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