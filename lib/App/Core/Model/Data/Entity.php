<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * TODO: Дабавить валидацию данных Zend_Validate?
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
     * Публичные свойства сущности, доступные через методы self::getProperty и self::setProperty
     * Как правило здесь хранятся восстановленные объекты-сущности
     * @var array
     */
    private $_properties = array();

    /**
     * TODO:
     * Данные сущности в виде Объектов
     * @deprecated use self::_dataObjects
     * @var array
     */
    private $_dataInstances = array();

    /**
     * Сопутствующие ссылки на другие объекты
     * @deprecated
     * @var array
     */
    private $_dataObjects = array();

    /**
     * @deprecated
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
     * @deprecated use self::setProperty
     * @param $key
     * @param $entry
     */
    protected function _setDataObject($key, $entry)
    {
        $this->_dataObjects[$key] = $entry;
    }

    /**
     * Good
     * Получить экземпляр связанной сущности из Хранилища объектов
     * @deprecated
     * @param string $key
     * @return mixed
     */
    protected function _getDataObject($key)
    {
        if(!array_key_exists($key, $this->_dataObjects)) {
            $this->_dataObjects[$key] = null;
            // Пробуем получить данные путем создания объектов через данные self::getData()
            // Используется возможность отложенной загрузки Lazy Load
            // Замена нотации ключа в массиве на нотацию функции, например: user_owner = setUserOwner();
            $method = 'set';
            foreach(explode('_', $key) as $part) {
                $method .= ucfirst($part);
            }

            if(method_exists($this, $method) && $this->getData()->has($key)) {
                $this->{$method}($this->getData($key));
            }
        }

        return $this->_dataObjects[$key];
    }

    /**
     * TODO: Будующая реализация! В разработке
     * Внимание! Назначенные через данный метод свойства являются публичными
     * и доступны через метод self::getProperty
     * @param $key
     * @param $value
     */
    public function setProperty($key, $value)
    {
        $this->_properties[$key] = $value;
    }

    /**
     * TODO: Будующая реализация! В разработке
     * Получить свойство сущности
     * @param $key
     * @return mixed
     */
    public function getProperty($key)
    {
        if(!array_key_exists($key, $this->_properties)) {
            $this->_properties[$key] = null;
        }

        return $this->_properties[$key];
    }

    /**
     * Проверка существования свойства
     * @param string $key
     * @return bool
     */
    public function hasProperty($key)
    {
        return array_key_exists($key, $this->_properties);
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
                        $this->getData()->setDirty(false);
                        return true;
                    }
                }
            } else {
                // Добавление записи
                $result = $this->_insert();
                if(is_int($result) && $result > 0) {
                    $this->getData()->set('id', $result);
                    $this->getData()->setDirty(false);
                    return true;
                }
            }
            return false;
        }

        throw new Exception('Отсутствуют данные для сохранения');
    }

    /**
     * TODO: Возможно стоит использовать данный метод вместо фабрик!!!!
     * Аналог Factory::restore
     */
    public function load(){}

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