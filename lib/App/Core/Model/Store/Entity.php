<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * TODO: Дабавить валидацию данных Zend_Validate?
 * ru: Абстрактный класс-контейнер для сущностей
 */
class App_Core_Model_Store_Entity extends App_Core_Model_ModelAbstract
{
    const DATA_STORE_NOT_FOUND = 'Данные не найдены';

    /**
     * Данные сущности - объект-значение
     * Здесь хранятся только данные простых типов: int, string, bool, array
     * @var null|App_Core_Model_Store_Data
     */
    private $_data = null;

    /**
     * Публичные свойства сущности, доступные через методы self::getProperty и self::setProperty
     * Как правило здесь хранятся восстановленные объекты-сущности
     * @var array
     */
    private $_properties = array();

    /**
     * TODO: Доработка и тестирование
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
     * TODO: Доработка и тестирование
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
     * @return App_Core_Model_Store_Data|mixed|null
     */
    public function getData($key = null)
    {
        if(!$this->_data instanceof App_Core_Model_Store_Data) {
            $this->_data = new App_Core_Model_Store_Data();
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
        if($this->_data instanceof App_Core_Model_Store_Data) {
            foreach($options as $key => $value) {
                if(is_string($key) || is_int($key)) {
                    $this->getData()->set($key, $value);
                }
            }
        } else {
            $this->_data = new App_Core_Model_Store_Data($options);
        }
    }

    /**
     * Сохранить объект-сущность.
     * Проверка по вставке сущности в БД осуществляется косвенно по наличию getId();
     * Работаем только с Identity данными
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if($this->getData() instanceof App_Core_Model_Store_Data) {
            if($this->isIdentity()){
                if($this->getData()->isRemoved()) {
                    // Удаление записи (только для Identity-сущности)
                    if($this->_remove() > 0) {
                        return true;
                    }
                } elseif($this->getData()->isDirty()) {
                    // Обновление записи (только для Identity-сущности)
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
     * TODO: Необходимо использовать данный метод вместо фабрик!!!!
     * Аналог Factory::restore
     * Использование позднего статического связывания для реализации метода наследуемыми потомками
     */
    public static function load()
    {
        return null;
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