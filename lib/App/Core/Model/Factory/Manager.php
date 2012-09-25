<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Абстрактная Фабрика для создания, инициализации и восстановления объектов из БД
 */
class App_Core_Model_Factory_Manager
{
    /**
     * @var array
     */
    private static $_instances = array();

    /**
     * Получить экземпляр фабрики
     * @static
     * @param $class
     * @return App_Core_Model_FactoryAbstract
     * @throws Exception
     */
    public static function getFactory($class)
    {
        if(class_exists($class) && get_parent_class($class) == 'App_Core_Model_FactoryAbstract') {
            if(!array_key_exists($class, self::$_instances)){
                self::$_instances[$class] = new $class();
            }
            return self::$_instances[$class];
        }
        throw new Exception('Class "' . $class . '" is not defined');
    }
}