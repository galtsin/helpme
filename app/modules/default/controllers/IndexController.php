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




        // TODO: алгоритм получения доступных данных
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user']);

        $accessColl = new HM_Model_Account_Access_Collection();
        $accessColl->setType('LINE')
            ->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory'))
            ->setRestrictionByCompany(12)
            ->setRestrictionByInheritanceFromRole('ADM_COMPANY');

        $accessColl->addEqualFilter('possibility', $user->getPossibilities())->getCollection();
        //Zend_Debug::dump($accessColl->getCollection()->getDataIterator());

        $inviteColl = new HM_Model_Account_Invite_Collection();
        $inviteColl->addEqualFilter('guest', 30)->getCollection();
        Zend_Debug::dump($inviteColl->getDataIterator());





    }

    private function _getHierarchyRoleAndCompany(HM_Model_Account_User $user)
    {
        $access = HM_Model_Account_Access::getInstance();
        $allowedRoles = array();
        foreach($user->getRoles() as $roleIdentifier => $companies) {
            foreach($access->getRoles() as $role){
                if($access->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier == $role->get('code')) {
                    if(!array_key_exists($role->get('code'), $allowedRoles)) {
                        $allowedRoles[$role->get('code')] = $companies;
                    } else {
                        $allowedRoles[$role->get('code')] = array_unique(array_merge($allowedRoles[$role->get('code')], $companies));
                    }
                }
            }
        }
        return $allowedRoles;
    }

    private function _getHierarchyRoles(HM_Model_Account_User $user)
    {
        $access = HM_Model_Account_Access::getInstance();
        $roles = $_index = array();
        // Построить иерархию Ролей
        foreach(array_keys($user->getRoles()) as $roleIdentifier) {
            $roles = array_merge(
                $roles,
                $access->getInheritsRoles($access->getRole($roleIdentifier), true),
                array($access->getRole($roleIdentifier))
            );
        }
        // Исключить повторяющиеся Роли.
        foreach($roles as $key => $role) {
            if(in_array($role->get('id'), $_index)) {
                unset($roles[$key]);
                continue;
            }
            $_index[] = $role->get('id');
        }

        return $roles;
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

class Test extends App_Core_Model_Data_Entity
{
    public function getUser()
    {
        return $this->_getDataObject('user');
    }

    public function setUser($user)
    {
        $this->_setDataObject('user', $user);
        return $this;
    }
}