<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Коллекция по сущностям Пользователь
 */
class HM_Model_Account_InvitedGuest_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_InvitedGuest_Factory'));
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'email');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'hashlink');
    }

    /**
     * Фильтр по хэшу
     * @return array
     */
    protected function _doHashlinkEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('hashlink')) > 0) {
            foreach($this->getEqualFilterValues('hashlink') as $hashlink) {
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('get_invited_user_by_hashlink', array(
                        'hashlink' => $hashlink
                    )
                );
                $row = $result->fetchRow();
                if($row['o_id_invited_user'] !== -1) {
                    $ids[] = (int)$row['o_id_invited_user'];
                }
            }
        }

        return $ids;
    }

    /**
    * Фильтр по электронной почте
    * @return array
    */
    protected function _doEmailEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('email')) > 0) {
            foreach($this->getEqualFilterValues('email') as $email) {
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('get_invited_user_by_email', array(
                        'email' => $email
                    )
                );
                $row = $result->fetchRow();
                if($row['o_id_invited_user'] != -1) {
                    $ids[] = (int)$row['o_id_invited_user'];
                }
            }
        }

        return $ids;
    }
}