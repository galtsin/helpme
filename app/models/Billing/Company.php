<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Модель сущности Компания
 */
class HM_Model_Billing_Company extends App_Core_Model_Store_Entity
{
    /**
     * @param int $id
     * @return HM_Model_Billing_Company|null
     */
    public static function load($id)
    {
        // TODO: Возможны варианты, когда id = 0 и empty принимает ее за пустое значение
        $id = intval($id);
        if($id == 0 || !empty($id)) {
            $result = App::getResource('FnApi')
                ->execute('company_get_identity', array(
                    'id' => $id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $company = new self();
                $company->getData()
                    ->set('id', $id)
                    ->set('name', $row['o_name'])
                    ->set('inn', $row['o_inn'])
                    ->set('kpp', $row['o_kpp'])
                    ->set('user_creator', $row['o_id_creator'])
                    ->setDirty(false);

                return $company;
            }
        }

        return null;
    }

    /*    protected function _insert(App_Core_Model_DataObject $dataObject)
    {
        $identity = $dataObject->get('identity');
        if(!empty($identity)) {
            $result = $this->getResource('postgres_api')->execute('company_add', array(
                    'name'  => $identity['name'],
                    'inn'   => $identity['inn'],
                    'kpp'   => $identity['kpp'],
                    'id_user_creator'   => $identity['user_creator']
                )
            );
            $row = $result->fetchRow();
            return (int)$row['company_add'];
        }
        return -1;
    }*/

    /**
     * Получить список договоров, где компания является владельцем ЛК (Партнером)
     * @return HM_Model_Billing_Agreement[]|null
     */
    public function getOwnerAgreements()
    {
        $property = 'owner_agreements';
        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                $agreementsColl = new HM_Model_Billing_Agreement_Collection();
                $agreementsColl->addEqualFilter('company_owner', $this->getData()->getId())
                    ->getCollection();
                $this->setProperty($property, $agreementsColl->getObjectsIterator());
                $this->getData()->set($property, $agreementsColl->getIdsIterator());
            }
        }

        return $this->getProperty($property);
    }

    /**
     * Получить список клиентских договоров, на которых компания является Контрагентом
     * @return HM_Model_Billing_Agreement[]|null
     */
    public function getClientAgreements()
    {
        $property = 'client_agreements';
        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                $agreementsColl = new HM_Model_Billing_Agreement_Collection();
                $agreementsColl->addEqualFilter('company_client', $this->getData('id'))
                    ->getCollection();
                $this->setProperty($property, $agreementsColl->getObjectsIterator());
                $this->getData()->set($property, $agreementsColl->getIdsIterator());
            }
        }

        return $this->getProperty($property);
    }

    /**
     * Получить счета компании
     * @return int[]|null
     */
    public function getInvoices()
    {
        $property = 'invoices';
        if(!$this->getData()->has($property)) {
            if($this->isIdentity()) {
                $invoices = array();
                $result = App::getResource('FnApi')
                    ->execute('company_get_invoices', array(
                        'id_company' => $this->getData()->getId()
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $invoices[] = $row['o_id_invoice'];
                    }
                }

                $this->getData()->set($property, $invoices);
            }
        }

        return $this->getData($property);
    }

    /**
     * Добавить в компанию новый счет
     * @return int
     */
    public function addInvoice()
    {
        if($this->isIdentity()) {
            $result = App::getResource('FnApi')
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

    /**
     * Получить баланс по счету
     * ID счета = ID баланса
     * @param int $invoice
     * @return App_Core_Model_Store_Data|null
     */
    public function getInvoiceBalance($invoice)
    {
        $balance = null;
        if($this->isIdentity()) {
            if(in_array($invoice, $this->getInvoices())){
                $result = App::getResource('FnApi')
                    ->execute('company_get_invoice_balance', array(
                        'id_invoice' => (int)$invoice
                    )
                );

                if($result->rowCount() > 0) {
                    $row = $result->fetchRow();
                    $balance = new App_Core_Model_Store_Data();
                    $balance->set('id', $invoice)
                        ->set('messages_count', (int)$row['o_messages_count'])
                        ->set('sum_time', (int)$row['o_sum_time'])
                        ->set('sum_money', (float)$row['o_sum_money'])
                        ->setDirty(false);
                }
            }
        }

        return $balance;

    }
}