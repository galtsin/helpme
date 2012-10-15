<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * TODO: Дабавить валидацию данных Zend_Validate
 * ru: Абстрактный класс-контейнер для сущностей
 */
class App_Core_Model_Data_Entity extends App_Core_Model_ModelAbstract
{
    const DATA_STORE_NOT_FOUND = 'Данные не найдены';

    /**
     * Данные сущности - объект-значение
     * @var null|App_Core_Model_Data_Store
     */
    private $_data = null;

    /**
     * Получить идентификационные данные сущности
     * @param null|string $key
     * @return App_Core_Model_Data_Store|array|int|null
     */
    public function getData($key = null)
    {
        if(!$this->_data instanceof App_Core_Model_Data_Store) {
            $this->_data = new App_Core_Model_Data_Store();
        }

        if(!is_null($key)) {
            return $this->_data->get($key);
        }

        return $this->_data;
    }

    /**
     * Добавить идентификационные данные в сущность
     * @param string $key
     * @param mixed $value
     * @return App_Core_Model_Data_Entity
     */
    public function setData($key, $value)
    {
        $this->getData()->set($key, $value);
        return $this;
    }

    /**
     *  Сохранить объект-сущность. Проверка по вставке сущности в БД осуществляется косвенно по наличию getId();
     * Работаем только с Identity данными
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if($this->getData() instanceof App_Core_Model_Data_Store) {
            if($this->isIdentity()){
                // Делегируем обновление записей
                if($this->_update() > 0) {
                    return true;
                }
            } else {
                $result = $this->_insert();
                if(is_int($result) && $result > 0) {
                    $this->getData()->set('id', $result);
                    return true;
                }
            }
            return false;
        }
        throw new Exception('Отсутствуют данные для сохранения');
    }

    /**
     * Проверка, принадлежности сущности системе
     * Определяется по Id сущности в системе
     */
    public function isIdentity()
    {
        if(null !== $this->getData()->getId()){
            return true;
        }
        return false;
    }

    /**
     * Делегирование наследникам
     * Добавление нового объекта
     * Возвращает идентификатор вставленной записи или -1 в случае неудачи
     * @return int
     */
    protected function _insert()
    {
        return -1;
    }

    /**
     * Делегирование наследникам
     * Обновление объекта
     * Возвращает идентификатор обновленной записи или -1 в случае неудачи
     * @return int
     */
    protected function _update()
    {
        return -1;
    }

    /**
     * Удаление объекта
     * @return int
     */
    public function remove()
    {
        if($this->_remove() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Делегирование наследникам
     * Удаление объекта
     * Возвращает идентификатор удаленной записи или -1 в случае неудачи
     * @return int
     */
    protected function _remove()
    {
        return -1;
    }
}