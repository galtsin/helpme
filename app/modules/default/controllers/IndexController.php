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
    }
}