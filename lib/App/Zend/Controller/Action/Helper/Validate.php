<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * TODO: http://framework.zend.com/manual/1.12/en/zend.filter.input.html
 */
class App_Zend_Controller_Action_Helper_Validate extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var null
     */
    private $_namespace = null;

    /**
     * @var array
     */
    private $_messages = array();

    /**
     * Фильтры в формате: 'key' => filters
      * @var array
     */
    private $_filters = array();

    /**
     * Валидаторы в формате: 'key' => validators
     * @var array
     */
    private $_validators = array();

    /**
     * Пространство имен
     * @param $namespace
     */
    public function __construct($namespace)
    {
        $this->_namespace = $namespace;
        $this->process();
    }

    /**
     * TODO: Лишнее
     * Проверить валидность данных
     * @deprecated
     * @param array $values
     * @return bool
     */
    public function isValid(array $values)
    {
        $validateConfig = Zend_Registry::get('validate');
        if($validateConfig->{$this->_namespace} instanceof Zend_Config){
            $processing = true;
            foreach($values as $key => $value){
                if($validateConfig->{$this->_namespace}->{$key} instanceof Zend_Config) {
                    $validatorChain = new Zend_Validate();
                    $validators = $validateConfig->{$this->_namespace}->{$key}->options->validators;
                    foreach($validators->toArray() as $options){
                        $class = 'Zend_Validate_' . $options['validator'];
                        if(!empty($options['options'])){
                            $validatorChain->addValidator(new $class($options['options']), true);
                        }
                    }
                    if(!$validatorChain->isValid($value)){
                        $this->_messages[$key] = $validatorChain->getMessages();
                        $processing = false;
                    }
                }
            }
            return $processing;
        }
        return false;
    }

    /**
     * Распарсить ini настройки
     * @return App_Zend_Controller_Action_Helper_Validate
     */
    public function process()
    {
        $validateConfig = Zend_Registry::get('validate');
        if($validateConfig->{$this->_namespace} instanceof Zend_Config){
            foreach($validateConfig->{$this->_namespace}->toArray() as $name => $options) {
                if(empty($options['options'])){
                    $this->_validators[$name] = array();
                    $this->_filters[$name] = array();
                } else {
                    $this->_validators[$name] = array();
                    foreach(array_keys($options['options']) as $key) {
                        if($key == 'validators') {
                            $this->_validators[$name] = array_merge($this->_validators[$name], $this->_getValidatorsChain($options['options'][$key]));
                        } elseif($key == 'filters') {
                            $this->_filters[$name] = $this->_getFiltersChain($options['options'][$key]);
                        } else {
                            $this->_validators[$name] = array_merge($this->_validators[$name], array($key => $options['options'][$key]));
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Получить цепочку валидаторов
     * @param array $validators
     * @return array
     */
    protected function _getValidatorsChain(array $validators)
    {
        $validatorsChain = array();
        foreach($validators as $validator => $params) {
            $options = array();
            if(array_key_exists('options', (array)$params)) {
                $options = $params['options'];
            }
            $validatorsChain[] = array($validator, $options);
        }
        return $validatorsChain;
    }


    /**
     * Получить цепочку фильтров
     * @param array $filters
     * @return array
     */
    public function _getFiltersChain(array $filters)
    {
        $filtersChain = array();
        foreach($filters as $filter) {
            $class = 'Zend_Filter_' . $filter['filter'];
            if(!empty($filter['options'])) {
                $filtersChain[] = new $class($filter['options']);
            } else {
                $filtersChain[] = new $class();
            }
        }
        return $filtersChain;
    }

    /**
     * Получить валидаторы
     * @param null|string $key
     * @return array
     */
    public function getValidators($key = null)
    {
        if(is_string($key)) {
            return $this->_validators[$key];
        }
        return $this->_validators;
    }

    /**
     * Получить фильтры
     * @param null|string $key
     * @return array
     */
    public function getFilters($key = null)
    {
        if(is_string($key)) {
            return $this->_filters[$key];
        }
        return $this->_filters;
    }

    /**
     * TODO: Лишнее
     * Получить сообщения об ошибках
     * @deprecated
     * @param bool $belongTo
     * @return array
     */
    public function getMessages($belongTo = false)
    {
        if(true == $belongTo) {
            $messagesBelongTo = array();
            foreach($this->_messages as $key => $message) {
                $belongTo = $this->_namespace . '[' . $key . ']';
                $messagesBelongTo[$belongTo] = $message;
            }
            return $messagesBelongTo;
        } else {
            return $this->_messages;
        }
    }
}
