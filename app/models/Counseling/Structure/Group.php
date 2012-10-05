<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Уровень
 */
class HM_Model_Counseling_Structure_Group extends App_Core_Model_Data_Entity
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }
}
