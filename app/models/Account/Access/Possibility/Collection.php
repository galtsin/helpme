<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Коллекция по сущностям Possibility
 */
class HM_Model_Account_Access_Possibility_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_Access_Possibility_Factory'));
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'company');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'urc');
    }

    /**
     * Фильтр по компании
     * @return array
     */
    protected function _doCompanyEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('company')) > 0) {
            foreach($this->getEqualFilterValues('company') as $company) {
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('possibility_by_company', array(
                        'id_company' => $company
                    )
                );
                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = $row['o_id_possibility'];
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Фильтр по связке пользователь+роль+компания
     * @return array
     */
    protected function _doUrcEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('urc')) > 0) {
            foreach($this->getEqualFilterValues('urc') as $urc) {
                if(is_array($urc)) {
                    $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                        ->execute('possibility_by_urc', array(
                            'id_user'       => $urc['user'],
                            'id_role'       => $urc['role'],
                            'id_company'    => $urc['company']
                        )
                    );
                    if($result->rowCount() > 0) {
                        foreach($result->fetchAll() as $row) {
                            $ids[] = $row['o_id_possibility'];
                        }
                    }
                }

            }
        }

        return $ids;
    }
}