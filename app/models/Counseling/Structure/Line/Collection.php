<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Коллекция по Линиям Консультации
 */
class HM_Model_Counseling_Structure_Line_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory'));
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'company');
    }

    /**
     * Фильтр по компании
     * @return array
     */
    protected function _doCompanyEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('company')) > 0) {
            foreach($this->getEqualFilterValues('company') as $company) {
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('lines_by_company', array(
                        'id_company' => $company
                    )
                );
                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = $row['o_id_line'];
                    }
                }
            }
        }

        return $ids;
    }
}