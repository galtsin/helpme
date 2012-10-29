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
     * Получить объекты
     * @param $typeIdentifier
     * @return array|null
     */
    public function getObjects($typeIdentifier = null)
    {
        if($this->isIdentity()) {
            $access = $this->getResource(HM_Model_Account_Access::RESOURCE_NAMESPACE);
            if($access instanceof HM_Model_Account_Access){
                if(null === $typeIdentifier) {
                    $objects = array();
                    foreach($access->getTypes() as $type) {
                        if(array_key_exists($access->getType($type)->get('code'), $this->_objects)){
                            $objects = array_merge($objects, $this->_objects[$access->getType($type)->get('code')]);
                        } else {
                            $this->_objects[$access->getType($type)->get('code')] = $this->_getObjects($type);
                            $objects = array_merge($objects, $this->_objects[$access->getType($type)->get('code')]);
                        }
                    }
                    return $objects;
                } else {
                    if(array_key_exists($access->getType($typeIdentifier)->get('code'), $this->_objects)){
                        return $this->_objects[$access->getType($typeIdentifier)->get('code')];
                    } else {
                        $this->_objects[$access->getType($typeIdentifier)->get('code')] = $this->_getObjects($typeIdentifier);
                        return $this->_objects[$access->getType($typeIdentifier)->get('code')];
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param $typeIdentifier
     * @return array|null
     */
    private function _getObjects($typeIdentifier)
    {
        if($this->isIdentity()) {
            $access = $this->getResource(HM_Model_Account_Access::RESOURCE_NAMESPACE);
            if($access instanceof HM_Model_Account_Access){
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('possibility_get_objects', array(
                        'id_possibility'    => $this->getData('id'),
                        'id_object_type'    => $access->getType($typeIdentifier)->getId()
                    )
                );
                $objects = array();
                if($result->rowCount() > 0){
                    foreach($result->fetchAll() as $row){
                        $object = new App_Core_Model_Data_Store(array(
                                'id'    => (int)$row['o_id_object'],
                                'rw'    => ('W' === $row['o_rw']) ? self::WRITE : self::READ,
                                'type'  => $access->getType($typeIdentifier)
                            )
                        );
                        $object->setWritable(('W' === $row['o_rw']) ? true : false)
                            ->unmarkDirty();
                        $objects[$object->getId()] = $object;
                    }
                }
                return $objects;
            }
        }

        return null;
    }


    public function assignObject(App_Core_Model_Data_Store $object)
    {

    }

    public function saveObjects()
    {
        foreach($this->getObjects() as $object) {

        }
    }

    private function _insertObject($object)
    {

    }

    private function _removeObject($object)
    {

    }

      /**
     * @param App_Core_Model_Data_Store $type
     * @param int $id
     * @return bool
     */
    public function has(App_Core_Model_Data_Store $type, $id)
    {
        return in_array($id, $this->getObjects($type));
    }
}
