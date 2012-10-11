<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Модель сущности Компания
 */
class HM_Model_Billing_Tariff extends App_Core_Model_Data_Entity
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * Вставить Тариф на Линию Консультации
     * @return int
     */
    protected function _insert()
    {
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('tarif_add', array(
                'id_line'   => $this->getData('line'),
                'name'      => $this->getData('name')
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            return (int)$row['o_id_tarif'];
        }

        return parent::_insert();
    }

    /**
     * TODO: в разработке
     * Обновить информацию Тарифа
     * @return int
     */
    protected function _update()
    {
        if($this->getData()->isDirty()) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('level_update_identity', array(
                    'id_level'  => $this->getData('id'),
                    'name'      => $this->getData('name')
                )
            );
            $row = $result->fetchRow();
            if($row['o_id_level'] !== -1) {
                $this->getData()->unmarkDirty();
                return $this->getData('id');
            }
        }

        return parent::_update();
    }
}