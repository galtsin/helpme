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
     * TODO:
     * Данные сущности в виде Объектов
     * @deprecated use self::_dataObjects
     * @var array
     */
    private $_dataInstances = array();

    /**
     * Сопутствующие ссылки на другие объекты
     * @var array
     */
    private $_dataObjects = array();

    /**
     * @param $key
     * @return bool
     */
    public function hasDataInstance($key)
    {
        return array_key_exists($key, $this->_dataInstances);
    }

    /**
     * TODO:  В разработке
     * Извлечь экземпляр из Хранилища
     * @deprecated
     * @param $key
     * @return null
     */
    protected function _getDataInstance($key){
        if(is_string($key)) {
            if(self::hasDataInstance($key)) {
                return $this->_dataInstances[$key];
            } else {
                // Замена нотации, например: user_owner = setUserOwner();
                $method = 'set';
                foreach(explode('_', $key) as $part) {
                    $method .= ucfirst($part);
                }
                if(method_exists($this, $method) && $this->getData($key)) {
                    // Заглушка от бесконечного зацикливания. Не пройдет из за self::hasDataInstance
                    $this->_dataInstances[$key] = null;
                    $this->{$method}($this->getData($key));
                    return self::_getDataInstance($key);
                }
            }
        }

        return null;
    }

    /**
     * TODO: В разработке
     * Установить экземпляр в Хранилище
     * @deprecated
     * @param $key
     * @param App_Core_Model_Data_Entity $value
     * @return self
     */
    protected function _setDataInstance($key, App_Core_Model_Data_Entity $value)
    {
        if(is_string($key) && $value instanceof App_Core_Model_Data_Entity) {
            $this->getData()->set($key, $value->getData()->getId());
            $this->_dataInstances[$key] = $value;
        }

        return $this;
    }


    /**
     * TODO: в разработке
     * @param $key
     * @param $values
     */
    protected function _setSeveralDataObject($key, array $values)
    {
        if(is_array($values)) {
            $this->_dataObjects[$key] = array();
            $this->getData()->set($key, array());

            foreach($values as $value) {
                if($value instanceof App_Core_Model_Data_Entity) {
                    $this->_setDataObject($key, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Установка одиночного объекта
     * @param $key
     * @param App_Core_Model_Data_Entity $entity
     * @return App_Core_Model_Data_Entity
     */
    protected function _setSingleDataObject($key, App_Core_Model_Data_Entity $entity)
    {
        if(is_array($this->_dataObjects[$key])) {
            $this->getData()->set(
                $key,
                array_merge($this->getData()->get($key), array($entity->getData('id')))
            );
            $this->_dataObjects[$key][] = $entity;
        } else {
            $this->getData()->set($key, $entity->getData('id'));
            $this->_dataObjects[$key] = $entity;
        }

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function _getDataObject($key)
    {
        if(null === $this->_dataObjects[$key]) {
            // Пробуем получить данные путем создания объектов через данные self::getData()
            // Используется возможность отложенной загрузки Lazy Load
            // Замена нотации ключа в массиве на нотацию функции, например: user_owner = setUserOwner();
            $method = 'set';
            foreach(explode('_', $key) as $part) {
                $method .= ucfirst($part);
            }

            if(method_exists($this, $method) && $this->getData($key)) {
                $this->{$method}($this->getData($key));
            }
        }

        return $this->_dataObjects[$key];
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