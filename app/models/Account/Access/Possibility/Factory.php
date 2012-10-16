<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 *
 */
class HM_Model_Account_Access_Possibility_Factory extends App_Core_Model_FactoryAbstract
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @param int $id
     * @return HM_Model_Account_Access_Possibility|null
     */
    public function restore($id)
    {
        $possibility = null;

        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('possibility_get_identity', array(
                'id_possibility' => (int)$id
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            $possibility = new HM_Model_Account_Access_Possibility();
            $possibility->getData()
                ->set('id', $id)
                ->set('user', $row['o_id_user'])
                ->set('company', $row['o_id_company'])
                ->set('role', $row['o_id_role']);
            $possibility->getData()->unmarkDirty();
        }

        return $possibility;
    }
}
