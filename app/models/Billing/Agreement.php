<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Договор
 */
class HM_Model_Billing_Agreement extends App_Core_Model_Data_Entity
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    public function addToSubscription(array $options){}

    public function addUserToSubscription(HM_Model_Account_User $user){}

    public function removeUserFromSubscription(HM_Model_Account_User $user){}

    public function addInvitedGuestToSubscription(HM_Model_Account_InvitedGuest $invitedGuest){}

    public function removeInvitedGuestFromSubscription(HM_Model_Account_InvitedGuest $invitedGuest){}

    public function getUsersFromSubscription(){}

    public function getInvitedGuestFromSubscription(){}

    public function getCompanyOwner(){}

    public function getCompanyClient()
    {

    }

    public function getTariff()
    {
        return $this->_getDataObject('tariff');
    }

    public function setTariff($tariff)
    {
        if($tariff instanceof HM_Model_Billing_Tariff) {
            $this->_setDataObject('tariff', $tariff);
        } elseif (is_int($tariff)) {
            self::setTariff(App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Tariff_Factory')
                ->restore($tariff));
        }

        return $this;
    }


    protected function _insert(){}

    protected function _update(){}
}