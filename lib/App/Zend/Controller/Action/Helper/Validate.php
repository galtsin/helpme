<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 09.10.12
 */
/**
 * ru:
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
     * Проверить валидность данных
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
                if(!empty($options['options']['validators'])) {
                    $this->_validators[$name] = $this->_getValidatorsChain($options['options']['validators']);
                } else{
                    $this->_validators[$name] = array();
                }
                if(!empty($options['options']['filters'])) {
                    $this->_filters[$name] = $this->_getFiltersChain($options['options']['filters']);
                } else {
                    $this->_filters[$name] = array();
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
        foreach($validators as $validator) {
            $class = 'Zend_Validate_' . $validator['validator'];
            $validatorsChain[] = new $class($validator['options']);
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
     * Получить сообщения об ошибках
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
