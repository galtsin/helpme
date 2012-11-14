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


    /**
     * Получить подписку на Договор
     * @return HM_Model_Billing_Agreement_Subscription|null
     */
    public function getSubscription()
    {
        $property = 'subscription';

        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                $subscription = new HM_Model_Billing_Agreement_Subscription();
                $subscription->getData()
                    ->set('id', $this->getData()->getId())
                    ->setDirty(false);
                $this->setProperty($property, $subscription);
            }
        }

        return $this->getProperty($property);
    }


    /**
     * TODO: В разработке
     * Добавить пользователя на Договор
     * @param HM_Model_Account_User $user
     * @return int
     */
    public function addSubscriptionUser(HM_Model_Account_User $user)
    {
        // Проверка подписчика в текущищих
        foreach($this->getSubscriptionUsers() as $subscriptionUser) {
            if($subscriptionUser->getData()->getId() == $user->getData()->getId()) {
                return $subscriptionUser->getData()->getId();
            }
        }

        if($this->isIdentity()) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('agreement_add_subscriber', array(
                    'id_agreement'  => $this->getData()->getId(),
                    'id_user'       => $user->getData()->getId()
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                if((int)$row["o_id_subscribe"] > 0) {

                }
            }

        }

/*        $row = $result->fetchRow();
        if((int)$row["o_id_subscribe"] > 0) {
            $subscribers = $this->getData()->get('subscribers');
            array_push($subscribers, $userId);
            $this->getData()->set('subscribers', $subscribers);
            return $userId;
        }*/


    }


    public function removeSubscriptionUser(HM_Model_Account_User $user){}

    public function addSubscriptionGuest(HM_Model_Account_Guest $guest){}

    public function removeSubscriptionGuest(HM_Model_Account_guest $guest){}

    /**
     * Final
     * TODO: Пример для подражания!
     * Получить список подписчиков на текущий договор
     * @return HM_Model_Account_User[]|null
     */
    public function getSubscriptionUsers()
    {
        $property = 'subscribers';

        if(null == $this->getProperty($property)) {
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

                $this->getData()->set($property, $userColl->getIdsIterator());
                $this->setProperty($property, $userColl->getObjectsIterator());
            }
        }

        return $this->getProperty($property);
    }

    /**
     * Получить список приглашенных на текущий договор пользоваелей
     */
    public function getSubscriptionGuests()
    {
        $property = 'invited_subscribers';

        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {

                $guestColl = new HM_Model_Account_Guest_Collection();

                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('agreement_get_invited_users', array(
                        'id_agreement' => $this->getData()->getId()
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $guestColl->load((int)$row["o_id_invited_user"]);
                    }
                }

                $this->getData()->set($property, $guestColl->getIdsIterator());
                $this->setProperty($property, $guestColl->getObjectsIterator());

            }
        }

        return $this->getProperty($property);
    }


    public function getCompanyOwner(){}

    public function getCompanyClient()
    {

    }

    /**
     * @deprecated
     * @return mixed
     */
    public function getTariff()
    {
        return $this->_getDataObject('tariff');
    }

    /**
     * @deprecated
     * @param $tariff
     * @return HM_Model_Billing_Agreement
     */
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

}