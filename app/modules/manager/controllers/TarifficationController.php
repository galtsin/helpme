<?php

class Manager_TarifficationController extends App_Zend_Controller_Action
{
    /**
     * @var Zend_Acl
     */
    private $acl;

    public function indexAction()
    {
/*        HM_Model_Account_Auth::getInstance()->getAccount();
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore(4);
        $role = HM_Model_Account_Access::getInstance()->getRole('MAGR');*/

/*        $b = new HM_Model_Account_Access_Collection();
        $b->addEqualFilter('id', 12);
        $b->addEqualFilter('id', 13);
        $b->setAccessFilter($user, $role, 15);
        $b->setObjectType('LINE');
        $b->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory'));
        Zend_Debug::dump($b->getCollection()->getDataIterator());*/

        //Zend_Debug::dump($this->getChildrenRoles(HM_Model_Account_Access::getInstance()->getRole(4)));

        $access = HM_Model_Account_Access::getInstance();
        $point =  microtime(true);
        $access->linkChainRoles($access->getRole(14));
        $access->getAcl()->addResource('level');
        $access->getAcl()->allow('ADM_LINE', 'level');
        $point = microtime(true) - $point;
        echo "Runtime: " . $point;

        Zend_Debug::dump($access->getAcl()->isAllowed('ADM_TARIFF', 'level'));

        Zend_Debug::dump((int)null);


    }

    public function getInheritsRoles(App_Core_Model_Data_Store $role)
    {
        $parents = array();
        foreach(HM_Model_Account_Access::getInstance()->getRoles() as $_role) {
            if(is_int($_role->get('pid')) && $_role->get('pid') === $role->get('id')){
                array_push($parents, $_role);
            }
        }
        return $parents;
    }

    public function linkRoles($role)
    {
        if(!$this->acl->hasRole($role->get('code'))) {
            $parents = $this->getInheritsRoles($role);
            if(count($parents) > 0){
                $codes = array();
                foreach($parents as $parent){
                    $this->linkRoles($parent);
                    $codes[] = $parent->get('code');
                }
                $this->acl->addRole($role->get('code'), $codes);
            } else {
                $this->acl->addRole($role->get('code'));
            }
        }
    }
}