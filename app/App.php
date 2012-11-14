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
     * Список ресурсов
     * @var array
     */
    private static $_resources = array(
        App_Core_Resource_DbApi::RESOURCE_NAMESPACE => 'App_Core_Resource_DbApi'
    );

    /**
     * App::getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
     * @param string $class
     * @return mixed
     * @throws Exception
     */

    /**
     *
     * @param $namespace
     */
    public static function getResource($namespace)
    {
        return self::$_resources[$namespace];
    }

    /**
     * @param $namespace
     * @param $instance
     */
    public static function registerResource($namespace, $instance)
    {
        self::$_resources[$namespace] = $instance;
    }

    /**
     * Зарегистрировать пространство имен, через которое к нему можно обращаться
     * App::getDefaultNamespace()
     * @param $namespace
     */
    public static function registerNamespace($namespace)
    {

    }

    /**
     * Пространство имен по умолчанию
     * @param string $namespace
     */
    public static function getNamespace($namespace = 'default')
    {
        return '';
    }

    public function __call($method, $params)
    {
        Zend_Debug::dump($method);
    }
}