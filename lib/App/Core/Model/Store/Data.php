<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Универсальный класс Объект-Значение
 * Позволяет хранить и структурировать информацию без ее дальнейшей обработки
 * Объект имеет зарезервированные переменные
 * "id" — идентификатор объекта
 * "data" — данные объекта
 */
final class App_Core_Model_Store_Data
{
    /**
    * en: Unique identifier of entity
    * ru: Уникальный идентификатор данных
    * @var null|string|int
    */
    private $_id = null;

    /**
    * en: Data of this model
    * ru: Данные Объекта-Значения
    * @var array
    */
    protected $_data = array();

    /**
     * Флаг изменения данных объекта
     * "true" — объект изменен; "false" — объект
     * Применяется для записи в БД, только измененых объектов
     * @var bool
     */
    private $_dirty = false;

    /**
     * TODO: Может по умолчанию false?
     * Разрешение на запись объекта
     * @var bool
     */
    private $_writable = true;

    /**
     * Пометка на удаление объекта
     * @var bool
     */
    private $_removed = false;

    /**
     * Конструктор позволяет инициализировать модель через метод self::set
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        if(is_array($options)){
            if(count($options) > 0) {
                foreach($options as $key => $value) {
                    if(method_exists($this, 'set' . ucfirst(trim($key)))) {
                        $method = 'set' . ucfirst($key);
                        $this->{$method}($value);
                    } else {
                        $this->set($key, $value);
                    }
                }
                // Считаем, что инициализация объекта - это не изменение объекта, а его наполнение
                $this->setDirty(false);
            }
        }
    }

    /**
    * en: Overwriting the magic method __get
    * ru: Доступ к Данным модели как к переменным. Например, $entity->info;
    * @param int|string $key
    * @return mixed
    */
    public function __get($key)
    {
        return $this->get(lcfirst($key));
    }

    /**
     * en: Overwriting the magic method __set
     * ru: Доступ к Данным модели как к переменным. Например, $entity->info;
     * @param int|string $key
     * @param mixed $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        return $this->set(lcfirst($key), $value);
    }

    /**
     * Позволяет полкчить доступ к данным модели через соответствующие методы
     * Пример: self::_data['name'] = 'andrey'; соответственно self::getName() и self::setName('alex');
     * @param $method
     * @param $arg
     * @return App_Core_Model_Store_Data|array|int
     * @throws Exception
     */
    public function __call($method, $arg)
    {
        $_sg = substr($method, 0, 3);

        if($_sg === 'get') {
            return $this->get(lcfirst(substr($method, 3)));
        } elseif ($_sg == 'set') {
            return $this->set(lcfirst(substr($method, 3)), array_shift($arg));
        }

        throw new Exception('Метод ' . $method . ' не найден');
    }

    /**
     * @param string $key
     * @return array|int
     * @throws Exception
     */
    public function get($key)
    {
        if(!is_string($key)) {
            throw new Exception("The key must be a string '" . (string)$key . "'. (Неверный формат. Ключ должен быть строкой)");
        }
        switch(strtolower($key)){
            case 'id':
                return $this->getId();
            case 'data':
                return $this->_data;
            default:
                if(array_key_exists($key, $this->_data)) {
                    return $this->_data[$key];
                }
        }
        throw new Exception("Key '" . (string)$key . "' does not exist. (Ключ не существует)");
    }

    /**
     * @param $key
     * @param $value
     * @return App_Core_Model_Store_Data
     * @throws Exception
     */
    public function set($key, $value)
    {
        if(!is_string($key)){
            throw new Exception("The key must be a string '" . (string)$key . "'. (Неверный формат. Ключ должен быть строкой)");
        }

        if($this->isWritable()) {
            switch(strtolower($key)){
                case 'id':
                    $this->_setId($value);
                    break;
                case 'data':
                    $this->_data = (array)$value;
                    break;
                default:
                    $this->_data[$key] = $value;
            }

            // Пометить объект как измененый
            $this->setDirty(true);
        }

        return $this;
    }

    /**
     * ru: Вернуть уникальный идентификатор сущности
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Установить идентификатор и запереть его
     * @param mixed $id
     * @throws Exception
     */
    private function _setId($id)
    {
        if(is_null($this->getId())) {
            $this->_id = $id;
        } else {
            throw new Exception("ID '" . $id . "' is exist. (Идентификатор уже задан)");
        }
    }

    /**
     * Проверка на изменения в объекте
     * "true" — объект изменен; "false" — объект
     * @return bool
     */
    public function isDirty()
    {
        return $this->_dirty;
    }

    /**
     * ПОметка объекта на изменение
     * @param bool $flag
     * @return App_Core_Model_Store_Data
     */
    public function setDirty($flag)
    {
        if(is_bool($flag)) {
            $this->_dirty = $flag;
        }

        return $this;
    }

    /**
     * Проверить записываемый объект или нет
     * @return bool
     */
    public function isWritable()
    {
        return $this->_writable;
    }

    /**
     * Установить флаг записи
     * @param bool $flag
     * @return App_Core_Model_Store_Data
     */
    public function setWritable($flag)
    {
        if(is_bool($flag)) {
            $this->_writable = $flag;
        }

        return $this;
    }

    /**
     * Установить флаг на удаление
     * @return bool
     */
    public function isRemoved()
    {
        return $this->_removed;
    }

    /**
     * Установить флаг на удаление объекта
     * @param $flag
     * @return App_Core_Model_Store_Data
     */
    public function setRemoved($flag)
    {
        if(is_bool($flag)) {
            $this->_removed = $flag;
        }

        return $this;
    }

    /**
     * ru: Очистить данные
     * @return array
     */
    public function clear()
    {
        $this->_id = null;
        $this->_data = array();
        $this->setDirty(false);

        return $this;
    }


    /**
     * Проверить наличие ключа в индексе данных data
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_data);
    }

    /**
     * ru: Преобразование объекта в массив
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->get('id'),
            'data' => $this->get('data')
        );
    }

    /**
     * Преобразовать объект в JSON данные
     * @return string
     */
    public function toJson()
    {
        return Zend_Json::encode($this->toArray());
    }
}