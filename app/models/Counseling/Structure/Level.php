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
     */
    public function addGroup(){}

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
            $rules = array();
            if($this->isIdentity()){
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('line_get_forwarding_rules', array(
                        'id_line' => $this->getData('id')
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        if($this->getData('id') === (int)$row['o_id_level_from']) {
                            $rule = new App_Core_Model_Data_Store();
                            $rule->set('id', (int)$row['o_id_rule'])
                                ->set('level_from', $row['o_id_level_from'])
                                ->set('name_level_from', $row['o_name_level_from'])
                                ->set('level_to', $row['o_id_level_to'])
                                ->set('name_level_to', $row['o_name_level_to'])
                                ->set('duration', $row['o_duration'])
                                ->set('is_enabled', (bool)$row['o_is_enabled'])
                                ->unmarkDirty();
                            $rules[$rule->getId()] = $rule;
                        }
                    }
                }
                $this->_rules = $rules;
            }
        }
        return $this->_rules;
    }

}