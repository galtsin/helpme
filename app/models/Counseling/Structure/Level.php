<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Уровень
 */
class HM_Model_Counseling_Structure_Level extends App_Core_Model_Data_Entity
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * Вставить Уровень на Линию Консультации
     * @return int
     */
    protected function _insert()
    {
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('level_add', array(
                'id_line'   => $this->getData('line'),
                'name'      => $this->getData('name')
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            return (int)$row['level_add'];
        }

        return parent::_insert();
    }

    /**
     * Добавить группу
     */
    public function addGroup(){}

    /**
     * Удалить группу
     */
    public function removeGroup(){}
}