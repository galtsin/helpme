<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Уровень
 */
class HM_Model_Counseling_Structure_Level extends App_Core_Model_Data_Entity
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    public function addGroup(){}
    public function removeGroup(){}
}