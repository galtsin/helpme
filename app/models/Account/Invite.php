<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 13.11.12
 */
/**
 * ru:
 */
class HM_Model_Account_Invite extends App_Core_Model_Data_Entity
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * Добавить Приглашение
     * @return int
     */
    protected function _insert()
    {
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('account_add_invite', array(
                'id_guest' => $this->getData('guest')
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            return (int)$row['o_id_invite'];
        }

        return parent::_insert();
    }

    /**
     * Удалить Приглашение
     * @return int
     */
    protected function _remove()
    {
        if($this->isIdentity()) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('account_remove_invite', array(
                    'id_invite' => $this->getData('id')
                )
            );
            $row = $result->fetchRow();
            if((int)$row['o_id_invite'] == $this->getData('id')) {
                $this->getData()->clear();
                return $row['o_id_invite'];
            }
        }
        return parent::_remove();
    }
}
