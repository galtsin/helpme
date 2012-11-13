<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 13.11.12
 */
/**
 * ru:
 */
class HM_Model_Account_Invite_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @param int $id
     * @return HM_Model_Account_Invite|null
     */
    public function restore($id)
    {
        $invite = null;

        if(isset($id)) {

            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('account_get_identity_invite', array(
                    'id_invite' => (int)$id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $invite = new HM_Model_Account_Invite();
                $invite->getData()
                    ->set('id', $id)
                    ->set('date_created', strtotime($row['o_date_created']))
                    ->set('guest', $row['o_id_guest'])
                    ->set('is_activated', $row['o_is_activated'])
                    ->setDirty(false);
            }
        }

        return $invite;
    }
}
