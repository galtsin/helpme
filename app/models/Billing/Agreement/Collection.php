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
        $this->setModelRestore('HM_Model_Billing_Agreement');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'company_owner'); // owner
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'company_client'); // client
    }

    /**
     * Фильтр по Компании Владельцу
     * @return array
     */
    protected function _doCompanyOwnerEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('company_owner')) > 0) {
            foreach($this->getEqualFilterValues('company_owner') as $company) {
                $result = App::getResource('FnApi')
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

        if(count($this->getEqualFilterValues('company_client')) > 0) {
            foreach($this->getEqualFilterValues('company_client') as $company) {
                $result = App::getResource('FnApi')
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