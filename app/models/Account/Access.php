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
        // Привилегии чтение/запись
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
                        ->set('type', $row['o_op_type'])
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
     * Получить массив ролей-родителей от которых происходит наследование прав текущей роли $role
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
     * Функция выстраивает зависимости ролей для дальнейшей работы с наследованием прав
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
     * Унаследовал ли пользователь текущую роль
     * @param HM_Model_Account_User $user
     * @param $roleIdentifier
     * @param null|int $company
     * @return bool
     */
    public function isUserInheritedRole(HM_Model_Account_User $user, $roleIdentifier, $company = null)
    {
        $role = $this->getRole($roleIdentifier);
        foreach($user->getRoles() as $roleIdentifier => $companies) {
            if($this->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier === $role->get('code')) {
                if(null !== $company){
                    if(in_array($company, $companies)){
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }
        return false;
    }
}