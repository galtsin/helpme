<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Линия Консультации
 */
class HM_Model_Counseling_Structure_Line extends App_Core_Model_Data_Entity
{
    /**
     * Массив правил переадресации на ЛК
     * @var null|array App_Core_Model_Dat_Store
     */
    private $_rules = null;

    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @param int $id
     * @return HM_Model_Counseling_Structure_Line|null
     */
    public static function load($id)
    {
        if(isset($id)) {
            $result = App::getResource('FnApi')
                ->execute('line_get_identity', array(
                    'id_line' => (int)$id
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
     * Добавить Уровень на Линию Консультации
     * @param array $options
     * @return HM_Model_Counseling_Structure_Level|null
     */
    public function addLevel(array $options)
    {
        if($this->isIdentity()){
            $level = new HM_Model_Counseling_Structure_Level();
            foreach($options as $key => $value) {
                $level->setData($key, $value);
            }
            $level->setData('line', $this->getData('id'));
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
     * Получить список правил переадресации на ЛК
     * @return array|null
     */
    public function getRules()
    {
        if(null === $this->_rules){
            if($this->isIdentity()){
                $rules = array();
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('line_get_forwarding_rules', array(
                        'id_line' => $this->getData('id')
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $rule = new App_Core_Model_Data_Store();
                        $rule->set('id', (int)$row['o_id_rule'])
                            ->set('level_from', $row['o_id_level_from'])
                            ->set('name_level_from', $row['o_name_level_from'])
                            ->set('level_to', $row['o_id_level_to'])
                            ->set('name_level_to', $row['o_name_level_to'])
                            ->set('duration', $row['o_duration'])
                            ->set('is_enabled', (bool)$row['o_is_enabled'])
                            ->setDirty(false);
                        $rules[$rule->getId()] = $rule;
                    }
                }
                $this->_rules = $rules;
            }
        }

        return $this->_rules;
    }

    /**
     * Обновить правила
     * @return bool
     */
    public function updateRules()
    {
        $processing = true;
        if(count($this->getRules()) > 0) {
            foreach($this->getRules() as $rule) {
                if(true === $rule->isDirty()) {
                    try{
                        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                            ->execute('level_update_forwarding_rule', array(
                                'id_rule'       => $rule->get('id'),
                                'id_duration'   => $rule->get('duration'),
                                'is_enabled'    => (bool)$rule->get('is_enabled')
                            )
                        );
                        $row = $result->fetchRow();
                        if($row['o_id_rule'] === -1) {
                            $processing = false;
                        } else {
                            $rule->setDirty(false);
                        }
                    } catch(Exception $ex) {
                        $processing = false;
                        continue;
                    }
                }
            }
        }
        return $processing;
    }

    /**
     * TODO: Пример
     * Получить список Тарифов на ЛК
     * @return HM_Model_Billing_Tariff[]|null
     */
    public function getTariffs()
    {
        $key = 'tariffs';
        if(null === $this->_getDataObject($key)) {
            if($this->isIdentity()) {
                $tariffColl = new HM_Model_Billing_Tariff_Collection();
                $tariffColl->addEqualFilter('line', $this->getData()->getId())
                    ->getCollection();
                $this->_setDataObject($key, $tariffColl->getObjectsIterator());
                $this->getData()->set($key, $tariffColl->getIdsIterator());
            }
        }
        return $this->_getDataObject($key);
    }
}