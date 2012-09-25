<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Абстрактная Фабрика для создания, инициализации и восстановления объектов из БД
 * Фабрика должна генерировать сущности типа App_Core_Model_Entity
 */
abstract class App_Core_Model_FactoryAbstract
{
    /**
     * ru: Список ресурсов
     * @var array
     */
    protected $_resources = array();

    /**
     * Общий для Фабрик конструктор
     */
    public function __construct()
    {
        $this->_init();
    }

    /**
     * Инициализация фабрики
     */
    protected function _init()
    {

    }

    /**
     * Восстановление объекта из БД
     * @param $id
     * @return App_Core_Model_Data_Entity
     */
    abstract public function restore($id);

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