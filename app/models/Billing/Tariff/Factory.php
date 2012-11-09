<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Фабрика сущностей Пользователь. Восстановление объекта из БД
 */
class HM_Model_Billing_Tariff_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @param int $id
     * @return HM_Model_Billing_Tariff
     */
    public function restore($id)
    {
        $tariff = null;

        if(isset($id)) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('tarif_get_identity', array(
                    'id_tariff' => (int)$id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $tariff = new HM_Model_Billing_Tariff();
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
                    ->set('line', (int)$row['o_id_line']);
                $tariff->getData()->unmarkDirty();
            }
        }

        return $tariff;
    }
}