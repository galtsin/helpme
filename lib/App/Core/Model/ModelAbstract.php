<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Абстрактный класс-контейнер для сущностей
 */
class App_Core_Model_ModelAbstract
{
    /**
     * ru: Список ресурсов
     * @var array
     */
    private $_resources = array();

    /**
     * Конструктор позволяет инициализировать модель через set методы
     * При этом существует возможность выбирать порядок инициализации путем указания позиции параметров в опциях конструктора (за счет числовых ключей массива)
     * Например: array(array('resource' => 'res'), array('factory' => 'fact'));
     * Первым будет инициализирован ресурс, вторым фабрика
     * @param array | null $options
     */
    public function __construct(array $options = null)
    {
        if(is_array($options)){
            if(count($options) > 0) {
                foreach($options as $queue => $option) {
                    if(is_int($queue)) {
                        $setterMethod = 'set' . ucfirst(key($option));
                        if(method_exists($this, $setterMethod)) {
                            $this->{$setterMethod}(current($option));
                        }
                    }
                }
            }
        }
        $this->_init();
    }

    /**
     * Делегирование прав по инициализации сущности потомкам
     */
    protected function _init()
    {

    }

    /**
     * ru: Добавление ресурса
     * @tag final
     * @param mixed $resource
     * @param string $name
     * @throws Exception
     */
    public function addResource(/*App_Core_Resource_Abstract*/ $resource, $name)
    {
        if(is_string($name)) {
            if(array_key_exists($name, $this->_resources)) {
                throw new Exception("Resource \"" . get_class($resource) . "\" is already defined (Ресурс был определен ранее)");
            }
            $this->_resources[$name] = $resource;
        }
    }

    /**
     * ru: Получить ресурс
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
        throw new Exception("Resource " . $name . " is not defined. (Ресурс отсутствует)");
    }
}