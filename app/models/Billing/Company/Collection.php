<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 01.06.12
 */
/**
 * ru: Коллекция по сущностям HM_Model_Account_User
 */
class HM_Model_Billing_Company_Collection extends App_Core_Collection_Filter
{
    /**
     * ru: Инициализия
     */
    public function __construct()
    {
        parent::__construct(HM_Model_Billing_Company_Factory::getInstance());
        $this->_addFilterName(App_Core_Collection_Filter::EQUAL_FILTER, 'inn');
        $this->_addFilterName(App_Core_Collection_Filter::EQUAL_FILTER, 'kpp');
    }

    /**
    * ru: Вернуть результат работы фильтра отбора по ИНН
    *
    * @return array
    */
    protected function _doInnEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('inn')) > 0) {
            // Получить ресурс
            $resource = $this->getFactory()->getResource('postgres_api');
            foreach($this->getEqualFilterValues('inn') as $inn) {
                $result = $resource->execute('company_by_inn', array('inn' => $inn));
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
    * ru: Вернуть результат работы фильтра отбора по КПП
    *
    * @return array
    */
    protected function _doKppEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('kpp')) > 0) {
            // Получить ресурс
            $resource = $this->getFactory()->getResource('postgres_api');
            foreach($this->getEqualFilterValues('kpp') as $kpp) {
                $result = $resource->execute('company_by_kpp', array('kpp' => $kpp));
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