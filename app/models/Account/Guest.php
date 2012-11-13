<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Гости системы, сохраненные в Кэше приглашений системы
 */
class HM_Model_Account_Guest extends App_Core_Model_Data_Entity
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    public function activate(){}
    public function getInvites(){}
}