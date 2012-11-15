<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Предоставляет доступ к объектам системы
 */
class App
{
    /**
     * Список ресурсов
     * @var array
     */
    private static $_resources = array();

    /**
     * @param string $namespace
     * @return mixed
     */
    public static function getResource($namespace)
    {
        return self::$_resources[$namespace];
    }

    /**
     * @param string $namespace
     * @param mixed $instance
     */
    public static function registerResource($namespace, $instance)
    {
        self::$_resources[$namespace] = $instance;
    }
}