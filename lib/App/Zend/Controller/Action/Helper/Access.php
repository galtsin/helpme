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
    public function getPossibilities($type, $companyOwner, /*временно вместо self::getActionAllowedRoles*/$roles)
    {
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();
        $possibilities = array();

        $accessColl = new HM_Model_Account_Access_Collection();
        $accessColl->setType($type);

        foreach($this->getActionAllowedRoles() as $role) {
            $accessColl->resetFilters();
            $accessColl->setAccessFilter(
                HM_Model_Account_User::load($account['user']),
                $access->getRole($role),
                $companyOwner)->getCollection();
            $possibilities = array_merge($accessColl->getPossibilities(), $possibilities);
        }

        return $possibilities;
    }
}