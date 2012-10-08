<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Абстрактный класс по работе с коллекциями
 * Реализация расширяющих и сужающих фильтров
 * В данной реализации работает сужающая модель наподобие фильтров MS_Excel 2007
 */
abstract class App_Core_Model_CollectionAbstract
{
	/**
     * @var array App_Core_Model_Data_Entity
     */
    private $_objectsCollection = array();

    /**
     * @var array|null IDs
     */
    private $_idsCollection = null;

    /**
     * @var App_Core_Model_FactoryAbstract|null
     */
    private $_factory = null;

    /**
     * ru: Список ресурсов
     * @var array
     */
    protected $_resources = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_init();
    }

    /**
     * ru: Пользовательская инициализация
     */
    protected function _init()
    {

    }

    /**
     * Загрузка коллекции идентификаторов сущностей
     * При каждом обращении происходит перезагрузка коллекции
     * @return App_Core_Model_CollectionAbstract
     */
    public function getCollection()
    {
        $this->clear();
        $this->_idsCollection = $this->_doCollection();
        return $this;
    }

    /**
     * Делегирование полномочий по созданию коллекции идентификаторов сущностей
     * для дальнейшей обработки и загрузки коллекции сущностей
     * @return array
     */
    abstract protected function _doCollection();

    /**
     * @return array|null
     */
    public function getIdsIterator()
    {
        if(!is_array($this->_idsCollection)) {
            $this->_idsCollection = array();
        }
        return $this->_idsCollection;
    }

    /**
     * @param int $id
     * @return App_Core_Model_Data_Entity|null
     */
    public function load($id)
    {
        if(array_key_exists($id, $this->_objectsCollection)) {
            return $this->_objectsCollection[$id];
        } else {
            $object = $this->getFactory()->restore($id);
            if($object instanceof App_Core_Model_Data_Entity) {
                if(!in_array($id, $this->getIdsIterator())){
                    $this->_idsCollection[] = $id;
                }
                $this->_objectsCollection[$id] = $object;
                return $this->_objectsCollection[$id];
            }
        }

        return null;
    }

    /**
     * TODO: Возвращать массив!
     * @return ArrayObject
     */
    public function getObjectsIterator()
    {
        if(count($this->getIdsIterator()) > 0) {
            foreach($this->getIdsIterator() as $id) {
                $this->load($id);
            }
        }
        return new ArrayObject($this->_objectsCollection);
    }

    /**
     * TODO: Возвращать массив!
     * @return ArrayObject App_Core_Model_Data_Store
     */
    public function getDataIterator()
    {
        $data = array();
        $objects = $this->getObjectsIterator();
        if(count($objects) > 0) {
            foreach($objects as $object) {
                $data[$object->getData()->getId()] = clone($object->getData());
            }
        }
        return new ArrayObject($data);
    }

    /**
     * ru: Очистить коллекцию
     * @return App_Core_Model_CollectionAbstract
     */
    public function clear()
    {
        $this->_idsCollection = null;
        $this->_objectsCollection = array();
        return $this;
    }

    /**
     * ru: Преобразовать коллекцию в массив
     * Объекты-сущности должны реализовывать метод toArray
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach($this->getDataIterator() as $data) {
            array_push($array, $data->toArray());
        }
        return $array;
    }

    /**
     * Получить фабрику
     * @return App_Core_Model_FactoryAbstract|null
     * @throws Exception
     */
    public function getFactory()
    {
        if($this->_factory instanceof App_Core_Model_FactoryAbstract) {
            return $this->_factory;
        }
        throw new Exception("Factory not assigned (Фабрика не назначена)");
    }

    /**
     * ru: Назначить фабрику
     * @param App_Core_Model_FactoryAbstract $factory
     */
    public function setFactory(App_Core_Model_FactoryAbstract $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * Добавить ресурс.
     * @param App_Core_Resource_Abstract $resource
     * @param string $name
     * @throws Exception
     */
    public function addResource(App_Core_Resource_Abstract $resource, $name)
    {
        if(is_string($name)) {
            if(array_key_exists($name, $this->_resources)) {
                throw new Exception('Resource named "' . $name . '" has initiated a class "' . get_class($resource) . '" (Ресурс уже инициирован ранее)');
            }
            $this->_resources[$name] = $resource;
        } else {
            throw new Exception('Incorrect name "' . $name . '"');
        }
    }

    /**
     * ru: Вернуть ресурс
     * @param string $name
     * @return App_Core_Resource_Abstract
     * @throws Exception
     */
    public function getResource($name)
    {
        if(is_string($name)) {
            if(array_key_exists($name, $this->_resources)) {
                return $this->_resources[$name];
            }
        }
        throw new Exception('Resource named "' . $name . '" is not defined. (Ресурс отсутствует)');
    }
}