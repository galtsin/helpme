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


/*        Zend_Debug::dump(array_merge(array(Zend_Filter_Input::DEFAULT_VALUE => 'dsf'), array(array('Alnum', array('allowWhiteSpace' => true)))));
        Zend_Debug::dump(array(
        Zend_Filter_Input::DEFAULT_VALUE => 'dsf',
        array('Alnum', array('allowWhiteSpace' => true))
    ));*/



        $k = new App_Zend_Controller_Action_Helper_Validate('tariff');
        //$input = new Zend_Filter_Input(array(), $k->getValidators());

        //$input->setData(array('name' => 'sdf s'));
        //Zend_Debug::dump($input->getEscaped('name'));


        $var1 = array('name' => array(array('Alnum', array()),'default' => 'dsf',));
        $var2 = $k->getValidators();

        //Zend_Debug::dump($var1);
        //Zend_Debug::dump($var2);

        $input = new Zend_Filter_Input(array(), $var2);
        $input->setData(array('name' => ''));
        Zend_Debug::dump($input->isValid());
        Zend_Debug::dump($input->getEscaped('active'));
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