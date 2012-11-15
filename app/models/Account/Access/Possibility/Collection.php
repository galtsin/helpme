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
        $this->setModelRestore('HM_Model_Account_Access_Possibility');
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
                $result = App::getResource('FnApi')
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
     * Фильтр по связке пользователь + роль + компания (User + Role + Company)
     * @return array
     */
    protected function _doUrcEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('urc')) > 0) {
            foreach($this->getEqualFilterValues('urc') as $urc) {
                if(is_array($urc)) {
                    $result = App::getResource('FnApi')
                        ->execute('possibility_by_urc', array(
                            'id_user'       => (int)$urc['user'],
                            'id_role'       => HM_Model_Account_Access::getInstance()->getRole($urc['role'])->getId(),
                            'id_company'    => (int)$urc['company']
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