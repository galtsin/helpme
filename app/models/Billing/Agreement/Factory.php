<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Фабрика для Договоров
 */
class HM_Model_Billing_Agreement_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    public function restore($id){}
}