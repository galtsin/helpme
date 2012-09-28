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

    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    public function addLevel(){}
    public function removeLevel(){}
    public function getLevels(){}

    /**
     * Получить список правил переадресации на ЛК
     * @return array|null
     */
    public function getRules()
    {
        if(null === $this->_rules){
            $rules = array();
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('line_get_forwarding_rules', array(
                    'id_line' => $this->getData('id')
                )
            );

            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $rule = new App_Core_Model_Data_Store();
                    $rule->set('id', (int)$row['id_rule'])
                        ->set('name_level_from', $row['name_level_from'])
                        ->set('name_level_to', $row['name_level_to'])
                        ->set('duration', $row['duration'])
                        ->set('is_enabled', (bool)$row['is_enabled']);
                    $rules[] = $rule;
                }
            }
            $this->_rules = $rules;
        }

        return $this->_rules;
    }

    /**
     * Обновить правила
     * @return bool
     */
    public function updateRules()
    {
        if(count($this->getRules()) > 0) {
            foreach($this->getRules() as $rule) {
                try{
                    $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                        ->execute('level_update_forwarding_rules', array(
                            'id_rule'       => $rule->get('id'),
                            'id_duration'   => $rule->get('duration'),
                            'is_enabled'    => (bool)$rule->get('is_enabled')
                        )
                    );
                    if($result)
                    return true;
                } catch(Exception $ex) {
                    $this->addAjaxError(array(
                            'system'    => array(
                                'fault' => 'Сбой во время сохранения информации.'
                            )
                        )
                    );
                }
            }
        }
        return false;
    }
}