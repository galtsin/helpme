<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 15.10.12
 */
/**
 * ru:
 */
class HM_Model_Account_Access_Possibility extends App_Core_Model_Data_Entity
{
    // Восстанавливают объекты
    public function getUser(){}
    public function getRole(){}
    public function getCompany(){}

    /**
     * Инициализация
     */
    protected function _init()
    {
        parent::_init();
        $this->setData('possibility', array(
                'write' => array(),
                'read'  => array()
            )
        );
    }

    protected function _insert()
    {
        return parent::_insert();
    }

    /**
     * @param int $entityId
     * @return HM_Model_Account_Access_Possibility
     */
    public function addWrite($entityId)
    {
        $possibility = $this->getData('possibility');
        if(!in_array($entityId, $possibility['write'])) {
            $possibility['write'][] = $entityId;
            $this->setData('possibility', $possibility);
        }

        return $this;
    }

    /**
     * @param int $entityId
     * @return HM_Model_Account_Access_Possibility
     */
    public function addRead($entityId)
    {
        $possibility = $this->getData('possibility');
        if(!in_array($entityId, $possibility['read'])) {
            $possibility['read'][] = $entityId;
            $this->setData('possibility', $possibility);
        }
        return $this;
    }
}
