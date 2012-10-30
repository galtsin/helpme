<?php
/**
 *
 */
/**
 * В классе расматриваются 2 вида прав и пользователь.
 * Первый: Администратор. Это тот кто редактирует права других.
 * Все ресурсы подчиненных менеджеров назначаются - наследниками ролей текущего администратора
 * Второй: Менеджер. Тот кому происходит назначение прав
 */
class Manager_PossibilityController extends App_Zend_Controller_Action
{
    /**
     * Good
     * // TODO: Проверить, есть ли право у пользователя работать с текущей страницей
     * Получить список менеджеров, ролями которых текущий Менеджер может управлять
     */
    public function managersAction()
    {
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $userColl = new HM_Model_Account_User_Collection();
        if($userColl->load($account['user']) instanceof HM_Model_Account_User) {
            // Определить роли текущего Менеджера, где он является Администратором Компании или выше
            // и получить список компаний, в которых он может назначать права другим Менеджерам
            $this->view->assign('possibilities', $this->_getManagersPossibility());
            $this->view->assign('rolesWithInheritance', $this->_getUserRolesWithInheritance());
        }
    }

    public function getManagersAction()
    {

    }

    /**
     * Good
     * TODO: Готово. На базе данной функции релизовать дальнейшее функционирование
     * Получить список Менеджеров и их возможности, правами которых можно управлять
     * Совпадение компании - один к одному
     * Совпадение ролей - через наследование
     * Условия: admin(company) = manager(company); admin(role) > manager(role)
     * Определение идет по текущему пользователю сессии
     * @return array HM_Model_Account_Access_Possibility
     */
    private function _getManagersPossibility()
    {
        // Получить текущего пользователяа
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Роль текущей страницы
        $pageRole = 'ADM_COMPANY';
        $userColl = new HM_Model_Account_User_Collection();
        $possibilityColl = new HM_Model_Account_Access_Possibility_Collection();

        $managersPossibility = array();

        if($userColl->load($account['user']) instanceof HM_Model_Account_User) {
            // Определить роли текущего Менеджера, где он является Администратором Компании или выше
            // и получить список компаний, в которых он может назначать права другим Менеджерам
            foreach($userColl->load($account['user'])->getRoles() as $userRoleIdentifier => $companies) {
                if($access->getAcl()->inheritsRole($userRoleIdentifier, $pageRole) || $userRoleIdentifier == $pageRole) {
                    // Получаем полный набор менеджеров, входящих в разрешенные компании
                    foreach($companies as $company) {
                        $possibilityColl->addEqualFilter('company', $company);
                    }
                    foreach($possibilityColl->getCollection()->getObjectsIterator() as $possibility) {
                        // Если роль администрирующего Менеджера выше или равна менеджеру, то он имеет право на управление его ролями
                        if($access->getAcl()->inheritsRole($userRoleIdentifier, $access->getRole($possibility->getData('role'))->get('code'))
                            || $userRoleIdentifier == $access->getRole($possibility->getData('role'))->get('code')) {
                            $managersPossibility[] = $possibility;
                        }
                    }
                }
            }
        }

        return $managersPossibility;
    }

    /**
     * Получить возможности по текущему менеджеру с привязкой к Администратору
     * Определение идет по текущему пользователю сессии
     * Условия: admin(company) = manager(company); admin(role) > manager(role)
     * @param HM_Model_Account_User $manager
     * @return array
     */
    private function _getManagerPossibility(HM_Model_Account_User $manager)
    {
        // Получить текущего пользователяа
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Роль текущей страницы
        $pageRole = 'ADM_COMPANY';
        $userColl = new HM_Model_Account_User_Collection();

        $managerPossibility = array();

        if($userColl->load($account['user']) instanceof HM_Model_Account_User) {
            foreach($userColl->load($account['user'])->getRoles() as $userRoleIdentifier => $userCompanies) {
                if($access->getAcl()->inheritsRole($userRoleIdentifier, $pageRole) || $userRoleIdentifier == $pageRole) {
                    foreach($manager->getPossibilityCollection()->getObjectsIterator() as $possibility) {
                        if($access->getAcl()->inheritsRole($userRoleIdentifier, $possibility->getData()->getRole()->get('code')) || $userRoleIdentifier == $possibility->getData()->getRole()->get('code')) {
                            if(in_array($possibility->getData('company'), $userCompanies)) {
                                $managerPossibility[] = $possibility;
                            }
                        }
                    }
                }
            }
        }

        return $managerPossibility;
    }


    /**
     * Получить список ролей пользователя с построением карты наследования
     * @return array
     */
    private function _getUserRolesWithInheritance()
    {
        $access = HM_Model_Account_Access::getInstance();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $userColl = new HM_Model_Account_User_Collection();

        // Минимальная Роль прохождения = Роли страницы
        $pageRole = 'ADM_COMPANY';
        $rolesWithInheritance = array();

        foreach($userColl->load($account['user'])->getRoles() as $roleIdentifier => $companies) {
            if($access->getAcl()->inheritsRole($roleIdentifier, $access->getRole($pageRole)->get('code')) || $roleIdentifier == $access->getRole($pageRole)->get('code')) {
                sort($companies);
                foreach($access->getRoles() as $role){
                    if($access->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier == $role->get('code')) {
                        if(!array_key_exists($role->get('code'), $rolesWithInheritance)) {
                            $rolesWithInheritance[$role->get('code')] = $companies;
                        } else {
                            foreach($companies as $company) {
                                if(!in_array($company, $rolesWithInheritance[$role->get('code')])){
                                    $rolesWithInheritance[$role->get('code')][] = $company;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $rolesWithInheritance;
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
            $this->view->assign('possibilities', $this->_getManagerPossibility($manager));
            $this->view->assign('manager', $manager);
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
     * 1. Получить русурсы принадлежащие компании
     * 2. Получить ресурсы принадлежащие текущему Менеджеру
     * 3. Получить пересечение ресурсов. Это пересечение - доступные текущему Менеджеру ресурсы
     * 4. Определить ресурсы уже отмеченные для редактируемого менеджера
     * В функции используется укороченный вариант (2 и 4 пункты)
     */
    public function editPossibilityObjectsAction()
    {
        $request = $this->getRequest();
        $access = HM_Model_Account_Access::getInstance();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();

        // Роль текущей страницы
        $pageRole = 'ADM_COMPANY';

        $userColl = new HM_Model_Account_User_Collection();
        $possibilityColl = new HM_Model_Account_Access_Possibility_Collection();

        switch($access->getRole($possibilityColl->load($request->getParam('possibility'))->getData('role'))->get('code')) {
            case 'ADM_LINE':
                $objectType = 'LINE';
                break;
            case 'ADM_TARIFF':
                $objectType = 'TARIFF';
                break;
            case 'ADM_GROUP':
                $objectType = 'GROUP';
                break;
            default:
                $objectType = null;
        }

        //$objectType = 'LINE';
        // TODO: МОЖНО сделать HELPER + PAGEROLES
        // Good
        // Получить ресурсы принадлежащие текущему Администратору
        $accessColl = new HM_Model_Account_Access_Collection();
/*        if(is_string($objectType)) {
            foreach(array_keys($userColl->load($account['user'])->getRoles()) as $roleIdentifier) {
                if($access->getAcl()->inheritsRole($roleIdentifier, $pageRole) || $roleIdentifier == $pageRole) {
                    $accessColl->resetFilters();
                    $accessColl->setType($objectType);
                    $accessColl->setAccessFilter(
                        $userColl->load($account['user']),
                        $access->getRole($pageRole),
                        $possibilityColl->load($request->getParam('possibility'))->getData('company')
                    );
                }
            }
        }*/

        $accessColl->setType($objectType);
        foreach($userColl->load($account['user'])->getPossibilityCollection()->getObjectsIterator() as $possibilityObject) {
            if($access->getAcl()->inheritsRole($possibilityObject->getData('role')->get('code'), $pageRole) || $possibilityObject->getData('role')->get('code') == $pageRole) {
                if($possibilityObject->getData('company') == $possibilityColl->load($request->getParam('possibility'))->getData('company')) {
                    $accessColl->addEqualFilter('possibility', $possibilityObject);
                }
            }
        }

        if($request->isPost()){
            if($request->getParam('objects')) {
                $intersect = array_intersect($request->getParam('objects'), $accessColl->getCollection()->getIdsIterator());
                // Сохранить пересечения
            } else {
                // Отключить все объекты
            }
        } else {
            $this->view->assign('adminResourcesColl', $accessColl->getCollection());
            $this->view->assign('type', $objectType);
            $this->view->assign('possibility',$possibilityColl->load($request->getParam('possibility')));
        }

    }

    public function addPossibilityAction()
    {
        $request = $this->getRequest();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $userColl = new HM_Model_Account_User_Collection();
        $possibilityColl = new HM_Model_Account_Access_Possibility_Collection();

        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);

        // TODO: Проверить перед созданием
        if($user instanceof HM_Model_Account_User) {
            if($request->isPost()) {
                $possibilityParams = $request->getPost('possibility');
                if(empty($possibilityParams['user'])) {
                    // Восстанавливаем
                    $userColl->addEqualFilter('email', $possibilityParams['account'])->getCollection();
                    $userColl->resetFilters()
                        ->addEqualFilter('login', $possibilityParams['account'])
                        ->getCollection();
                    $manager = current($userColl->getObjectsIterator());

                } else {
                    $manager = $userColl->load($possibilityParams['user']);
                }

                if($manager instanceof HM_Model_Account_User){
                    $possibility = new HM_Model_Account_Access_Possibility();
                    $possibility->setData(array(
                            'user'      => $manager->getData('id'),
                            'role'      => HM_Model_Account_Access::getInstance()->getRole((int)$possibilityParams['role'])->getId(),
                            'company'   => $possibilityParams['company']
                        )
                    );
                    if($possibility->save()) {
                        $this->setAjaxResult($possibility->getData('id'));
                        $this->setAjaxStatus('ok');
                    }
                }
            } else {
                $this->view->assign('hierarchyRoles', $this->_getHierarchyRoles($user));
                $this->view->assign('manager', $request->getQuery('manager'));
            }
        }
    }


    public function removePossibilityAction()
    {
        $request = $this->getRequest();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);
        if($user instanceof HM_Model_Account_User) {
            if($request->isPost()) {
                $possibility = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_Access_Possibility_Factory')
                    ->restore($request->getPost('possibility'));
                $possibility->getData()->setRemoved(true);
                if($possibility->save()) {
                    $this->setAjaxResult($request->getPost('possibility'));
                    $this->setAjaxStatus('ok');
                }
            }
        }
    }



    private function _getHierarchyRoles(HM_Model_Account_User $user)
    {
        $access = HM_Model_Account_Access::getInstance();
        $roles = $_index = array();
        // Построить иерархию Ролей
        foreach(array_keys($user->getRoles()) as $roleIdentifier) {
            $roles = array_merge(
                $roles,
                $access->getInheritsRoles($access->getRole($roleIdentifier), true),
                array($access->getRole($roleIdentifier))
            );
        }
        // Исключить повторяющиеся Роли.
        foreach($roles as $key => $role) {
            if(in_array($role->get('id'), $_index)) {
                unset($roles[$key]);
                continue;
            }
            $_index[] = $role->get('id');
        }

        return $roles;
    }

    /**
     * TODO: rename
     */
    private function _getManagerPossibilityObjects($objectType)
    {
        switch ($objectType) {

        }
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