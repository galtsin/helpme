<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Загрузочный класс. Предоставляет доступ к объектам системы
 */
class App
{
    /**
     * @var array
     */
    private static $_instances = array();

    private $_resources = array();

    /**
     * ru: Получить экземпляр класса
     * @static
     * @return mixed
     */
    public static function getInstance()
    {
        $calledClass = get_called_class();
        if(false === self::hasInstance()) {
            self::$_instances[$calledClass] = new $calledClass();
        }
        return self::$_instances[$calledClass];
    }


    public static function _getInstance($key, $class)
    {
        if(class_exists($class)) {
            if(!array_key_exists($key, self::$_instances)) {
                self::$_instances[$key] = array();
            }
            if(!array_key_exists($class, self::$_instances[$key])){
                self::$_instances[$key][$class] = new $class();
            }
            return self::$_instances[$key][$class];
        }
        throw new Exception('Class "' . $class . '" is not defined');
    }


    /**
     * ru: Проверка наличия экземпляра класса в Памяти
     * @static
     * @return bool
     */
    public static function hasInstance()
    {
        $calledClass = get_called_class();
        if(!isset(self::$_instances[$calledClass])) {
            return false;
        }
        return true;
    }

    /**
     * Получить фабрику
     * @param string $class
     * @return App_Core_Model_FactoryAbstract
     * @throws Exception
     */
    public static function getFactory($class)
    {
        if(get_parent_class($class) == 'App_Core_Model_FactoryAbstract') {
            return self::_getInstance('factory', $class);
        }
        throw new Exception('Class "' . $class . '" is not Factory instance');
    }

    /**
     * App::getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
     * @param string $class
     * @return mixed
     * @throws Exception
     */
    public static function getResource($class)
    {
        return self::_getInstance('resource', $class);
    }

    public static function _getResource($namespace)
    {

    }

    public function resisterResource($instance, $namespace)
    {
        $this->_resources[$namespace] = $instance;
    }
}