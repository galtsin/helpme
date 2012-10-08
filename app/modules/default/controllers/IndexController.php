<?php

class Default_IndexController extends App_Zend_Controller_Action
{
    public function indexAction()
    {
        $acl = new Zend_Acl();
        $acl->addRole('guest');
        $acl->addRole('user', 'guest');
        $acl->addResource('page');
        $acl->allow('guest', 'page', 'read');
        $acl->allow('user', 'page', 'write');
        Zend_Debug::dump($acl->isAllowed('guest', 'page', 'write'));


        $line = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Group_Factory')->restore(12);
        Zend_Debug::dump($line->getExperts());
    }
}