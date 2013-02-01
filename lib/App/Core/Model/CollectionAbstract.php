<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Абстрактный класс по работе с коллекциями
 * Реализация расширяющих и сужающих фильтров
 * В данной реализации работает сужающая модель наподобие фильтров MS_Excel 2007
 * TODO:!
 * Можно также использовать результаты итераторов для дальнейших преобразований
 * Например: new ArrayObject(self::getCollection()->getObjectsIterator());
 * TODO:!
 * Так же можно передавать коллекции в качестве массивов объектов в вид
 *
 * count(data) = count(objects) = count(ids)
 */
abstract class App_Core_Model_CollectionAbstract
{
	/**
     * Массив объектов
     * @var App_Core_Model_Store_Entity[]
     */
    private $_objectsCollection = array();

    /**
     * Массив идентификаторов
     * @var int[]|null
     */
    private $_idsCollection = array();

    /**
     * Класс-Модель по которой должен восстанавливаться объект в методе load
     * @var string|null
     */
    private $_modelRestore = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_init();
    }

    /**
     * Пользовательская инициализация
     */
    protected function _init()
    {

    }

    /**
     * Установить лимит выводимых элементов
     */
    public function setLimit($limit)
    {

    }

    /**
     * Сделать смещение элементов
     */
    public function setOffset($offset)
    {

    }

    public function setSort()
    {

    }

    /**
     * Загрузка коллекции идентификаторов сущностей
     * При каждом обращении происходит перезагрузка коллекции
     * @return App_Core_Model_CollectionAbstract
     */
    public function getCollection()
    {
        $idsCollection = array_merge($this->_idsCollection, $this->_doCollection());
        $this->_idsCollection = array_unique($idsCollection);
        return $this;
    }

    /**
     * Призвана заменить self::getCollection()
     */
    public function fetch(){}

    /**
     * Делегирование полномочий по созданию коллекции идентификаторов сущностей
     * для дальнейшей обработки и загрузки коллекции сущностей
     * @return array
     */
    abstract protected function _doCollection();

    /**
     * @return int[]|null
     */
    public function getIdsIterator()
    {
        return $this->_idsCollection;
    }

    /**
     * Центральный загрузочный метод
     * @param int $id
     * @return App_Core_Model_Store_Entity|null
     */
    public function load($id)
    {
        if(array_key_exists(/*(int)*/$id, $this->_objectsCollection)) { // TODO: еобходимо ли приводить к integer?
            return $this->_objectsCollection[$id];
        } else {
            $object = forward_static_call_array(array($this->getModelRestore(), 'load'), array($id));
            if($object instanceof App_Core_Model_Store_Entity) {
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
     * @return App_Core_Model_Store_Entity[]
     */
    public function getObjectsIterator()
    {
        if(count($this->getIdsIterator()) > 0) {
            foreach($this->getIdsIterator() as $id) {
                $this->load($id);
            }
        }
        return $this->_objectsCollection;
    }

    /**
     * @return App_Core_Model_Store_Data[]
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
        return $data;
    }

    /**
     * Добавить запись или набор записей в текущую коллекцию
     * @param mixed $set
     * @return static
     */
    public function addToCollection($set)
    {
        if(is_array($set)) {
            foreach($set as $entry) {
                $this->_addToCollection($entry);
            }
        } else {
            $this->_addToCollection($set);
        }

        return $this;
    }

    /**
     * Добавить объекты в коллекцию
     * @param $entry
     * @throws Exception
     */
    private function _addToCollection($entry)
    {
        if(is_int($entry)) {
            if(!in_array($entry, $this->getIdsIterator())) {
                $this->_idsCollection[] = $entry;
            }
            // Проверять тип добавляемой в коллекцию сущности!!!
        } elseif ($entry instanceof App_Core_Model_Store_Entity) {
            if(get_class($entry) == $this->getModelRestore()) {
                if(!array_key_exists($entry->getData()->getId(), $this->getObjectsIterator()) && !in_array($entry->getData()->getId(), $this->getIdsIterator())) {
                    $this->_idsCollection[] = $entry->getData()->getId();
                    $this->_objectsCollection[$entry->getData()->getId()] = $entry;
                }
            } else {
                throw new Exception("Entity is not instance '" . $this->getModelRestore() . "' (Сущность не является экземпляром класса '" . $this->getModelRestore() . "')");
            }
        }
    }

    /**
     * ru: Очистить коллекцию
     * @return App_Core_Model_CollectionAbstract
     */
    public function clear()
    {
        $this->_idsCollection = array();
        $this->_objectsCollection = array();
        return $this;
    }

    /**
     * Преобразовать коллекцию в массив
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
     * Преобразовать коллекцию в Json формат
     * @return string
     */
    public function toJson()
    {
        return Zend_Json::encode($this->toArray());
    }

    /**
     * Назначить модель восстановления
     * @param string $model
     * @return static
     * @throws Exception
     */
    public function setModelRestore($model)
    {
        if(class_exists($model)) {
            if(method_exists($model, 'load')) {
                $this->_modelRestore = $model;
            } else {
                throw new Exception("Модель '" . $model . "' не содержит метод восстановления load");
            }
        } else {
            throw new Exception("Модель '" . $model . "' не существует");
        }

        return $this;
    }

    /**
     * Получить модель восстановления
     * @return null|string
     * @throws Exception
     */
    public function getModelRestore()
    {
        if(null == $this->_modelRestore){
            throw new Exception("Model Restore not assigned (Модель восстановления не назначен)");
        }

        return $this->_modelRestore;
    }
}