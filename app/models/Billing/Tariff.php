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
                ->execute('tarif_update', array(
                    'id_tarif'              => $this->getData('id'),
                    'name'                  => $this->getData('name'),
                    'consultation_enabled'  => $this->getData('consultation_enabled'),
                    'consultation_unlimited'=> $this->getData('consultation_unlimited'),
                    'auto_prolongate'       => $this->getData('auto_prolongate'),
                    'message_enabled'       => $this->getData('message_enabled'),
                    'message_unlimited'     => $this->getData('message_unlimited'),
                    'not_available'         => $this->getData('not_available'),
                    'minute_count'          => $this->getData('minute_count'),
                    'message_count'         => $this->getData('message_count'),
                    'price'                 => $this->getData('price'),
                    'specchoice'            => $this->getData('specchoice'),
                    'description'           => $this->getData('description'),
                    'message_response'      => $this->getData('message_response'),
                    'consultation_response' => $this->getData('consultation_response'),
                    'tmin'                  => $this->getData('tmin'),
                    'message_price'         => $this->getData('message_price'),
                    'minute_price'          => $this->getData('minute_price'),
                    'queue_priority'        => $this->getData('queue_priority'),
                    'tquant'                => $this->getData('tquant'),
                    'period'                => $this->getData('period'),
                    'tqmin'                 => $this->getData('tqmin'),
                    'active'                => $this->getData('active'),
                    'need_committer'        => $this->getData('need_committer')
                )
            );
            $row = $result->fetchRow();
            if($row['o_id_tarif'] !== -1) {
                $this->getData()->unmarkDirty();
                return $this->getData('id');
            }
        }

        return parent::_update();
    }

    /**
     * @return int
     */
    protected function _remove()
    {
        if($this->isIdentity()) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('tarif_del', array(
                    'id_tarif'              => $this->getData('id')
                )
            );
            $row = $result->fetchRow();
            if((int)$row['o_id_tarif'] == $this->getData('id')) {
                $this->getData()->clear();
                return $row['o_id_tarif'];
            }
        }
        return parent::_remove();
    }

    /**
     * Получить ЛК
     * @return HM_Model_Counseling_Structure_Line|null
     */
    public function getLine()
    {
        return $this->_getDataObject('line');
    }

    /**
     * Установить ЛК для тарифа
     * @param $line
     */
    public function setLine($line)
    {
        if($line instanceof HM_Model_Counseling_Structure_Line) {
            $this->_setDataObject('line', $line);
        } elseif (is_int($line)) {
            self::setLine(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory')
                ->restore($line));
        }
    }
}