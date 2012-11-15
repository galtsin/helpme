<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 13.11.12
 */
/**
 * ru:
 */
class HM_Model_Account_Invite extends App_Core_Model_Data_Entity
{
    /**
     * @param int $id
     * @return HM_Model_Account_Invite|null
     */
    public static function load($id)
    {
        if(isset($id)) {
            $result = App::getResource('FnApi')
                ->execute('account_get_identity_invite', array(
                    'id_invite' => (int)$id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $invite = new self();
                $invite->getData()
                    ->set('id', $id)
                    ->set('date_created', strtotime($row['o_date_created']))
                    ->set('guest', $row['o_id_guest'])
                    ->set('is_activated', $row['o_is_activated'])
                    ->setDirty(false);

                return $invite;
            }
        }

        return null;
    }

    /**
     * Добавить Приглашение
     * @return int
     */
    protected function _insert()
    {
        $result = App::getResource('FnApi')
            ->execute('account_add_invite', array(
                'id_guest' => $this->getData('guest')
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            return (int)$row['o_id_invite'];
        }

        return parent::_insert();
    }

    /**
     * Удалить Приглашение
     * @return int
     */
    protected function _remove()
    {
        if($this->isIdentity()) {
            $result = App::getResource('FnApi')
                ->execute('account_remove_invite', array(
                    'id_invite' => $this->getData('id')
                )
            );
            $row = $result->fetchRow();
            if((int)$row['o_id_invite'] == $this->getData('id')) {
                $this->getData()->clear();
                return $row['o_id_invite'];
            }
        }
        return parent::_remove();
    }

    /**
     * Получить Гостя текущего приглашения
     * @return HM_Model_Account_Guest|null
     */
    public function getGuest()
    {
        $property = 'guest';

        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                $guestColl = new HM_Model_Account_Guest_Collection();
                $this->setProperty($property, $guestColl->load($this->getData('guest')));
            }
        }

        return $this->getProperty($property);
    }
}
