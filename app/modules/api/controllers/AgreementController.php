<?php
/**
 * Product: HELPME
 * @author: Galtsinak
 * @version: 30.11.12
 */
/**
 *
 */
class Api_AgreementController extends Service_RestController
{
    /**
     * Инициализируем модель
     */
    public function init()
    {
        parent::init();
        $this->_modelClass = 'HM_Model_Billing_Agreement';
    }

    /**
     * TODO: В доработке
     */
    public function putAction()
    {
        $agreement = HM_Model_Billing_Agreement::load($this->_getParam('id'));
        if($agreement instanceof HM_Model_Billing_Agreement){
            if($this->getRequest()->getPost('agreement')) {
                $agreement->setData($this->getRequest()->getPost('agreement'));
            } else {
                $agreement->setData($this->getRequest()->getPost());
            }
            if($agreement->save()){
                $this->setAjaxStatus(self::STATUS_OK);
                $this->setAjaxResult($this->_getParam('id'));
                $this->getResponse()->setHttpResponseCode(202);
            } else {
                $this->getResponse()->setHttpResponseCode(500);
            }
        } else {
            $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * Удалить договор
     */
    public function deleteAction()
    {
        $agreement = HM_Model_Billing_Agreement::load($this->_getParam('id'));
        if($agreement instanceof HM_Model_Billing_Agreement){
            $agreement->getData()->setRemoved(true);
            if($agreement->save()) {
                $this->getResponse()->setHttpResponseCode(204);
            } else {
                $this->getResponse()->setHttpResponseCode(500);
            }
        } else {
            $this->getResponse()->setHttpResponseCode(404);
        }
    }


    /**
     * Подписать Пользователя
     * @param user
     */
    public function subscribeUserAction()
    {
        $agreement = HM_Model_Billing_Agreement::load($this->_getParam('id'));
        if($agreement instanceof HM_Model_Billing_Agreement){
            $user = HM_Model_Account_User::load($this->_getParam('user'));
            if($user instanceof HM_Model_Account_User) {
                $subscription = $agreement->getSubscription();
                if(!$subscription->hasUser($user)){
                    if($subscription->addUser($user) == $user->getData()->getId()){
                        $events = Zend_Registry::get('events');
                        $events['agreement_subscribe_user']
                            ->setUser($user)
                            ->setAgreement($agreement)
                            ->notify();
                        $this->setAjaxResult($user->getData()->getId());
                        $this->setAjaxStatus(self::STATUS_OK);
                    }
                }
            }
        }
    }

    /**
     * Отписать Пользователя
     * @param user
     */
    public function unsubscribeUserAction()
    {
        $agreement = HM_Model_Billing_Agreement::load($this->_getParam('id'));
        if($agreement instanceof HM_Model_Billing_Agreement){
            $user = HM_Model_Account_User::load($this->_getParam('user'));
            if($user instanceof HM_Model_Account_User) {
                $subscription = $agreement->getSubscription();
                if($subscription->hasUser($user)){
                    if($subscription->removeUser($user) == $user->getData()->getId()){
                        $events = Zend_Registry::get('events');
                        $events['agreement_unsubscribe_user']
                            ->setUser($user)
                            ->setAgreement($agreement)
                            ->notify();
                        $this->setAjaxResult($user->getData()->getId());
                        $this->setAjaxStatus(self::STATUS_OK);
                    }
                }
            }
        }
    }

    /**
     * Подписать Гостя
     * @param guest
     */
    public function subscribeGuestAction()
    {
        $agreement = HM_Model_Billing_Agreement::load($this->_getParam('id'));
        if($agreement instanceof HM_Model_Billing_Agreement){
            $guest = HM_Model_Account_Guest::load($this->_getParam('guest'));
            if($guest instanceof HM_Model_Account_Guest && $guest->isIdentity()) {
                $subscription = $agreement->getSubscription();
                if(!$subscription->hasGuest($guest)){
                    if($subscription->addGuest($guest) == $guest->getData()->getId()){
                        $this->setAjaxResult($guest->getData()->getId());
                        $this->setAjaxStatus(self::STATUS_OK);
                    }
                }
            }
        }
    }

    /**
     * Отписать Гостя
     * @param guest
     */
    public function unsubscribeGuestAction()
    {
        $agreement = HM_Model_Billing_Agreement::load($this->_getParam('id'));
        if($agreement instanceof HM_Model_Billing_Agreement){
            $guest = HM_Model_Account_Guest::load($this->_getParam('guest'));
            if($guest instanceof HM_Model_Account_Guest) {
                $subscription = $agreement->getSubscription();
                if($subscription->hasGuest($guest)){
                    if($subscription->removeGuest($guest) == $guest->getData()->getId()){
                        $this->setAjaxResult($guest->getData()->getId());
                        $this->setAjaxStatus(self::STATUS_OK);
                    }
                }
            }
        }
    }

    /**
     * Пользователи подписки
     */
    public function getSubscriptionUsersAction()
    {
        $agreement = HM_Model_Billing_Agreement::load($this->_getParam('id'));
        if($agreement instanceof HM_Model_Billing_Agreement){
            $userColl = new HM_Model_Account_User_Collection();
            $userColl->addToCollection($agreement->getSubscription()->getUsers());
            $this->setAjaxData($userColl->toArray());
            $this->setAjaxStatus(self::STATUS_OK);
        }
    }

    /**
     * Гости подписки
     */
    public function getSubscriptionGuestsAction()
    {
        $agreement = HM_Model_Billing_Agreement::load($this->_getParam('id'));
        if($agreement instanceof HM_Model_Billing_Agreement){
            $guestColl = new HM_Model_Account_Guest_Collection();
            $guestColl->addToCollection($agreement->getSubscription()->getGuests());
            $this->setAjaxData($guestColl->toArray());
            $this->setAjaxStatus(self::STATUS_OK);
        }
    }
}
