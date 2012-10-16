<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 15.10.12
 */
/**
 * ru:
 */
class App_Zend_Controller_Action_Helper_Access extends Zend_Controller_Action_Helper_Abstract
{
    // Получить разешенные для действия роли
    public function getActionAllowedRoles()
    {
        return array();
    }

    //
    /**
     * @param string $type
     * @param int $companyOwner
     * @return array
     */
    public function getPossibilities($type, $companyOwner)
    {
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();
        $possibilities = array();

        $accessColl = new HM_Model_Account_Access_Collection();
        $accessColl->setType($type);
        foreach($this->getActionAllowedRoles() as $role) {
            $accessColl->resetFilters();
            $accessColl->setAccessFilter(
                App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user']),
                $access->getRole($role),
                $companyOwner)->getCollection();
            $possibilities = array_merge($accessColl->getPossibilities(), $possibilities);
        }

        return $possibilities;
    }
}