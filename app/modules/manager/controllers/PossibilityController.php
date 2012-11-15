<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * В классе расматриваются 2 вида прав и пользователей.
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
            $this->view->assign('rolesWithInheritance', $this->_getUserRolesWithInheritance());
        }
    }

    /**
     * Получить список пользователей
     */
    public function getManagersAction()
    {
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $userColl = new HM_Model_Account_User_Collection();
        if($userColl->load($account['user']) instanceof HM_Model_Account_User) {
            // Определить роли текущего Менеджера, где он является Администратором Компании или выше
            // и получить список компаний, в которых он может назначать права другим Менеджерам
            $this->view->assign('possibilities', $this->_getManagersPossibility());
        }
    }

    /**
     * Удалить менеджеров
     */
    public function removeManagersAction()
    {
        $request = $this->getRequest();
        if($request->isPost() && $request->getPost('managers')){
            $userColl = new HM_Model_Account_User_Collection();
            foreach($request->getPost('managers') as $manager){
                if($userColl->load($manager) instanceof HM_Model_Account_User){
                    foreach($userColl->load($manager)->getPossibilities() as $possibility) {
                        $possibility->getData()->setRemoved(true);
                        if($possibility->save()) {
                            $this->setAjaxResult(3);
                            $this->setAjaxStatus('ok');
                        }
                    }
                }
            }
        }
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
     * Good
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
                    foreach($manager->getPossibilities() as $possibility) {
                        if($access->getAcl()->inheritsRole($userRoleIdentifier, $possibility->getData()->getRole()->get('code')) || $userRoleIdentifier == $possibility->getData()->getRole()->get('code')) {
                            if(in_array($possibility->getData('company')->getData('id'), $userCompanies)) {
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
     * Good
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
        $manager = HM_Model_Account_User::load($request->getQuery('manager'));
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
        $user = HM_Model_Account_User::load($account['user']);

        $manager = HM_Model_Account_User::load($request->getParam('manager'));

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
     * Good
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
                $objectType = HM_Model_Account_Access::EMPTY_TYPE;
        }

        //$objectType = 'LINE';
        // TODO: МОЖНО сделать HELPER + PAGEROLES
        // Good
        // Получить ресурсы принадлежащие текущему Администратору
        $accessColl = new HM_Model_Account_Access_Collection();
        $accessColl->setType($objectType);
        foreach($userColl->load($account['user'])->getPossibilities() as $possibilityObject) {
            if($access->getAcl()->inheritsRole($possibilityObject->getData('role')->get('code'), $pageRole) || $possibilityObject->getData('role')->get('code') == $pageRole) {
                if($possibilityObject->getData('company')->getData('id') == $possibilityColl->load($request->getParam('possibility'))->getData('company')->getData('id')) {
                    $accessColl->addEqualFilter('possibility', $possibilityObject);
                }
            }
        }

        if($request->isPost()){
            if($request->getParam('objects')) {
                // Эти данные остаются + вставляются
                $safeIds = array_intersect((array)$request->getParam('objects'), $accessColl->getCollection()->getIdsIterator());
            } else {
                $safeIds = array();
            }

            // Сохранить пересечения
            $possibility = $possibilityColl->load($request->getParam('possibility'));
            if($possibility instanceof HM_Model_Account_Access_Possibility) {
                // Определить удаляемые
                foreach($possibility->getObjects($objectType) as $object) {
                    $key = array_search($object->getId(), $safeIds);
                    if(is_bool($key)) {
                        $object->setRemoved(true);
                    } else {
                        unset($safeIds[$key]);
                    }
                }
                foreach($safeIds as $id) {
                    $objectAdded = new App_Core_Model_Data_Store(array(
                            'id'        => $id,
                            'type'      => $access->getType($objectType),
                            'writable'  => true
                        )
                    );
                    $objectAdded->setDirty(true);
                    $possibility->addObject($objectAdded);
                }

                // Фиксируем изменения
                if($possibility->saveObjects()) {
                    $this->setAjaxStatus('ok');
                    $this->setAjaxResult($possibility->getData('id'));
                }
            }
        } else {
            $this->view->assign('adminResourcesColl', $accessColl->getCollection());
            $this->view->assign('type', $objectType);
            $this->view->assign('possibility',$possibilityColl->load($request->getParam('possibility')));
        }

    }

    /**
     * Good
     */
    public function addPossibilityAction()
    {
        $request = $this->getRequest();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $userColl = new HM_Model_Account_User_Collection();


        $admin = HM_Model_Account_User::load($account['user']);

        // TODO: Проверить перед созданием
        if($admin instanceof HM_Model_Account_User) {
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
                    $managerPossibility = new HM_Model_Account_Access_Possibility();
                    $managerPossibility->setUser($manager)
                        ->setRole((int)$possibilityParams['role'])
                        ->setCompany((int)$possibilityParams['company']);

                    if($managerPossibility->save()) {
                        // Если у Менеджера Роль - Администратор компании, то скопировать ему все доступные объекты
                        if($managerPossibility->getData('role')->get('code') == 'ADM_COMPANY') {
                            $possibilityColl = new HM_Model_Account_Access_Possibility_Collection();
                            $possibilityColl->addEqualFilter('urc', array(
                                    'user'      => $admin->getData('id'),
                                    'role'      => 'ADM_COMPANY',
                                    'company'   => $managerPossibility ->getData('company')->getData('id')
                                )
                            );
                            $possibilityColl->getCollection();
                            if(count($possibilityColl->getIdsIterator()) > 0) {
                                foreach($possibilityColl->getObjectsIterator() as $adminPossibility) {
                                    // Перебираем все типы данных
                                    foreach(HM_Model_Account_Access::getInstance()->getTypes() as $type) {
                                        foreach($adminPossibility->getObjects($type) as $object) {
                                            $object->setDirty(true);
                                            $managerPossibility->addObject($object);
                                        }
                                    }
                                }
                                if($managerPossibility->saveObjects()) {
                                    $this->setAjaxResult($managerPossibility ->getData('id'));
                                    $this->setAjaxStatus('ok');
                                }
                            }
                        } else {
                            $this->setAjaxResult($managerPossibility ->getData('id'));
                            $this->setAjaxStatus('ok');
                        }
                    }
                }
            } else {
                $this->view->assign('hierarchyRoles', $this->_getHierarchyRoles($admin));
                $this->view->assign('manager', $request->getQuery('manager'));
            }
        }
    }


    public function removePossibilityAction()
    {
        $request = $this->getRequest();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $user = HM_Model_Account_User::load($account['user']);
        if($user instanceof HM_Model_Account_User) {
            if($request->isPost()) {
                $possibility = HM_Model_Account_Access_Possibility::load($request->getPost('possibility'));
                $possibility->getData()->setRemoved(true);
                if($possibility->save()) {
                    $this->setAjaxResult($request->getPost('possibility'));
                    $this->setAjaxStatus('ok');
                }
            }
        }
    }


    /**
     * @deprecated
     * @param HM_Model_Account_User $user
     * @return array
     */
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
}