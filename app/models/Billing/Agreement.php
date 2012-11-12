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

    /**
     * TODO: Доработать
     * Получить список подписчиков
     */
    public function getSubscriptionUsers()
    {
        $key = 'subscribers';

        if(!$this->getData()->has($key)) {
            if($this->isIdentity()) {
                $userColl = new HM_Model_Account_User_Collection();
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('agreement_get_subscribers', array(
                        'id_agreement' => $this->getData()->getId()
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $userColl->load((int)$row["o_id_user"]);
                    }
                }

                $this->getData()
                    ->set($key, $userColl->getIdsIterator());

                $this->_setDataObject($key, $userColl->getObjectsIterator());
            }
        }

        return $this->_getDataObject($key);
    }



    public function getSubscriptionInvitedGuests(){}

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

    /**
     * Создать договор
     * @return int
     */
    protected function _insert()
    {
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('agreement_add', array(
                'id_tariff'     => (int)$this->getData('tariff'),
                'id_invoice'    => (int)$this->getData('invoice'),
                'date_end'      => $this->getData('date_end')
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            return (int)$row['o_id_agreement'];
        }

        return parent::_insert();
    }

    protected function _update(){}
}