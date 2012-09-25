<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Пользователь, получивший инвайт в систему
 */
class HM_Model_Account_InvitedGuest extends App_Core_Model_Data_Entity
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    public function activate(){}
}