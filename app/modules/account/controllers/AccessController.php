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
     * TODO: Zend_Filter_Input
     */
    public function loginAction()
    {
        if(true == HM_Model_Account_Auth::getInstance()->isAuth()) {
            //$this->_redirect($this->view->baseUrl('account/access/possibility'));
            $this->_redirect($this->view->baseUrl());
        }

        $request = $this->getRequest();
        if ($request->isPost()) {

            $validate = new App_Zend_Controller_Action_Helper_Validate('account');
            $filterInput = new Zend_Filter_Input($validate->getFilters(), $validate->getValidators());
            $filterInput->setData($request->getPost('account'));

            // TODO: Внимание! Валидируются только входящие данные. Посторонние данные не описанные в Ini - игнорируются
            if($filterInput->isValid()){
                $auth = HM_Model_Account_Auth::getInstance();
                if($auth->authenticate($filterInput->getEscaped('login'), $filterInput->getEscaped('password'))) {
                    //$url = 'account/access/possibility';
                    $url = '';
                    if($request->getParam('ref')) {
                        $url .= '/ref/' . $request->getParam('ref');
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