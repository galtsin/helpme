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

        $input = new Zend_Filter_Input(array(), $var1);
        $input->setData(array('igor' => '123'));
        //Zend_Debug::dump($input->isValid());
        //Zend_Debug::dump($input->getEscaped('igor'));


        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Узнать по какой роли стоит осуществлять поиск
        // Мы знаем URL-адрес
        $pageRole = $access->getRole('ADM_LINE'); // TODO: Как то нужно узнавать!
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user']);


        $accessColl = new HM_Model_Account_Access_Collection();
        $accessColl->setType('LINE')
            ->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory'));
        $accessColl->setAccessFilter($user, $pageRole, 15)->getCollection();
        $lines = $accessColl->getDataIterator();

        $possibility = current($accessColl->getPossibilities());

        // TODO: если объект находится в possibility, то доступ к нему разрешен!!!

        foreach($lines as $line) {
            $possibility->assignPrivileges(HM_Model_Account_Access::getInstance()->getType('LINE'), $line);
        }

        $a = new App_Core_Model_Data_Store();
        $a->set('test2', 'test2');
        $b = array();
        $b[] = $a->toArray();

        //Zend_Debug::dump(Zend_Json::encode(array('data' => array('count' => 10, 'items' => $b))));



        // 1. Проверить доступ к функции и получить роли
        // 2. Получить список доступных ресурсов Possibility
        // 3. Проставить режимы записи/чтения объектов

        // Определить какой компании принадлежит ресурс - параметр company_owner
        // user + company_owner + currentFunctionRole

/*        $line = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory')
            ->restore($this->getRequest()->getParam('line'));
        if($line instanceof HM_Model_Counseling_Structure_Line) {
            $accessColl = new HM_Model_Account_Access_Collection();
            $accessColl->setType('LINE');
            $accessColl->setAccessFilter($user, $pageRole, $line->getData('company_owner'))->getCollection();
            $possibility = current($accessColl->getPossibilities());
            if($possibility->has($line->getData('id'))){
                echo "Объект доступен пользователю";
            }
            // Расставить режимы на чтение/запись
            $possibility->setPrivileges($line);
        }*/



        $access = HM_Model_Account_Access::getInstance();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);

        Zend_Debug::dump(array_keys($user->getRoles()));
        $allowedRoles = array();
        foreach($user->getRoles() as $roleIdentifier => $companies) {
            foreach($access->getRoles() as $role){
                if($access->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier === $role->get('code')) {
                    if(!array_key_exists($role->get('code'), $allowedRoles)) {
                        $allowedRoles[$role->get('code')] = $companies;
                    }
                }
            }
        }


        $_roles = array();
        foreach(array_keys($user->getRoles()) as $roleIdentifier) {
            $_roles = array_merge(
                $_roles,
                $access->getInheritsRoles($access->getRole($roleIdentifier), true),
                array($access->getRole($roleIdentifier))
            );
        }
        // Исключить повторяющиеся роли.
        $index = array();
        $__roles = $_roles;
        foreach($__roles as $key => $role) {
            if(in_array($role->get('id'), $index)) {
                unset($_roles[$key]);
                continue;
            }
            $index[] = $role->get('id');
        }
        //Zend_Debug::dump($_roles);

        $possibilityColl = new HM_Model_Account_Access_Possibility_Collection();
        Zend_Debug::dump($possibilityColl->getObjectsIterator());

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