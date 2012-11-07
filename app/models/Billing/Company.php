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
     * Получить список клиентских договоров, на которых компания является Контрагентом
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

    /**
     * Получить счета компании
     * @return App_Core_Model_Data_Store|array|int|null
     */
    public function getInvoices()
    {
        $key = 'invoices';

        if(!$this->getData()->has($key)) {
            if($this->isIdentity()) {
                $invoices = array();
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('company_get_invoices', array(
                        'id_company' => $this->getData()->getId()
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $invoices[] = $row['o_id_invoice'];
                    }
                }

                $this->getData()
                    ->set('invoices', $invoices);
            }
        }

        return $this->getData($key);
    }

    /**
     * Добавить в компанию новый счет
     * @return int
     */
    public function addInvoice()
    {
        if($this->isIdentity()) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('company_add_invoice', array(
                    'id_company' => $this->getData()->getId()
                )
            );
            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                if($row['o_id_invoice'] > 0) {
                    if($this->getData()->has('invoices')) {
                        $this->getData()->set(
                            'invoices',
                            array_merge($this->getData('invoices'), array((int)$row['o_id_invoice']))
                        );
                    } else {
                        $this->getData()->set('invoices', (int)$row['o_id_invoice']);
                    }
                    return (int)$row['o_id_invoice'];
                }
            }
        }

        return parent::_insert();
    }
}