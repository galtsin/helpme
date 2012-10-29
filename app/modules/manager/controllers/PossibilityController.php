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
            $this->view->assign(
                'hierarchyRoleAndCompanies',
                $this->_getHierarchyRoleAndCompanies(
                    App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user'])
                )
            );
        }
    }

    public function getManagersAction()
    {

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
                        $possibilityColl->addEqualFilter('company', $company);
                    }
                    foreach($possibilityColl->getCollection()->getObjectsIterator() as $possibility) {
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

    public function addManagerRole(){}
    public function removeManagerRole(){}

    /**
     * TODO: текущая отладка
     */
    private function _editPossibilityObjectsAction()
    {
        $request = $this->getRequest();
        $access = HM_Model_Account_Access::getInstance();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();

        // Роль текущей страницы
        $pageRole = 'ADM_COMPANY';
        $objectType = 'LINE';
        $userColl = new HM_Model_Account_User_Collection();
        $accessColl = new HM_Model_Account_Access_Collection();

        // 1. Получить русурсы принадлежащие компании
        // 2. Получить ресурсы принадлежащие текущему Менеджеру
        // 3. Получить пересечение ресурсов. Это пересечение - доступные текущему Менеджеру ресурсы
        // 4. Определить ресурсы уже отмеченные для редактируемого менеджера

        // QUICK способ Используем укороченный вариант только пункты 2 и 4

        $companyLines = $userLines = array();

        $possibilityColl = new HM_Model_Account_Access_Possibility_Collection();
        $possibility = $possibilityColl->load($request->getParam('possibility'));

        // 1. Получить русурсы принадлежащие компании
        $lineColl = new HM_Model_Counseling_Structure_Line_Collection();
        $lineColl->addEqualFilter('company', $possibility->getData('company'));
        $companyLines = $lineColl->getCollection()
            ->getIdsIterator();

        $companyLines = array(11, 15);
        // 2. Получить ресурсы принадлежащие текущему Менеджеру
        // Постоянная часть
        foreach(array_keys($userColl->load($account['user'])->getRoles()) as $roleIdentifier) {
            if($access->getAcl()->inheritsRole($roleIdentifier, $pageRole) || $roleIdentifier == $pageRole) {
                $accessColl->clear();
                $accessColl->resetFilters();
                $accessColl->setType($objectType);
                $accessColl->setAccessFilter($userColl->load($account['user']), $access->getRole($pageRole), $possibility->getData('company'));
                $userLines = array_merge($userLines, $accessColl->getCollection()->getIdsIterator());
            }
        }


        // 3. Получить пересечение ресурсов
        $resources = array_intersect($companyLines, $userLines);

        // 4. Определить ресурсы уже отмеченные для редактируемого менеджера
        //Zend_Debug::dump($possibility->getObjects($access->getType($objectType)));

        $this->view->assign('resources', $resources);
        $this->view->assign('possibility', $possibility);

        if($request->isPost()){

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
        if(is_string($objectType)) {
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
                if($possibility->remove()) {
                    $this->setAjaxResult($request->getPost('possibility'));
                    $this->setAjaxStatus('ok');
                }
            }
        }
    }

    private function _getHierarchyRoleAndCompanies(HM_Model_Account_User $user)
    {
        $access = HM_Model_Account_Access::getInstance();
        $allowedRoles = array();
        foreach($user->getRoles() as $roleIdentifier => $companies) {
            foreach($access->getRoles() as $role){
                if($access->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier == $role->get('code')) {
                    if(!array_key_exists($role->get('code'), $allowedRoles)) {
                        $allowedRoles[$role->get('code')] = $companies;
                    } else {
                        $allowedRoles[$role->get('code')] = array_unique(array_merge($allowedRoles[$role->get('code')], $companies));
                    }
                }
            }
        }
        return $allowedRoles;
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