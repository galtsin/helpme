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
    }
}