<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Контроллер Авторизации и Аутендефикации пользователей
 */
class Account_AccessController extends App_Zend_Controller_Action
{
    /*
     * final
     * ru: Авторизация пользователя
     * TODO: Использовать MD5 шифрование для паролей
     */
    public function loginAction()
    {
        if(true == HM_Model_Account_Auth::getInstance()->isAuth()) {
            $this->_redirect($this->view->baseUrl('account/access/possibility'));
        }

        $forms = Zend_Registry::get('forms');
        $form = new App_Zend_Form($forms->account->options);
        $form->getElement('login')->setRequired(true);
        $form->getElement('password')->setRequired(true);

        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                $auth = HM_Model_Account_Auth::getInstance();
                if($auth->authenticate($form->getValue('login'), $form->getValue('password'))) {
                    $url = 'account/access/possibility';
                    if($this->getRequest()->getParam('ref')) {
                        $url .= '/ref/' . $this->getRequest()->getParam('ref');
                    }
                    $this->_redirect($this->view->baseUrl($url));
                }
            }
        }
    }

    /*
     * final
     * ru: Выход пользователя из системы
     */
    public function logoutAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        if(HM_Model_Account_Auth::getInstance()->isAuth()) {
            HM_Model_Account_Auth::getInstance()->unsetAuth();
        }

        $url = ($this->getRequest()->getParam('ref')) ?
            'account/access/login/ref/' . $this->getRequest()->getParam('ref') :
            'account/access/login';
        $this->_redirect($this->view->baseUrl($url));
    }

    /**
     * Страница закрытого доступа
     */
    public function deniedAction()
    {

    }
}