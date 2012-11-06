<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Модель сущности Компания
 */
class HM_Model_Billing_Company extends App_Core_Model_Data_Entity
{
    /**
     * @var App_Core_Model_Data_Entity[]null
     */
    private $_agreements = null;

    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }


    public function getAgreements()
    {
        if($this->isIdentity()) {
            if(null === $this->_agreements) {
                $agreementsColl = new HM_Model_Billing_Agreement_Collection();
                $agreementsColl->addEqualFilter('companyOwner', $this->getData('id'));
                $this->_agreements = $agreementsColl->getCollection()->getObjectsIterator();
            }
        }

        return $this->_agreements;
    }

    /**
     * Получить список договоров, где компания является владельцем ЛК
     * @return App_Core_Model_Data_Entity[]|null
     */
    public function getOwnerAgreements()
    {
        if($this->isIdentity()) {
            if(null === $this->_agreements) {
                $agreementsColl = new HM_Model_Billing_Agreement_Collection();
                $agreementsColl->addEqualFilter('companyOwner', $this->getData('id'));
                $this->_agreements = $agreementsColl->getCollection()->getObjectsIterator();
            }
        }

        return $this->_agreements;
    }

    /**
     * Получить список договоров, на которых компания является Контрагентом
     */
    public function getClientAgreements()
    {
        if($this->isIdentity()) {
            if(null === $this->_agreements) {
                $agreementsColl = new HM_Model_Billing_Agreement_Collection();
                $agreementsColl->addEqualFilter('companyClient', $this->getData('id'));
                $this->_agreements = $agreementsColl->getCollection()->getObjectsIterator();
            }
        }

        return $this->_agreements;
    }
}