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
    const WRITE = 'write';

    /**
     * Индекс Привелегии на Чтение
     */
    const READ = 'read';

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
     * @param App_Core_Model_Data_Store $type
     * @return array
     */
    public function getObjects(App_Core_Model_Data_Store $type)
    {
        $objects = $this->_getObjects($type);
        return $objects['index'];
    }

    /**
     * Получить идектификаторы объектов с типом $type
     * @param App_Core_Model_Data_Store $type
     * @return array('type' => array('write' => array(), 'read' => array(), 'index' => array()))
     */
    private function _getObjects(App_Core_Model_Data_Store $type)
    {
        if(!array_key_exists($type->get('code'), $this->_objects)) {

            $this->_objects[$type->get('code')] = array(
                self::WRITE => array(), // идентификаторы на запись
                self::READ  => array(), // идентификаторы на чтение
                'index' => array() // общий индекс всех идентификаторов
            );

            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('possibility_get_objects', array(
                    'id_possibility'    => $this->getData('id'),
                    'id_object_type'    => $type->get('id')
                )
            );

            if($result->rowCount() > 0){
                foreach($result->fetchAll() as $row){
                    $mode = ('W' === $row['o_rw']) ? self::WRITE : self::READ;
                    $this->_add($type, (int)$row['o_id_object'], $mode);
                }
            }
        }

        return $this->_objects[$type->get('code')];
    }

    /**
     * TODO: В разработке
     */
    public function updateObjects()
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

    /**
     * Установка прав передаваемых объектов на чтение/запись
     * По умолчанию проставляет все объекты как на чтение
     * @param App_Core_Model_Data_Store $type
     * @param App_Core_Model_Data_Store $data
     */
    public function assignPrivileges(App_Core_Model_Data_Store $type, App_Core_Model_Data_Store $data)
    {
        if($this->has($type, $data->get('id'))) {
            $objects = $this->_getObjects($type);
            if(in_array($data->get('id'), $objects[self::WRITE])) {
                $data->setWritable(true);
            } else {
                $data->setWritable(false);
            }
        }
    }

    /**
     * @param App_Core_Model_Data_Store $type
     * @param int $id
     * @param string $mode
     * @return HM_Model_Account_Access_Possibility
     */
    private function _add(App_Core_Model_Data_Store $type, $id, $mode)
    {
        if(self::READ === $mode || self::WRITE === $mode) {
            $this->_objects[$type->get('code')][$mode][] = $this->_objects[$type->get('code')]['index'][] = $id;
        }
        return $this;
    }
}
