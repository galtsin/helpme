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

    public function getCompanyOwner(){}

    public function getCompanyClient(){}

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