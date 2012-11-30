<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Модель сущности Компания
 */
class HM_Model_Billing_Tariff extends App_Core_Model_Store_Entity
{
    /**
     * @param int $id
     * @return HM_Model_Billing_Tariff
     */
    public static function load($id)
    {
        $id = intval($id);
        if($id == 0 || !empty($id)) {
            $result = App::getResource('FnApi')
                ->execute('tarif_get_identity', array(
                    'id_tariff' => $id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $tariff = new self();
                $tariff->getData()
                    ->set('id', $id)
                    ->set('name', $row['o_name'])
                    ->set('consultation_enabled', (bool)$row['o_consultation_enabled'])
                    ->set('consultation_unlimited', (bool)$row['o_consultation_enabled'])
                    ->set('auto_prolongate', (bool)$row['o_auto_prolongate'])
                    ->set('message_enabled', (bool)$row['o_message_enabled'])
                    ->set('message_unlimited', (bool)$row['o_message_unlimited'])
                    ->set('not_available', (bool)$row['o_not_available'])
                    ->set('minute_count', (int)$row['o_minute_count'])
                    ->set('message_count', (int)$row['o_message_count'])
                    ->set('price', (float)$row['o_price'])
                    ->set('specchoice', (bool)$row['o_specchoice'])
                    ->set('description', $row['o_description']) // Очистить HTML сущности (XSS) htmlspecialchars
                    ->set('message_response', $row['o_message_response'])
                    ->set('consultation_response', $row['o_consultation_response'])
                    ->set('tmin', $row['o_tmin'])
                    ->set('message_price', (float)$row['o_message_price'])
                    ->set('minute_price', (float)$row['o_minute_price'])
                    ->set('queue_priority', (int)$row['o_queue_priority' ])
                    ->set('tquant', $row['o_tquant'])
                    ->set('period', (int)$row['o_period'])
                    ->set('tqmin', $row['o_tqmin'])
                    ->set('active', (bool)$row['o_active'])
                    ->set('need_committer', (bool)$row['o_need_committer'])
                    ->set('used', (bool)$row['o_used'])
                    ->set('line', (int)$row['o_id_line'])
                    ->setDirty(false);

                return $tariff;
            }
        }

        return null;
    }


    /**
     * Вставить Тариф на Линию Консультации
     * @return int
     */
    protected function _insert()
    {
        $result = App::getResource('FnApi')
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
            $result = App::getResource('FnApi')
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
            $result = App::getResource('FnApi')
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
        $property = 'line';
        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                $line = $this->getData()->get('line');
                $this->setProperty($property, HM_Model_Counseling_Structure_Line::load($line));
            }
        }

        return $this->getProperty($property);
    }

    /**
     * Установить ЛК для тарифа
     * TODO: Странно??
     * @param $line
     */
    public function setLine($line)
    {
        if($line instanceof HM_Model_Counseling_Structure_Line) {
            $this->setProperty('line', $line);
        } elseif (is_int($line)) {
            self::setLine(HM_Model_Counseling_Structure_Line::load($line));
        }
    }
}