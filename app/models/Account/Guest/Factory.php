<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 13.11.12
 */
/**
 * Восстановить пользователя, если он был зафиксирован в приглашениях в системе
 */
class HM_Model_Account_Guest_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @param int $id
     * @return HM_Model_Account_Guest|null
     */
    public function restore($id)
    {
        $guest = null;

        if(isset($id)) {

            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('get_identity_invited_user', array(
                    'id_guest' => (int)$id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $guest = new HM_Model_Account_Guest();
                $guest->getData()
                    ->set('id', $id)
                    ->set('hashlink', $row['o_hashlink'])
                    ->set('email', $row['o_email'])
                    ->set('first_name', $row['o_first_name'])
                    ->set('last_name', $row['o_last_name'])
                    ->set('middle_name', $row['o_middle_name'])
                    ->set('is_activated', $row['o_activated']) // is_activated
                    ->set('date_create', $row['o_create_date'])
                    ->setDirty(false);
            }
        }

        return $guest;
    }
}
