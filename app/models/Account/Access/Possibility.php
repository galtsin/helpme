<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Привилегии и Доступные объекты
 */
class HM_Model_Account_Access_Possibility extends App_Core_Model_Data_Entity
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
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->addResource(HM_Model_Account_Access::getInstance(), HM_Model_Account_Access::RESOURCE_NAMESPACE);
    }

    /**
     * Добавить
     * @return int
     */
    protected function _insert()
    {
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('possibility_add', array(
                'id_user'       => (int)$this->getData('user'),
                'id_role'       => (int)$this->getData('role'),
                'id_company'    => (int)$this->getData('company')
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
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('group_update_identity', array(
                    'id_possibility'    => (int)$this->getData('id'),
                    'id_user'           => (int)$this->getData('user'),
                    'id_role'           => (int)$this->getData('role'),
                    'id_company'        => (int)$this->getData('company')
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
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
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
            $access = $this->getResource(HM_Model_Account_Access::RESOURCE_NAMESPACE);
            $objects = array();
            if($access instanceof HM_Model_Account_Access){
                if(null === $typeIdentifier) {
                    foreach($this->_objects as $_objects) {
                        $objects = array_merge($objects, $_objects);
                    }
                } else {
                    if(!array_key_exists($access->getType($typeIdentifier)->get('code'), $this->_objects)){
                        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                            ->execute('possibility_get_objects', array(
                                'id_possibility'    => $this->getData('id'),
                                'id_object_type'    => $access->getType($typeIdentifier)->getId()
                            )
                        );
                        if($result->rowCount() > 0){
                            foreach($result->fetchAll() as $row){
                                $_object = new App_Core_Model_Data_Store(array(
                                        'id'    => (int)$row['o_id_object'],
                                        'type'  => $access->getType($typeIdentifier)
                                    )
                                );
                                $_object->setWritable(('W' === $row['o_rw']) ? true : false)
                                    ->unmarkDirty();
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
     * Добавить объект к набору
     * @param App_Core_Model_Data_Store $object
     */
    public function addObject(App_Core_Model_Data_Store $object)
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
                $this->_removeObject($object);
            } elseif ($object->isDirty()) {
                $this->_insertObject($object);
            }
        }
    }

    /**
     * Вставить объект в БД
     * @param App_Core_Model_Data_Store $object
     * @return int
     */
    private function _insertObject(App_Core_Model_Data_Store $object)
    {
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('possibility_add_object', array(
                'id_possibility'    => $this->getData('id'),
                'id_object_type'    => $object->get('type')->getId(),
                'id_object'         => $object->getId(),
                'rw'                => $object->isWritable() ? self::WRITE : self::READ

            )
        );

        $row = $result->fetchRow();
        if($row['o_id_possibility_object'] > 0) {
            $object->set('id', (int)$row['o_id_possibility_object']);
            return $row['o_id_possibility_object'];
        }

        return parent::_insert();
    }

    /**
     * Удалить объект из БД
     * @param $object
     * @return int
     */
    private function _removeObject(App_Core_Model_Data_Store $object)
    {
        if($this->isIdentity()) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
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
                return $row['o_id_possibility_object'];
            }
        }

        return parent::_remove();
    }

    /**
     * @param App_Core_Model_Data_Store $object
     * @return bool
     */
    public function has(App_Core_Model_Data_Store $object)
    {
        foreach($this->getObjects($object->get('type')->get('code')) as $_object) {
            if($_object->getId() === $object->getId()) {
                return true;
            }
        }
        return false;
    }
}
