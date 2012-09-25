<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Работа с Api базы данных через ее функции
 */
class App_Core_Resource_DbApi extends App_Core_Resource_Abstract
{
    /**
     * Пространство имен ресурса
     */
    const RESOURCE_NAMESPACE = 'postgres_api';

    /**
     * Соединение с БД
     * @var null|Zend_Db_Adapter_Abstract
     */
    protected $_dbConnect = null;

    /**
     * Список функций в Api
     * @var Zend_Config_Xml|null
     */
    protected $_functions = null;

    /*
     * Инициализация
     */
    public function __construct()
    {
        $this->_dbConnect = Zend_Db_Table::getDefaultAdapter();
        $this->_functions = Zend_Registry::get(App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @static
     * @param string $type
     * @return int
     * @throws Exception
     */
    static public function getStatamentType($type)
    {
        if(is_string($type)) {
            switch(strtolower($type)) {
                case 'string':
                    return PDO::PARAM_STR;
                case 'integer':
                    return PDO::PARAM_INT;
                case 'bool':
                    return PDO::PARAM_BOOL;
                default:
                    return PDO::PARAM_STR;
            }
        }
        throw new Exception("Statament type " . $type . " is not defined");
    }

    /**
     * Выполнить функцию
     * @param string $functionName
     * @param array|null $params
     * @return App_Core_Resource_DbApi_Result
     * @throws App_Core_Resource_Exception
     */
    public function execute($functionName, array $params = null)
    {
        try {
            $sql = $this->_prepareFunction($functionName);
            $stmt = $this->_dbConnect->prepare($sql);

            // У функции присутствуют параметры
            if($this->_functions->get($functionName) instanceof Zend_Config) {
                $functionParams = $this->_functions->get($functionName)->toArray();
                foreach($functionParams as $key => $value) {
                    if(!array_key_exists($key, $params)) {
                        $params[$key] = null;
                    }
                    $stmt->bindParam(":" . $key, $params[$key], self::getStatamentType($value['type']));
                }
            }

            $stmt->execute();
            return new App_Core_Resource_DbApi_Result($stmt);

        } catch(Exception $ex) {
            throw new App_Core_Resource_Exception($ex->getMessage());
        }
    }

    /**
     * Создание основного SQL запроса
     * @param string $functionName
     * @return string
     * @throws App_Core_Resource_Exception
     */
    protected function _prepareFunction($functionName)
    {
        if(is_null($this->_functions->get((string)$functionName))) {
            throw new App_Core_Resource_Exception('Function ' . $functionName . ' don`t exists (Функция ' . $functionName . ' не найдена)');
        }

        $sql = "SELECT * FROM ";
        $sql .= Zend_Registry::get('configs')->resources->db->general->scheme . "." . $functionName;
        $sql .= $this->_prepareFunctionParams($this->_functions->get($functionName)) . ';';

        return $sql;
    }

    /**
     * Преобразование параметров функции в именованные SQL переменные
     * Если у функции нет параметров - вернется пустая строка '()'
     * @param Zend_Config|array $function
     * @return string
     */
    protected function _prepareFunctionParams($function)
    {
        $bindParams = array();
        if($function instanceof Zend_Config) {
            foreach($function->toArray() as $param => $attr) {
                $bindParams[] = ':' . $param;
            }
        }
        return '(' . implode(',', $bindParams) . ')';
    }
}