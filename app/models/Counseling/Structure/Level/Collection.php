<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Коллекция по Уровням
 */
class HM_Model_Counseling_Structure_Level_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Level_Factory'));
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
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
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('line_get_levels', array(
                        'id_line' => $line
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
}