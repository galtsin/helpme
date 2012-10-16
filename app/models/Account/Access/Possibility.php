<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 15.10.12
 */
/**
 * ru:
 */
class HM_Model_Account_Access_Possibility extends App_Core_Model_Data_Entity
{
    /**
     * Индекс Записи
     */
    const WRITE = 'write';

    /**
     * Индекс Чтения
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
        return parent::_insert();
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
     * Возвращает индекс значений
     * Получить объекты с типом $type
     * @param $type
     */
    private function _getObjects(App_Core_Model_Data_Store $type)
    {
        if(!array_key_exists($type->get('code'), $this->_objects)) {

            $this->_objects[$type->get('code')] = array(
                self::WRITE => array(),
                self::READ  => array(),
                'index' => array()
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
     * @param App_Core_Model_Data_Store $type
     * @param int $id
     * @return bool
     */
    public function has(App_Core_Model_Data_Store $type, $id)
    {
        return in_array($id, $this->getObjects($type));
    }

    /**
     * Установка прав на чтение/запись
     * По умолчанию проставляет все объекты как на чтение
     * @param App_Core_Model_Data_Store $type
     * @param App_Core_Model_Data_Store $data
     */
    public function assignPrivileges(App_Core_Model_Data_Store $type, App_Core_Model_Data_Store $data)
    {
        if($this->has($type, $data->get('id'))) {
            $objects = $this->getObjects($type);
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
