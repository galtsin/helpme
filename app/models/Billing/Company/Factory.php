<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 05.03.12
 */
/**
 * ru: Фабрика сущностей Пользователь. Восстановление объекта из БД
 */
class HM_Model_Billing_Company_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        App::getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    protected function _insert(App_Core_Model_DataObject $dataObject)
    {
        $identity = $dataObject->get('identity');
        if(!empty($identity)) {
            $result = $this->getResource('postgres_api')->execute('company_add', array(
                    'name'  => $identity['name'],
                    'inn'   => $identity['inn'],
                    'kpp'   => $identity['kpp'],
                    'id_user_creator'   => $identity['user_creator']
                )
            );
            $row = $result->fetchRow();
            return (int)$row['company_add'];
        }
        return -1;
    }

    protected function _update(App_Core_Model_DataObject $dataObject)
    {

    }

    public function restore($id)
    {
        $result = $this->getResource('postgres_api')->execute('company_get_identity', array(
                'id' => (int)$id
            )
        );
        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            $entity = $this->create(array(
                    array(
                        'data'  => array(
                            'identity'  => array(
                                'name'  => $row['o_name'],
                                'inn'   => $row['o_inn'],
                                'kpp'   => $row['o_kpp'],
                                'user_creator'   => $row['o_id_creator']
                            )
                        )
                    ),
                    array(
                        'id'    => $id
                    )
                )
            );
        } else {
            $entity = null;
        }

        return $entity;
    }

    public function remove($id)
    {

    }
}