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
        $this->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Group_Factory'));
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'level');
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
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
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
}