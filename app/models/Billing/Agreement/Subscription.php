<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Подписка пользователей
 * TODO: ID Договора == ID Подписки
 */
class HM_Model_Billing_Agreement_Subscription extends App_Core_Model_Store_Entity
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * Получить список пользователей
     * @return HM_Model_Account_User[]|null
     */
    public function getUsers()
    {
        $property = 'users';

        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                $userColl = new HM_Model_Account_User_Collection();

                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('subscription_get_users', array( // refactor
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
     * Добавить пользователя в Подписку
     * @param HM_Model_Account_User $user
     * @return int
     */
    public function addUser(HM_Model_Account_User $user)
    {
        // Проверка аличия пользователя в текущей подписке
        if($this->hasUser($user)) {
            return $user->getData()->getId();
        }

        if($this->isIdentity()) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('subscription_add_user', array(
                    'id_agreement'  => $this->getData()->getId(),
                    'id_user'       => $user->getData()->getId()
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                if((int)$row["o_id_user"] > 0) {
                    $this->getData()
                        ->set('users', array_merge($this->getData('users'), array($user->getData()->getId())));
                    $this->setProperty('users', array_merge($this->getProperty('users'), array($user)));
                    return $user->getData()->getId();
                }
            }

        }

        return parent::_insert();
    }

    /**
     * Удалить пользователя из подписки
     * @param HM_Model_Account_User $user
     * @return int
     */
    public function removeUser(HM_Model_Account_User $user)
    {
        // Проверка аличия пользователя в текущей подписке
        if($this->hasUser($user)) {
            if($this->isIdentity()) {
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('subscription_remove_user', array(
                        'id_agreement'  => $this->getData()->getId(),
                        'id_user'       => $user->getData()->getId()
                    )
                );

                if($result->rowCount() > 0) {
                    $row = $result->fetchRow();
                    if((int)$row["o_id_user"] == $user->getData()->getId()) {

                        $userColl = new HM_Model_Account_User_Collection();

                        foreach($this->getUsers() as $subscriptionUser) {
                            if($subscriptionUser->getData()->getId() != $user->getData()->getId()) {
                                $userColl->addToCollection($subscriptionUser);
                            }
                        }

                        $this->getData()
                            ->set('users', $userColl->getIdsIterator());
                        $this->setProperty('users', $userColl->getObjectsIterator());


                        return $user->getData()->getId();
                    }
                }

            }
        }

        return parent::_remove();
    }

    /**
     * Проверка наличия пользователя в текущей подписке
     * @param HM_Model_Account_User $user
     * @return bool
     */
    public function hasUser(HM_Model_Account_User $user)
    {
        foreach($this->getUsers() as $subscriptionUser) {
            if($subscriptionUser->getData()->getId() == $user->getData()->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Проверка наличия гостя в текущей подписке
     * @param HM_Model_Account_Guest $guest
     * @return bool
     */
    public function hasGuest(HM_Model_Account_Guest $guest)
    {
        foreach($this->getGuests() as $subscriptionGuest) {
            if($subscriptionGuest->getData()->getId() == $guest->getData()->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Получить приглашенных Гостей в текущую подписку
     * @return HM_Model_Account_Guest[]|null
     */
    public function getGuests()
    {
        $property = 'guests';

        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                $guestColl = new HM_Model_Account_Guest_Collection();

                foreach($this->getInvites() as $invite) {
                    $guestColl->addToCollection($invite->getGuest());
                }

                $this->getData()->set($property, $guestColl->getIdsIterator());
                $this->setProperty($property, $guestColl->getObjectsIterator());
            }
        }

        return $this->getProperty($property);
    }

    /**
     * Добавить Гостя в текущую подписку
     * @param HM_Model_Account_Guest $guest
     * @return int
     */
    public function addGuest(HM_Model_Account_Guest $guest)
    {
        // Проверка аличия пользователя в текущей подписке
        if($this->hasGuest($guest)) {
            return $guest->getData()->getId();
        }

        if($this->isIdentity()) {

            // Создать инвайт
            $invite = new HM_Model_Account_Invite();
            $invite->getData()
                ->set('guest', $guest->getData()->getId());

            if($invite->save()){

                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('subscription_add_invite', array(
                        'id_invite'     => $invite->getData()->getId(),
                        'id_agreement'  => $this->getData()->getId()
                    )
                );

                if($result->rowCount() > 0) {
                    $row = $result->fetchRow();
                    if((int)$row["o_id_invite"] > 0) {
                        $this->getData()
                            ->set('guests', array_merge($this->getData('guests'), array($guest->getData()->getId())));
                        $this->setProperty('guests', array_merge($this->getProperty('guests'), array($guest)));
                        return $guest->getData()->getId();
                    }
                }
            }
        }

        return parent::_insert();
    }

    /**
     * Удалить гостя из текущей подписки
     * @param HM_Model_Account_Guest $guest
     * @return int
     */
    public function removeGuest(HM_Model_Account_Guest $guest)
    {
        // Проверка аличия пользователя в текущей подписке
        if($this->hasGuest($guest)) {
            if($this->isIdentity()) {
                foreach($this->getInvites() as $invite){
                    if($invite->getGuest()->getData()->getId() == $guest->getData()->getId()) {
                        $invite->getData()
                            ->setRemoved(true);
                        if($invite->save()) {

                            $guestColl = new HM_Model_Account_Guest_Collection();

                            foreach($this->getGuests() as $subscriptionGuest) {
                                if($subscriptionGuest->getData()->getId() != $guest->getData()->getId()) {
                                    $guestColl->addToCollection($subscriptionGuest);
                                }
                            }

                            $this->getData()
                                ->set('guests', $guestColl->getIdsIterator());
                            $this->setProperty('guests', $guestColl->getObjectsIterator());

                            return $guest->getData()->getId();
                        }
                    }
                }
            }
        }

        return parent::_remove();
    }


    /**
     * Получить приглашения подписки
     * @return HM_Model_Account_Invite[]|null
     */
    public function getInvites()
    {
        $property = 'invites';

        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                $inviteColl = new HM_Model_Account_Invite_Collection();

                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('subscription_get_invites', array( // refactor
                        'id_agreement' => $this->getData()->getId()
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $inviteColl->load((int)$row["o_id_invite"])->getGuest();
                    }
                }

                $this->getData()->set($property, $inviteColl->getIdsIterator());
                $this->setProperty($property, $inviteColl->getObjectsIterator());
            }
        }

        return $this->getProperty($property);
    }
}
