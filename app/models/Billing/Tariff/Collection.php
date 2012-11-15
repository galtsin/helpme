<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Коллекция по сущностям HM_Model_Account_User
 */
class HM_Model_Billing_Tariff_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setModelRestore('HM_Model_Billing_Tariff');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'line');
    }

    /**
     * Фильтр по Линии Консультации
     * @return array
     */
    protected function _doLineEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('line')) > 0) {
            foreach($this->getEqualFilterValues('line') as $line) {
                $result = App::getResource('FnApi')
                    ->execute('line_get_tarifs', array(
                        'id_line' => $line
                    )
                );
                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = $row['o_id_tariff'];
                    }
                }
            }
        }

        return $ids;
    }
}