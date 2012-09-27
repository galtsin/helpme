<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Фабрика по Уровням
 */
class HM_Model_Counseling_Structure_Level_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @param int $id
     * @return HM_Model_Counseling_Structure_Level|null
     */
    public function restore($id)
    {
        $level = null;

        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('level_get_identity', array(
                'id_level' => (int)$id
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            $level = new HM_Model_Counseling_Structure_Level();
            $level->getData()
                ->set('id', $id)
                ->set('name', $row['o_name'])
                ->set('priority', $row['o_priority']);
        }

        return $level;
    }

}