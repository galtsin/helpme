<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Привилегии и Доступные объекты
 * Позволяет устанавливать объекты и привилегии для них
 */
class HM_Model_Account_Access_Possibility extends App_Core_Model_Store_Entity
{
    /**
     * Индекс Привелегии на Запись
     */
    const WRITE = 'W';

    /**
     * Индекс Привелегии на Чтение
     */
    const READ = 'R';

    /**
     * Список идентификаторов объектов
     * @var null|array
     */
    private $_objects = array();

    /**
     * @param int $id
     * @return HM_Model_Account_Access_Possibility|null
     */
    public static function load($id)
    {
        $id = intval($id);
        if($id == 0 || !empty($id)) {
            $result = App::getResource('FnApi')
                ->execute('possibility_get_identity', array(
                    'id_possibility' => $id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $possibility = new self();
                $possibility->setUser((int)$row['o_id_user'])
                    ->setRole((int)$row['o_id_role'])
                    ->setCompany((int)$row['o_id_company']);
                $possibility->getData()
                    ->set('id', $id)
                    ->setDirty(false);

                return $possibility;
            }
        }


        return null;
    }

    /**
     * Назначить Пользователя
     * @param $user
     * @return HM_Model_Account_Access_Possibility
     */
    public function setUser($user)
    {
        if($user instanceof HM_Model_Account_User) {
            $this->getData()->set('user', $user);
        } elseif (null !== $user && is_int($user)) {
            self::setUser(HM_Model_Account_User::load($user));
        }
        return $this;
    }

    /**
     * Назначить Компанию
     * @param $company
     * @return HM_Model_Account_Access_Possibility
     */
    public function setCompany($company)
    {
        if($company instanceof HM_Model_Billing_Company) {
            $this->getData()->set('company', $company);
        } elseif (null !== $company && is_int($company)) {
            self::setCompany(HM_Model_Billing_Company::load($company));
        }
        return $this;
    }

    /**
     * Назначить Роль
     * @param $roleIdentifier
     * @return HM_Model_Account_Access_Possibility
     */
    public function setRole($roleIdentifier)
    {
        try {
            $this->getData()->set('role', HM_Model_Account_Access::getInstance()->getRole($roleIdentifier));
        } catch(Exception $ex) {
            $this->getData()->set('role', HM_Model_Account_Access::getInstance()->getRole(HM_Model_Account_Access::EMPTY_ROLE));
        }

        return $this;
    }

    /**
     * Добавить
     * @return int
     */
    protected function _insert()
    {
        $result = App::getResource('FnApi')
            ->execute('possibility_add', array(
                'id_user'       => $this->getData('user')->getData('id'),
                'id_role'       => $this->getData('role')->getId(),
                'id_company'    => $this->getData('company')->getData('id')
            )
        );
        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            return (int)$row['o_id_possibility'];
        }
        return parent::_insert();
    }

    /**
     * Обновить
     * @return int
     */
    protected function _update()
    {
        if($this->getData()->isDirty()) {
            $result = App::getResource('FnApi')
                ->execute('possibility_update_identity', array(
                    'id_possibility'    => (int)$this->getData('id'),
                    'id_user'           => $this->getData('user')->getData('id'),
                    'id_role'           => $this->getData('role')->getId(),
                    'id_company'        => $this->getData('company')->getData('id')
                )
            );
            $row = $result->fetchRow();
            if($row['o_id_possibility'] !== -1) {
                return $this->getData('id');
            }
        }

        return parent::_update();
    }

    /**
     * Удалить Possibility
     * @return int
     */
    protected function _remove()
    {
        if($this->isIdentity()) {
            $result = App::getResource('FnApi')
                ->execute('possibility_del', array(
                    'id_possibility' => $this->getData('id')
                )
            );

            $row = $result->fetchRow();
            if((int)$row['o_id_possibility'] == $this->getData('id')) {
                $this->getData()->clear();
                return $row['o_id_possibility'];
            }
        }

        return parent::_remove();
    }

    /**
     * Получить объекты по их типу
     * @param $typeIdentifier
     * @return array|null
     */
    public function getObjects($typeIdentifier = null)
    {

        if($this->isIdentity()) {
            $access = HM_Model_Account_Access::getInstance();
            $objects = array();
            if($access instanceof HM_Model_Account_Access){
                if(null === $typeIdentifier) {
                    foreach($this->_objects as $_objects) {
                        $objects = array_merge($objects, $_objects);
                    }
                } else {
                    if(!array_key_exists($access->getType($typeIdentifier)->get('code'), $this->_objects)){
                        $result = App::getResource('FnApi')
                            ->execute('possibility_get_objects', array(
                                'id_possibility'    => $this->getData('id'),
                                'id_object_type'    => $access->getType($typeIdentifier)->getId()
                            )
                        );
                        if($result->rowCount() > 0){
                            foreach($result->fetchAll() as $row){
                                $_object = new App_Core_Model_Store_Data(array(
                                        'id'    => (int)$row['o_id_object'],
                                        'type'  => $access->getType($typeIdentifier)
                                    )
                                );
                                $_object->setWritable(('W' === $row['o_rw']) ? true : false)
                                    ->setDirty(false);
                                $objects[] = $_object;
                            }
                        }
                        $this->_objects[$access->getType($typeIdentifier)->get('code')] = $objects;
                    }
                    $objects = $this->_objects[$access->getType($typeIdentifier)->get('code')];
                }
            }
            return $objects;
        }
        return null;
    }

    /**
     * TODO: В Идеале передавать объекты App_Core_Model_Store_Entity
     * Добавить объект к набору
     * @param App_Core_Model_Store_Data $object
     */
    public function addObject(App_Core_Model_Store_Data $object)
    {
        if(!$this->has($object)) {
            $this->_objects[$object->get('type')->get('code')][] = $object;
        }
    }

    /**
     * Сохранить объекты
     */
    public function saveObjects()
    {
        foreach($this->getObjects() as $object) {
            if($object->isRemoved()) {
                $result = $this->_removeObject($object);
            } elseif ($object->isDirty()) { // TODO: необходимо ли?
                $result = $this->_insertObject($object);
            }
            if(!empty($result)) {
                if(is_int($result) && $result == -1) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Вставить объект в БД
     * @param App_Core_Model_Store_Data $object
     * @return int
     */
    private function _insertObject(App_Core_Model_Store_Data $object)
    {
        $result = App::getResource('FnApi')
            ->execute('possibility_add_object', array(
                'id_possibility'    => $this->getData('id'),
                'id_object_type'    => $object->get('type')->getId(),
                'id_object'         => $object->getId(),
                'rw'                => $object->isWritable() ? self::WRITE : self::READ

            )
        );

        $row = $result->fetchRow();
        if($row['o_id_possibility_object'] != -1 && (int)$row['o_id_possibility_object'] > 0) {
            return (int)$row['o_id_possibility_object'];
        }

        return parent::_insert();
    }

    /**
     * Удалить объект из БД
     * @param $object
     * @return int
     */
    private function _removeObject(App_Core_Model_Store_Data $object)
    {
        if($this->isIdentity()) {
            $result = App::getResource('FnApi')
                ->execute('possibility_del_object', array(
                    'id_possibility'    => $this->getData('id'),
                    'id_object_type'    => $object->get('type')->getId(),
                    'id_object'         => $object->getId(),
                )
            );

            $row = $result->fetchRow();
            if((int)$row['o_id_possibility_object'] == $object->getId()) {
                $object->clear();
                unset($object);
                return (int)$row['o_id_possibility_object'];
            }
        }

        return parent::_remove();
    }

    /**
     * @param App_Core_Model_Store_Data $object
     * @return bool
     */
    public function has(App_Core_Model_Store_Data $object)
    {
        foreach($this->getObjects($object->get('type')->get('code')) as $_object) {
            if($_object->getId() === $object->getId()) {
                return true;
            }
        }
        return false;
    }
}
