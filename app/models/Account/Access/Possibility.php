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
     * Инициализация
     */
    protected function _init()
    {
        parent::_init();
        $this->setData('possibility', array(
                'write' => array(),
                'read'  => array(),
                'index' => array()
            )
        );
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
     * Проверка существования идентификатора
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return in_array($id, $this->_getIndex());
    }

    /**
     * Установка прав на чтение/запись
     * По умолчанию проставляет все объекты как на чтение
     * @param App_Core_Model_Data_Store $store
     */
    public function setPrivileges(App_Core_Model_Data_Store $store)
    {
        if($this->has($store->get('id'))) {
            $possibility = $this->getData('possibility');
            if(in_array($store->get('id'), $possibility[self::WRITE])) {
                $store->setWritable(true);
            } else {
                $store->setWritable(false);
            }
        }
    }

    /**
     * @param $id
     * @param string $mode (read|write)
     * @return HM_Model_Account_Access_Possibility
     */
    public function add($id, $mode)
    {
        if(!$this->has($id)) {
            if(self::READ === $mode || self::WRITE === $mode) {
                $possibility = $this->getData('possibility');
                $possibility[$mode][] = $possibility['index'][] = $id;
                $this->setData('possibility', $possibility);
            }
        }
        return $this;
    }

    /**
     * Получить индекс
     * @return array
     */
    protected function _getIndex()
    {
        $possibility = $this->getData('possibility');
        return $possibility['index'];
    }

    // Восстанавливают объекты
/*    public function getUser(){}
    public function getRole(){}
    public function getCompany(){}*/
}
