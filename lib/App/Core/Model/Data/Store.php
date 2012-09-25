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
final class App_Core_Model_Data_Store
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
     * Конструктор позволяет инициализировать модель через метод self::set
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        if(is_array($options)){
            if(count($options) > 0) {
                foreach($options as $key => $value) {
                    $this->set($key, $value);
                }
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
        return $this->get($key);
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
        return $this->set($key, $value);
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
     * @return App_Core_Model_Data_Store
     * @throws Exception
     */
    public function set($key, $value)
    {
        if(!is_string($key)){
            throw new Exception("The key must be a string '" . (string)$key . "'. (Неверный формат. Ключ должен быть строкой)");
        }

        switch(strtolower($key)){
            case 'id':
                $this->setId($value);
                break;
            case 'data':
                $this->_data = (array)$value;
                break;
            default:
                $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @param bool $recursive
     * @return App_Core_Model_Data_Store
     */
    public function add($key, $value, $recursive = false)
    {
        switch(strtolower($key)){
            case 'data':
                $this->_data = $this->merge($this->_data, (array)$value, $recursive);
                break;
            default:
                if(!array_key_exists($key, $this->_data)) {
                    $this->_data[$key] = $value;
                } else {
                    $this->_data[$key] = $this->merge((array)$this->_data[$key], (array)$value, $recursive);
                }
        }
        return $this;
    }

    /**
     * Слияние
     * @param array $array
     * @param array $value
     * @param bool $recursive
     * @return array
     */
    public function merge(array $array, array $value, $recursive = false)
    {
        if(true === $recursive) {
            return array_merge_recursive($array, $value);
        }
        return array_merge($array, $value);
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
    public function setId($id)
    {
        if(is_null($this->getId())) {
            $this->_id = $id;
        } else {
            throw new Exception("ID '" . $id . "' is exist. (Идентификатор уже задан)");
        }
    }

    /**
     * ru: Очистить данные
     * @return array
     */
    public function clear()
    {
        $this->_id = null;
        $this->_data = array();
        return $this;
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
}