<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Фабрика для Линий Консультации
 */
class HM_Model_Counseling_Structure_Line_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @param int $id
     * @return HM_Model_Counseling_Structure_Line|null
     */
    public function restore($id)
    {
        $line = null;

        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('line_get_identity', array(
                'id_line' => (int)$id
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            $line = new HM_Model_Counseling_Structure_Line();
            $line->getData()
                ->set('id', $id)
                ->set('name', $row['o_name'])
                ->set('description', $row['o_description'])
                ->set('logo', $row['o_logo'])
                ->set('company_owner', (int)$row['o_id_company']);
            $line->getData()->unmarkDirty();
        }

        return $line;
    }
}