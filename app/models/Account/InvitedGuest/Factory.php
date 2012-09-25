<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Фабрика сущностей Приглашенный Гость
 */
class HM_Model_Account_InvitedGuest_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @param int $id
     * @return HM_Model_Account_InvitedGuest|null
     */
    public function restore($id)
    {
        $invitedGuest = null;

        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('get_identity_invited_user', array(
                'id' => (int)$id
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            $invitedGuest = new HM_Model_Account_InvitedGuest();
            $invitedGuest->getData()
                        ->set('id', $id)
                        ->set('hashlink', $row['o_hashlink'])
                        ->set('email', $row['o_email'])
                        ->set('first_name', $row['o_first_name'])
                        ->set('last_name', $row['o_last_name'])
                        ->set('middle_name', $row['o_middle_name'])
                        ->set('activated', $row['o_activated'])
                        ->set('create_date',$row['o_create_date']);
        }

        return $invitedGuest;
    }
}