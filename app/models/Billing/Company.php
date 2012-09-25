<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 26.03.12
 */
/**
 * ru: Модель сущности Компания
 */
class HM_Model_Billing_Company extends App_Core_Model_Data_Entity
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }
}