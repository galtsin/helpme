<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Уровень
 */
class HM_Model_Counseling_Structure_Level extends App_Core_Model_Store_Entity
{
    /**
     * Массив групп
     * @var null|array HM_Model_Counseling_Structure_Group
     */
    private $_groups = null;

    /**
     * Массив правил переадресации на ЛК
     * @var null|array App_Core_Model_Dat_Store
     */
    private $_rules = null;

    /**
     * @param int $id
     * @return HM_Model_Counseling_Structure_Level|null
     */
    public static function load($id)
    {
        $id = intval($id);
        if($id == 0 || !empty($id)) {
            $result = App::getResource('FnApi')
                ->execute('level_get_identity', array(
                    'id_level' => $id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $level = new self();
                $level->getData()
                    ->set('id', $id)
                    ->set('name', $row['o_name'])
                    ->set('line', $row['o_id_line'])
                    ->set('priority', $row['o_priority'])
                    ->setDirty(false);

                return $level;
            }
        }

        return null;
    }

    /**
     * Вставить Уровень на Линию Консультации
     * @return int
     */
    protected function _insert()
    {
        $result = App::getResource('FnApi')
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
     * Обновить уровень
     * @return int
     */
    protected function _update()
    {
        if($this->getData()->isDirty()) {
            $result = App::getResource('FnApi')
                ->execute('level_update_identity', array(
                    'id_level'  => $this->getData('id'),
                    'name'      => $this->getData('name')
                )
            );
            $row = $result->fetchRow();
            if($row['o_id_level'] !== -1) {
                return $this->getData('id');
            }
        }

        return parent::_update();
    }

    /**
     * Получить список групп
     * @return ArrayObject
     */
    public function getGroups()
    {
        if(null === $this->_groups){
            if($this->isIdentity()){
                $groupColl = new HM_Model_Counseling_Structure_Group_Collection();
                $this->_groups = $groupColl->addEqualFilter('level', $this->getData('id'))
                    ->getCollection()
                    ->getObjectsIterator();
            }
        }
        return $this->_groups;
    }

    /**
     * Добавить группу
     * @param array $options
     * @return HM_Model_Counseling_Structure_Group|null
     */
    public function addGroup(array $options)
    {
        if($this->isIdentity()){
            $group = new HM_Model_Counseling_Structure_Group();
            $group->setData($options);
            $group->getData()->set('level', $this->getData('id'));
            if($group->save()) {
                return $group;
            } else {
                unset($group);
            }
        }
        return null;
    }

    /**
     * Удалить группу
     */
    public function removeGroup(){}

    /**
     * Получить список правил переадресации на Уровень
     * @return array|null
     */
    public function getRules()
    {
        if(null === $this->_rules){
            if($this->isIdentity()){
                $rules = array();
                $result = App::getResource('FnApi')
                    ->execute('level_get_forwarding_rules', array(
                        'id_level' => $this->getData('id')
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $rule = new App_Core_Model_Store_Data();
                        $rule->set('id', (int)$row['o_id_rule'])
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
                        $result = App::getResource('FnApi')
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

}