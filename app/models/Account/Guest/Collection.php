<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 13.11.12
 */
/**
 * ru:
 */
class HM_Model_Account_Guest_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_Guest_Factory'));
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'email');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'hashActivation');
    }

    /**
     * Фильтр по хэшу
     * @return array
     */
    protected function _doHashActivationEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('hashActivation')) > 0) {
            foreach($this->getEqualFilterValues('hashActivation') as $hashActivation) {
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('account_get_guest_by_hash_activation', array(
                        'hash_activation' => $hashActivation
                    )
                );
                $row = $result->fetchRow();
                if($row['o_id_guest'] !== -1) {
                    $ids[] = (int)$row['o_id_guest'];
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
                    ->execute('account_get_guest_by_email', array(
                        'email' => $email
                    )
                );
                $row = $result->fetchRow();
                if($row['o_id_guest'] != -1) {
                    $ids[] = (int)$row['o_id_guest'];
                }
            }
        }

        return $ids;
    }
}
