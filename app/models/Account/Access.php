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
     * @deprecated
     */
    const RESOURCE_NAMESPACE = 'account_access';

    /**
     * Роль "по умолчанию"
     */
    const EMPTY_ROLE = 'GUEST';

    /**
     * Тип ресурсов "по умолчанию"
     */
    const EMPTY_TYPE = 'EMPTY';

    /**
     * Массив ТипоОбъектов.
     * Null используется для отложенной загрузки
     * @var null|array App_Core_Model_Store_Data
     */
    private $_types = null;

    /**
     * Массив системных ролей
     * @var null|array App_Core_Model_Store_Data
     */
    private $_roles = null;

    /**
     * Список страниц
     * @var null|Zend_Navigation_Page
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

        // TODO: Необходимо ли?
        $this->getRoles();
        $this->getTypes();

        // TODO: Перенести в БД. Выставить доступ
        $this->getAcl()
            ->allow('ADM_TARIFF', 'TARIFF', array('W', 'R'))
            ->allow('ADM_GROUP', 'GROUP', array('W', 'R'))
            ->allow('ADM_LINE', 'LINE', array('W', 'R'));

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
            $this->_types = array();

            $result = App::getResource('FnApi')
                ->execute('possibility_get_object_types', array());

            foreach($result->fetchAll() as $row) {
                $type = new App_Core_Model_Store_Data(array(
                        'id'    => (int)$row['id'],
                        'code'  => $row['code'],
                        'name'  => $row['name']
                    )
                );

                $this->getAcl()->addResource($type->get('code'));
                $this->_types[] = $type;
            }
        }

        return $this->_types;
    }

    /**
     * Получить список ролей системы
     * Автоматическая привязка ролей при наличии корневой роли. (pid == NULL)
     * @return array App_Core_Model_Store_Data
     */
    public function getRoles()
    {
        if(null === $this->_roles) {
            $this->_roles = array();
            $rootRole = null;

            $result = App::getResource('FnApi')
                ->execute('possibility_get_roles', array());

            foreach($result->fetchAll() as $row) {
                $role = new App_Core_Model_Store_Data(array(
                        'id'    => (int)$row['id'],
                        'pid'   => $row['pid'],
                        'code'  => $row['code'],
                        'name'  => $row['name']
                    )
                );
                $this->_roles[] = $role;

                // Определяем корневую роль, у которой в БД запись = NULL.
                // TODO: При отсутствии данной записи в БД - необходимо использовать ручную привязку ролей
                if(is_null($role->get('pid'))){
                    $rootRole = $role;
                }
            }

            // Привязка ролей от корня ролей
            if(!is_null($rootRole)) {
                $this->linkChainRoles($rootRole);
            }
        }

        return $this->_roles;
    }

    /**
     * Получить список доступных системных страниц
     * @return Zend_Navigation
     */
    public function getPages()
    {
        if(null === $this->_pages) {
            $this->_pages = new Zend_Navigation();

            $result = App::getResource('FnApi')
                ->execute('possibility_get_pages', array());

            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $page = new Zend_Navigation_Page_Uri();
                    $page->setLabel($row['o_label'])
                        ->setId((int)$row['o_id_page'])
                        ->setPrivilege($row['o_uri']);
                    $this->_pages->addPage($page);
                }
            }
        }

        return $this->_pages;
    }

    /**
     * Получить список системных операций - действий
     * @return null|App_Core_Model_Store_Data[]
     */
    public function getOperations()
    {
        if(null === $this->_operations) {
            $this->_operations = array();

            $result = App::getResource('FnApi')
                ->execute('possibility_get_operations', array());

            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $operation = new App_Core_Model_Store_Data();
                    $operation->set('id', $row['o_id_operation'])
                        ->set('label', $row['o_label'])
                        ->set('is_enabled', $row['o_is_enabled'])
                        ->set('uri', $row['o_uri'])
                        ->setDirty(false);
                    $this->_operations[] = $operation;
                }
            }
        }

        return $this->_operations;
    }

    public function getPage($pageIdentifier)
    {

    }

    /**
     * В качестве идентификаторов выступает - id или uri
     * TODO: RecursiveIteratorIterator для поиска значения
     * @param int|string (id || uri) $operationIdentifier
     * @return App_Core_Model_Store_Data
     * @throws Exception
     */
    public function getOperation($operationIdentifier)
    {
        switch(gettype(trim($operationIdentifier))){
            case 'integer':
                $field = 'id';
                break;
            case 'string':
                $field = 'uri';
                break;
            case 'object':
                if($operationIdentifier instanceof App_Core_Model_Store_Data){
                    return $operationIdentifier;
                }
                break;
            default:
                throw new Exception("Type incorrect identifier value (Некорректное значение идентификатора типа)");
        }

        if(isset($field)){
            foreach($this->getOperations() as $operation) {
                if($operationIdentifier === $operation->get($field)) {
                    return $operation;
                }
            }
        }

        return null;
    }

    /**
     * Получить объект данных Тип
     * В качестве идентификаторов выступает - id или code
     * TODO: RecursiveIteratorIterator для поиска значения
     * @param $typeIdentifier
     * @return App_Core_Model_Store_Data
     * @throws Exception
     */
    public function getType($typeIdentifier)
    {
        if(is_int($typeIdentifier)) {
            $field = 'id';
        } elseif(is_string($typeIdentifier)) {
            $field = 'code';
        } elseif($typeIdentifier instanceof App_Core_Model_Store_Data) {
            return $this->getType((int)$typeIdentifier->getId());
        } else{
            throw new Exception("Type incorrect identifier value (Некорректное значение идентификатора типа)");
        }
        foreach($this->getTypes() as $type) {
            if($typeIdentifier === $type->get($field)) {
                return $type;
            }
        }
        throw new Exception("Type the identifier '" . $typeIdentifier . "' is not found (Тип с идентификатором '" . (string)$typeIdentifier . "' не найден)");
    }

    /**
     * Получить объект данных Роль
     * В качестве идентификаторов выступает - id или code
     * TODO: RecursiveIteratorIterator для поиска значения
     * @param mixed $roleIdentifier
     * @return App_Core_Model_Store_Data
     * @throws Exception
     */
    public function getRole($roleIdentifier)
    {
        if(is_int($roleIdentifier)) {
            $field = 'id';
        } elseif(is_string($roleIdentifier)) {
            $field = 'code';
        } elseif($roleIdentifier instanceof App_Core_Model_Store_Data) {
            return $this->getRole((int)$roleIdentifier->getId());
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
     * Проверить, существует ли указанный тип объектов
     * @param $typeIdentifier
     * @return bool
     */
    public function hasType($typeIdentifier)
    {
        if(is_int($typeIdentifier)) {
            $field = 'id';
        } else {
            settype($typeIdentifier, 'string');
            $field = 'code';
        }

        foreach($this->getTypes() as $type) {
            if($typeIdentifier === $type->get($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Zend_Acl
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Получить массив ролей от которых происходит наследование
     * @param App_Core_Model_Store_Data $role
     * @param bool $recursive
     * @return array App_Core_Model_Store_Data
     */
    public function getInheritsRoles(App_Core_Model_Store_Data $role, $recursive = false)
    {
        $inherits = array();
        foreach($this->getRoles() as $_role) {
            if(is_int($_role->get('pid')) && $_role->get('pid') == $role->get('id')) {
                array_push($inherits, $_role);
            }
        }
        if(true === $recursive) {
            foreach($inherits as $inherit) {
                if($inherit->get('pid')) {
                    $inherits = array_merge($inherits, $this->getInheritsRoles($inherit, true));
                }
            }
        }
        return $inherits;
    }

    /**
     * Построить цепочку связей ролей снизу вверх c привязкой к роделельским элементам
     * TODO: Не должно быть использование вызовов self::getRole и self::getRoles внутри данной функции т.к. приведет к зацикливанию в автоматической привязке ролей в методе getRoles()
     * @param App_Core_Model_Store_Data $role
     */
    public function linkChainRoles(App_Core_Model_Store_Data $role)
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
     * Далее не проработано!!!!
     */

    /**
     * TODO:
     * 1. Разрешен ли пользователю доступ к объекту
     * 2. Загрузить список доступных ресурсов ObjectType + IdObject
     * if(isAllowed && isWritable)
     */

    public function isWritable($user, $role, $company, $type, $objectId){}



    /**
     * @param HM_Model_Account_User $user
     * @param App_Core_Model_Store_Data $role
     * @return bool
     */
    public function isAllowedRole(HM_Model_Account_User $user, App_Core_Model_Store_Data $role)
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
        $result = App::getResource('FnApi')
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
            $result = App::getResource('FnApi')
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