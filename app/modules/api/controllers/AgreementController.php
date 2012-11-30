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
     * Подписать Пользователя
     * @param user
     */
    public function subscribeUserAction()
    {
        $agreement = HM_Model_Billing_Agreement::load($this->_getParam('id'));
        if($agreement instanceof HM_Model_Billing_Agreement){
            $user = HM_Model_Account_User::load($this->_getParam('user'));
            if($user instanceof HM_Model_Account_User) {
                if($agreement->getSubscription()->addUser($user) == $user->getData()->getId()){
                    $this->setAjaxResult($user->getData()->getId());
                    $this->setAjaxStatus('ok');
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
                if($agreement->getSubscription()->removeUser($user) == $user->getData()->getId()){
                    $this->setAjaxResult($user->getData()->getId());
                    $this->setAjaxStatus('ok');
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
                if($agreement->getSubscription()->addGuest($guest) == $guest->getData()->getId()){
                    $this->setAjaxResult($guest->getData()->getId());
                    $this->setAjaxStatus('ok');
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
                if($agreement->getSubscription()->removeGuest($guest) == $guest->getData()->getId()){
                    $this->setAjaxResult($guest->getData()->getId());
                    $this->setAjaxStatus('ok');
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
            $this->setAjaxStatus('ok');
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
            $this->setAjaxStatus('ok');
        }
    }
}
