<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 01.06.12
 */
/**
 * ru: Коллекция по сущностям HM_Model_Account_User
 */
class HM_Model_Billing_Company_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setModelRestore('HM_Model_Billing_Company');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'inn');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'kpp');
    }

    /**
     * Фильтр по ИНН компании
     * @return array
     */
    protected function _doInnEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('inn')) > 0) {
            foreach($this->getEqualFilterValues('inn') as $inn) {
                $result = App::getResource('FnApi')
                    ->execute('company_by_inn', array(
                        'inn' => $inn
                    )
                );
                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = $row['id_company'];
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Фильтр по КПП компании
     * @return array
     */
    protected function _doKppEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('kpp')) > 0) {
            foreach($this->getEqualFilterValues('kpp') as $kpp) {
                $result = App::getResource('FnApi')
                    ->execute('company_by_kpp', array(
                        'kpp' => $kpp
                    )
                );
                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $ids[] = $row['id_company'];
                    }
                }
            }
        }

        return $ids;
    }
}