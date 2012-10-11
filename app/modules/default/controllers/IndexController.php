<?php

class Default_IndexController extends App_Zend_Controller_Action
{
    public function indexAction()
    {
/*        $acl = new Zend_Acl();
        $acl->addRole('guest');
        $acl->addRole('user', 'guest');
        $acl->addResource('page');
        $acl->allow('guest', 'page', 'read');
        $acl->allow('user', 'page', 'write');
        Zend_Debug::dump($acl->isAllowed('user', 'page', 'write'));

        $validatorChain = new Zend_Validate();
        $validatorChain->addValidator(new Zend_Validate_StringLength(6, 12), true)
            ->addValidator(new Zend_Validate_Alnum(), true);
        Zend_Debug::dump($validatorChain->isValid('Hello4_'));*/


        $validateConfig = Zend_Registry::get('validate');

/*        $messages = array();
        foreach($validateConfig->level->elements->toArray() as $key => $options) {
            $validatorChain = new Zend_Validate();
            $validators = $validateConfig->level->elements->{$key}->options->validators;
            foreach($validators->toArray() as $validateOptions) {
                $class = 'Zend_Validate_' . $validateOptions['validator'];
                if(!empty($validateOptions['options'])){
                    $validatorChain->addValidator(new $class($validateOptions['options']), true);
                }
            }
            if(false === $validatorChain->isValid("Привет Миро @4")){
                $messages[] = $validatorChain->getMessages();
            }
        }*/

        //Zend_Debug::dump($this->isValidChain('level', 'name', "Привет Миро 4"));
        //Zend_Debug::dump($this->isValidChain('level', 'name', "Привет Миро @4"));



        $k = new App_Zend_Controller_Action_Helper_Validate('account');
        $f = new Zend_Filter_Input(array('*' => 'StringTrim'), array('name' => 'Alnum'));
        $f->setData(array('login' => ' dfgo'));
        Zend_Debug::dump($f->isValid());
        Zend_Debug::dump($f->getEscaped('login'));
        Zend_Debug::dump($f->getMessages());
    }

    public function isValid(array $values)
    {

    }

    public function isValidChain($element, $key, $value)
    {
        $validateConfig = Zend_Registry::get('validate');
        if($validateConfig->{$element}->{$key} instanceof Zend_Config) {
            $validatorChain = new Zend_Validate();
            $validators = $validateConfig->{$element}->{$key}->options->validators;
            foreach($validators->toArray() as $options){
                $class = 'Zend_Validate_' . $options['validator'];
                if(!empty($options['options'])){
                    $validatorChain->addValidator(new $class($options['options']), true);
                }
            }
            return $validatorChain->isValid($value);
        }
        return false;
    }

}