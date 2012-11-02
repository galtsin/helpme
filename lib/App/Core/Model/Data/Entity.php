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
     * Здесь хранятся только данные простых типов: int, string, bool, array
     * @var null|App_Core_Model_Data_Store
     */
    private $_data = null;

    /**
     * Данные сущности в виде Объектов
     * @var array
     */
    private $_dataInstances = array();


    public function setDataInstance($key, $value)
    {
        if(is_string($key)) {
            $this->_dataInstances[$key] = $value;
        }
        return $this;
    }


    public function getDataInstance($key)
    {
        return $this->_dataInstances[$key];
    }

    public function hasDataInstance($key)
    {
        return array_key_exists($key, $this->_dataInstances);
    }

    // TODO: В Разработке
    public function getEntityInstance($key)
    {
        if($this->getData($key)) {
            if($this->getDataInstance($key) instanceof App_Core_Model_Data_Entity) {
                return $this->getDataInstance($key);
            } else {
                // Защита от бесконечной рекурсии
                if(!$this->hasDataInstance($key)) {
                    $this->{'set' . ucfirst($key)}($this->getData($key));
                    return $this->{'get' . ucfirst($key)}();
                }
            }
        }

        return null;
    }


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
     * Назначить данные в сущность
     * @param array $options
     */
    public function setData(array $options)
    {
        if($this->_data instanceof App_Core_Model_Data_Store) {
            foreach($options as $key => $value) {
                if(is_string($key) || is_int($key)) {
                    $this->getData()->set($key, $value);
                }
            }
        } else {
            $this->_data = new App_Core_Model_Data_Store($options);
        }
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
                if($this->getData()->isRemoved()) {
                    // Удаление записи
                    if($this->_remove() > 0) {
                        return true;
                    }
                } elseif($this->getData()->isDirty()) {
                    // Обновление записи
                    if($this->_update() > 0) {
                        $this->getData()->unmarkDirty();
                        return true;
                    }
                }
            } else {
                // Добавление записи
                $result = $this->_insert();
                if(is_int($result) && $result > 0) {
                    $this->getData()->set('id', $result);
                    $this->getData()->unmarkDirty();
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
     * Универсальный ответ неудачи
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
     * Универсальный ответ неудачи
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
     * Универсальный ответ неудачи
     * Удаление объекта
     * Возвращает идентификатор удаленной записи или -1 в случае неудачи
     * @return int
     */
    protected function _remove()
    {
        return -1;
    }
}