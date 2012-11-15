<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Коллекция по Группам
 */
class HM_Model_Counseling_Structure_Group_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setModelRestore('HM_Model_Counseling_Structure_Group');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'level');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'company');
    }

    /**
     * Фильтр по Линии Консультации
     * @return array
     */
    protected function _doLevelEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('level')) > 0) {
            foreach($this->getEqualFilterValues('level') as $level) {
                $result = App::getResource('FnApi')
                    ->execute('level_get_groups', array(
                        'id_level' => $level
                    )
                );
                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = $row['o_id'];
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Фильтр по Компании
     * @return array
     */
    protected function _doCompanyEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('company')) > 0) {
            foreach($this->getEqualFilterValues('company') as $company) {
                $result = App::getResource('FnApi')
                    ->execute('company_get_groups', array(
                        'id_company' => $company
                    )
                );
                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = $row['o_id_group'];
                    }
                }
            }
        }

        return $ids;
    }
}