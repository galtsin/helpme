<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Договор
 */
class HM_Model_Billing_Agreement extends App_Core_Model_Store_Entity
{
    /**
     * @param $id
     * @return HM_Model_Billing_Agreement|null
     */
    public static function load($id)
    {
        $id = intval($id);
        if($id == 0 || !empty($id)) {
            $result = App::getResource('FnApi')
                ->execute('agreement_get_identity', array(
                    'id_agreement' => $id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $agreement = new self();
                $agreement->getData()
                    ->set('id', $id)
                    ->set('date_begin', strtotime($row['o_date_begin']))
                    ->set('date_end', strtotime($row['o_date_end']))
                    ->set('tariff', (int)$row['o_id_tarif'])
                    ->set('invoice', (int)$row['o_id_account'])
                // Компания - Владелец ЛК; оказывает услуги
                    ->set('company_owner', (int)$row['o_id_company_owner_line'])
                // Компания-контрагент; поребляет услуги
                    ->set('company_client', (int)$row['o_id_company_client'])
                    ->setDirty(false);

                return $agreement;
            }
        }

        return null;
    }

    /**
     * Создать договор
     * @return int
     */
    protected function _insert()
    {
        $result = App::getResource('FnApi')
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

    public function getCompanyOwner(){}

    public function getCompanyClient(){}

    /**
     * @deprecated
     * @return mixed
     */
    public function getTariff()
    {
        $property = 'tariff';
        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                $tariffColl = new HM_Model_Billing_Tariff_Collection();
                $this->setProperty($property, $tariffColl->load($this->getData($property)));
            }
        }

        return $this->getProperty($property);
    }
}