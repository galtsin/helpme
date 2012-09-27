<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Класс предоставляет доступ к различным русурсам системы
 */

class HM_Model_Account_Access extends App_Core_Model_ModelAbstract
{
    /**
     * Массив ТипоОбъектов.
     * Null используется для отложенной загрузки
     * @var null|array App_Core_Model_Data_Store
     */
    private $_types = null;

    /**
     * Массив системных ролей
     * @var null|array App_Core_Model_Data_Store
     */
    private $_roles = null;

    /**
     * Список страниц
     * @var null|array Zend_Navigation_Page_Uri
     */
    private $_pages = null;

    /**
     * Список операций (url-адресов)
     * @var null
     */
    private $_operations = null;

    /**
     * @var Zend_Acl|null
     */
    private $_acl = null;

    /**
     * Синглтон
     * @var HM_Model_Account_Access|null
     */
    private static $_instance = null;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->_acl = Zend_Registry::get('acl');
        $this->_acl->deny();
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * Реализация паттерна Singleton
     * @return HM_Model_Account_Access
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Получить типы ресурсов системы
     * @return array
     */
    public function getTypes()
    {
        if(null === $this->_types) {
            $types = array();
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('possibility_get_object_types', array());
            foreach($result->fetchAll() as $row) {
                $type = new App_Core_Model_Data_Store();
                $type->set('id', $row['id'])
                     ->set('code', $row['code'])
                     ->set('name', $row['name']);
                array_push($types, $type);
            }
            $this->_types = $types;
        }
        return $this->_types;
    }

    /**
     * Получить список ролей системы
     * Автоматическая привязка ролей при наличии корневой роли. (pid == NULL)
     * @return array App_Core_Model_Data_Store
     */
    public function getRoles()
    {
        if(null === $this->_roles) {
            $roles = array();
            $rootRole = null;

            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('possibility_get_roles', array());

            foreach($result->fetchAll() as $row) {
                $role = new App_Core_Model_Data_Store();
                $role->set('id', $row['id'])
                     ->set('pid', $row['pid']) // может быть null
                     ->set('code', $row['code'])
                     ->set('name', $row['name']);
                array_push($roles, $role);

                // Определяем корневую роль, у которой в БД запись = NULL.
                // При отсутствии данной записи - необходимо использовать ручную привязку ролей
                if(is_null($role->get('pid'))){
                    $rootRole = $role;
                }
            }

            $this->_roles = $roles;

            // Привязка ролей от корня ролей
            if(!is_null($rootRole)) {
                $this->linkChainRoles($rootRole);
            }
        }

        return $this->_roles;
    }

    /**
     * Получить объект данных Тип
     * @param $typeIdentifier
     * @return App_Core_Model_Data_Store
     * @throws Exception
     */
    public function getType($typeIdentifier)
    {
        if(is_int($typeIdentifier)) {
            $field = 'id';
        } elseif(is_string($typeIdentifier)) {
            $field = 'code';
        } else{
            throw new Exception("Type incorrect identifier value (Некорректное значение идентификатора типа)");
        }
        foreach($this->getTypes() as $type) {
            if($typeIdentifier === $type->get($field)) {
                return $type;
            }
        }
        throw new Exception("Type the identifier '" . $typeIdentifier . "' is not found (Тип с идентификатором '" . $typeIdentifier . "' не найден)");
    }

    /**
     * Получить объект данных Роль
     * @param mixed $roleIdentifier
     * @return App_Core_Model_Data_Store
     * @throws Exception
     */
    public function getRole($roleIdentifier)
    {
        if(is_int($roleIdentifier)) {
            $field = 'id';
        } elseif(is_string($roleIdentifier)) {
            $field = 'code';
        } else{
            throw new Exception("Role incorrect identifier value (Некорректное значение идентификатора роли)");
        }
        foreach($this->getRoles() as $role) {
            if($roleIdentifier === $role->get($field)) {
                return $role;
            }
        }
        throw new Exception("Role the identifier '" . $roleIdentifier . "' is not found (Роль с идентификатором '" . $roleIdentifier . "' не найдена)");
    }

    /**
     * Вернуть Zend_Acl
     * @return mixed|null|Zend_Acl
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Массив ролей от которых происходит наследование
     * @param App_Core_Model_Data_Store $role
     * @return array App_Core_Model_Data_Store
     */
    public function getInheritsRoles(App_Core_Model_Data_Store $role)
    {
        $inherits = array();
        foreach($this->getRoles() as $_role) {
            if(is_int($_role->get('pid')) && $_role->get('pid') === $role->get('id')) {
                array_push($inherits, $_role);
            }
        }
        return $inherits;
    }

    /**
     * Построить цепочку связей ролей снизу вверх c привязкой к роделельским элементам
     * TODO: Не должно быть использование вызовов self::getRole и self::getRoles т.к. приведет к зацикливанию в автоматической привязке ролей в методе getRoles()
     * @param App_Core_Model_Data_Store $role
     */
    public function linkChainRoles(App_Core_Model_Data_Store $role)
    {
        if(!$this->getAcl()->hasRole($role->get('code'))) {
            $parents = $this->getInheritsRoles($role);
            if(count($parents) > 0){
                $codes = array();
                foreach($parents as $parent){
                    $this->linkChainRoles($parent);
                    $codes[] = $parent->get('code');
                }
                $this->getAcl()->addRole(new Zend_Acl_Role($role->get('code')), $codes);
            } else {
                $this->getAcl()->addRole(new Zend_Acl_Role($role->get('code')));
            }
        }
    }

    /**
     * ДАлее не проработано!!!!
     */


    /**
     * @param HM_Model_Account_User $user
     * @param App_Core_Model_Data_Store $role
     * @return bool
     */
    public function isAllowedRole(HM_Model_Account_User $user, App_Core_Model_Data_Store $role)
    {
        foreach($user->getRoles() as $roleIdentifier => $companies) {
            if($this->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier === $role->get('code')) {
                return true;
            }
        }
        return false;
    }


    /**
     * TODO: Объединить текущий класс с HM_Model_Account_AccessMenu
     */

    /**
     * TODO: в разработке.
     * Загрузить список ролей для текущего действия
     * В приложении перебрать роли и объединить объекты
     * @param string $action
     * @return array App_Core_Model_DataObject
     */
    public function getActionRoles($action)
    {
        $roles = array();
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('possibility_get_roles_by_action', array(
                'action'           => $action,
            )
        );
        if($result->rowCount() > 0) {
            foreach($result->fetchAll() as $row) {
                array_push($roles, $this->getRole($row['id_role']));
            }
        }
        return $roles;
    }

    /**
     * Final
     * Получить массив разрешенных объектов для пользователя по определенной роли
     * @param HM_Model_Account_User $user
     * @param int $company
     * @param App_Core_Model_DataObject $role
     * @param App_Core_Model_DataObject $type
     * @return array ids
     */
    public function getObjectsForUserRole(HM_Model_Account_User $user, $company, App_Core_Model_DataObject $role,  App_Core_Model_DataObject $type)
    {
        $objects = array();
        if($this->isAllowedUserRole($user, $company, $role)) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('possibility_get_objects', array(
                    'id_user'           => $user->getData()->getId(),
                    'id_role'           => $role->get('id'),
                    'id_company'        => $company,
                    'id_object_type'    => $type->get('id')
                )
            );
            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    array_push($objects, (int)$row['id_object']);
                }
            }
        }
        return array_unique($objects);
    }

    /**
     * Final
     * Проверить переданные в качестве параметра права на наличие их у данного пользователя с учетом наследования
     * Используется система наследований
     * @param HM_Model_Account_User $user
     * @param int $company
     * @param App_Core_Model_DataObject $compareRole
     * @return bool
     */
    public function isAllowedUserRole(HM_Model_Account_User $user, $company, App_Core_Model_DataObject $compareRole)
    {
        $this->linkRole($compareRole);
        $listRolesOfCompanies= $user->getRoles();
        if(array_key_exists($company, $listRolesOfCompanies)) {
            foreach($listRolesOfCompanies[$company] as $roleIdentifier) {
                $role = $this->getRole($roleIdentifier);
                $this->linkRole($role);
                if($this->getAcl()->inheritsRole($compareRole->get('code'), $role->get('code'))
                   || $compareRole->get('code') == $role->get('code')) {
                    return true;
                }
            }
        }
        return false;
    }
}