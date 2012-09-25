<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Коллекция по сущностям Договор
 */
class HM_Model_Billing_Agreement_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Agreement_Factory'));
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'companyOwner');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'companyClient');
    }

    /**
     * Фильтр по Компании Владельцу
     * @return array
     */
    protected function _doCompanyOwnerEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('companyOwner')) > 0) {
            foreach($this->getEqualFilterValues('companyOwner') as $company) {
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('agreements_by_company_owner_line', array(
                        'id_company' => $company
                    )
                );
                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = $row['id_agreement'];
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Фильтр по Компании Клиенту
     * @return array
     */
    protected function _doCompanyClientEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('companyClient')) > 0) {
            foreach($this->getEqualFilterValues('companyClient') as $company) {
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('agreements_by_company_client', array(
                        'id_company' => $company
                    )
                );
                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = $row['id_agreement'];
                    }
                }
            }
        }

        return $ids;
    }
}