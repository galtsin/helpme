<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 14.11.12
 */
/**
 * ru:
 */
class HM_Model_Billing_Agreement_Subscription_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    public function restore($id)
    {
        $subscription = null;

        if(isset($id)) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('agreement_get_identity_subscription', array(
                    'id_agreement' => (int)$id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $agreement = new HM_Model_Billing_Agreement();
                $agreement->getData()
                    ->set('id', $id)
                    ->set('date_begin', strtotime($row['o_date_begin']))
                    ->set('date_end', strtotime($row['o_date_end']))
                    ->set('tariff', (int)$row['o_id_tarif'])
                    ->set('invoice', (int)$row['o_id_account'])
                // Компания - Владелец ЛК; оказывает услуги
                    ->set('company_owner', (int)$row['o_id_company_owner_line'])
                // Компания-контрагент; поребляет услуги
                    ->set('company_client', (int)$row['o_id_company_client']);
                $agreement->getData()->setDirty(false);
            }
        }


        return $agreement;
    }
}
