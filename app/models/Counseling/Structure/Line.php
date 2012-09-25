<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Линия Консультации
 */
class HM_Model_Counseling_Structure_Line extends App_Core_Model_Data_Entity
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    public function addLevel(){}
    public function removeLevel(){}
    public function getLevels(){}
}