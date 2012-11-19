<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 19.11.12
 */
/**
 * ru:
 */
class Account_InviteController extends App_Zend_Controller_Action
{
    /**
     * Активация
     */
    public function activateAction()
    {
        $request = $this->getRequest();
        if(array_key_exists('g', $request->getParams())){

            $guestColl = new HM_Model_Account_Guest_Collection();
            $guestColl->addEqualFilter('hashActivation', $request->getParam('g'))
                ->getCollection();

            if(count($guestColl->getIdsIterator()) > 0){

                $form = new Zend_Form();
                $form->addElement('text', 'login')->addElement('password', 'password');

                if($request->isPost()) {
                    $form->getElement('login')
                        ->setRequired(true)
                        ->addValidator(new Zend_Validate_Regex('/^[0-9a-z_-]{1,50}$/iu'));

                    $form->getElement('password')
                        ->setRequired(true)
                        ->addValidator(new Zend_Validate_Regex('/^[0-9a-z_-]{1,50}$/iu'));

                    if($form->isValid($request->getPost('account'))){
                        // Проверка логина
                        $userColl = new HM_Model_Account_User_Collection();
                        $userColl->addEqualFilter('login', $form->getValue('login'))
                            ->getCollection();

                        if(count($userColl->getIdsIterator()) == 0){
                            $guest = current($guestColl->getObjectsIterator());
                            // Зарегистрировать пользователя
                            if($guest->activate($form->getValue('login'), $form->getValue('password'))){

                                // Оповещаем наблюдателей
                                // TODO: Доработать
                                $events = Zend_Registry::get('events');
                                $events['account_activate_guest']->setGuest($guest)->notify();
                                $guest->getData()->setRemoved(true);

                                if($guest->save()){
                                    $this->_redirect($this->view->baseUrl('account/access/login'));
                                }
                            }
                            // Во время работы произошла ошибка
                            $error = 'Во время работы произошла ошибка';
                        } else {
                            // Аккаунт уже существует
                            $error = 'Аккаунт уже существует';
                        }
                    }
                }
                $this->view->assign('form', $form);
            } else {
                // Невенный код приглашения
                $error = 'Невенный код приглашения';
            }
        } else {
            // Отсутствует приглашение
            $error = 'Отсутствует приглашение';
        }

        if(isset($error)){
            $this->view->assign('error', $error);
        }

    }
}
