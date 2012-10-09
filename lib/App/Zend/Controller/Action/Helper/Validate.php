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
     * Пространство имен
     * @param $namespace
     */
    public function __construct($namespace)
    {
        $this->_namespace = $namespace;
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
