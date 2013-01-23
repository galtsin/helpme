<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Линия Консультации
 */
class HM_Model_Counseling_Structure_Line extends App_Core_Model_Store_Entity
{
    /**
     * @param int $id
     * @return HM_Model_Counseling_Structure_Line|null
     */
    public static function load($id)
    {
        $id = intval($id);
        if($id == 0 || !empty($id)) {
            $result = App::getResource('FnApi')
                ->execute('line_get_identity', array(
                    'id_line' => $id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $line = new self();
                $line->getData()
                    ->set('id', $id)
                    ->set('name', $row['o_name'])
                    ->set('description', $row['o_description'])
                    ->set('logo', $row['o_logo'])
                    ->set('company_owner', (int)$row['o_id_company'])
                    ->setDirty(false);

                return $line;
            }
        }

        return null;
    }

    /**
     * TODO:
     * Добавить Уровень на Линию Консультации
     * @deprecated. Сделать создание подуровня createLevel
     * @param array $options
     * @return HM_Model_Counseling_Structure_Level|null
     */
    public function addLevel(array $options)
    {
        if($this->isIdentity()){
            $level = new HM_Model_Counseling_Structure_Level();
            $options['line'] = $this->getData('id');
            $level->setData($options);
            if($level->save()) {
                return $level;
            } else {
                unset($level);
            }
        }
        return null;
    }

    public function removeLevel(){}

    /**
     * Получить массив Уровней
     * TODO: Доработать
     * @return ArrayObject
     */
    public function getLevels()
    {
        if($this->isIdentity()){
            $levelColl = new HM_Model_Counseling_Structure_Level_Collection();
            return $levelColl->addEqualFilter('line', $this->getData('id'))
                ->getCollection()
                ->getObjectsIterator();
        }
        return new ArrayObject(array());
    }

    /**
     * Получить список Тарифов на ЛК
     * @return HM_Model_Billing_Tariff[]|null
     */
    public function getTariffs()
    {
        $property = 'tariffs';
        if(null == $this->getProperty($property)) {
            if($this->isIdentity()) {
                if($this->isIdentity()) {
                    $tariffColl = new HM_Model_Billing_Tariff_Collection();
                    $tariffColl->addEqualFilter('line', $this->getData()->getId())
                        ->getCollection();
                    $this->setProperty($property, $tariffColl->getObjectsIterator());
                    $this->getData()->set($property, $tariffColl->getIdsIterator());
                }
            }
        }

        return $this->getProperty($property);
    }
}