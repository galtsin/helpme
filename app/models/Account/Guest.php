<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Гость системы.
 * Учетная запись, зафиксированная в системе в формате email`a и уникального кода активации
 */
class HM_Model_Account_Guest extends App_Core_Model_Store_Entity
{
    /**
     * @param int $id
     * @return HM_Model_Account_Guest|null
     */
    public static function load($id)
    {
        if(isset($id)) {

            $result = App::getResource('FnApi')
                ->execute('account_get_identity_guest', array(
                    'id_guest' => (int)$id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $guest = new HM_Model_Account_Guest();
                $guest->getData()
                    ->set('id', $id)
                    ->set('hash_activation', $row['o_hash_activation'])
                    ->set('email', $row['o_email'])
                    ->set('first_name', $row['o_first_name'])
                    ->set('last_name', $row['o_last_name'])
                    ->set('middle_name', $row['o_middle_name'])
                    ->set('date_created', strtotime($row['o_date_created']))
                    ->setDirty(false);

                return $guest;
            }
        }

        return null;
    }

    /**
     * Добавить Гостя
     * @return int
     */
    protected function _insert()
    {
        $result = App::getResource('FnApi')
            ->execute('account_add_guest', array(
                'email'         => $this->getData('email'),
                'first_name'    => $this->getData('first_name'),
                'last_name'     => $this->getData('last_name'),
                'middle_name'   => $this->getData('middle_name')
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            $guestId = (int)$row['o_id_guest'];

            // Загрузить новые данные
            $result = App::getResource('FnApi')
                ->execute('account_get_identity_guest', array(
                    'id_guest' => $guestId
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $this->getData()
                    ->set('hash_activation', $row['o_hash_activation'])
                    ->set('date_created', strtotime($row['o_date_created']))
                    ->setDirty(false);

                return $guestId;
            }
        }

        return parent::_insert();
    }

    /**
     * @return int
     */
    protected function _remove()
    {
        if($this->isIdentity()) {
            $result = App::getResource('FnApi')
                ->execute('account_remove_guest', array(
                    'id_guest'  => $this->getData()->getId()
                )
            );
            $row = $result->fetchRow();

            if((int)$row['o_id_guest'] == $this->getData()->getId()) {
                $this->getData()->clear();
                return (int)$row['o_id_guest'];
            }
        }

        return parent::_remove();
    }

    /**
     * Активировать Гостя в учетную запись Пользователя
     * @param string $login
     * @param string $password
     * @return bool
     */
    public function activate($login, $password)
    {
        if($this->isIdentity()){
            $result = App::getResource('FnApi')
                ->execute('account_activate_guest', array(
                    'id_guest'  => $this->getData()->getId(),
                    'login'     => $login,
                    'password'  => isset($password) ? $password : rand(1000, 9999)
                )
            );

            $row = $result->fetchRow();
            Zend_Debug::dump($row);
            if((int)$row['o_id_user'] > 0) {
                $user = HM_Model_Account_User::load((int)$row['o_id_user']);
                if($user instanceof HM_Model_Account_User) {
                    $this->getData()->set('activated_user', $user->getData()->getId());
                    $this->setProperty('activated_user', $user);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Получить активированную учетную запись
     * @return HM_Model_Account_User|null
     */
    public function getActivatedUser()
    {
        return $this->getProperty('activated_user');
    }
}