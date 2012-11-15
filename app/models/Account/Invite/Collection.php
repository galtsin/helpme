<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 13.11.12
 */
/**
 * ru:
 */
class HM_Model_Account_Invite_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_Invite_Factory'));
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->setModelRestore('HM_Model_Account_Invite');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'guest');
    }

    /**
     * Фильтр по Гостю
     * @return array
     */
    protected function _doGuestEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('guest')) > 0) {
            foreach($this->getEqualFilterValues('guest') as $guest) {
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('account_get_invites_by_guest', array(
                        'id_guest' => $guest
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = (int)$row['o_id_invite'];
                    }
                }

            }
        }

        return $ids;
    }
}
