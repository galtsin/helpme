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

    /**
     * Добавить Гостя
     * @return int
     */
    protected function _insert()
    {
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('account_add_guest', array(
                'email'         => $this->getData('email'),
                'first_name'    => $this->getData('first_name'),
                'last_name'     => $this->getData('last_name'),
                'middle_name'   => $this->getData('middle_name')
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            return (int)$row['o_id_guest'];
        }

        return parent::_insert();
    }

    public function activate(){}
    public function getInvites(){}
    public function addInvite(){}
}