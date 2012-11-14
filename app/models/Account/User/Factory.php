<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 05.03.12
 */
/**
 * ru: Фабрика сущностей Пользователь. Восстановление объекта из БД
 */
class HM_Model_Account_User_Factory extends App_Core_Model_FactoryAbstract
{
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @param int $id
     * @return HM_Model_Account_User|null
     */
    public function restore($id)
    {
        $user = null;

        if(isset($id)) {
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('user_identity', array(
                    'id' => (int)$id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $user = new HM_Model_Account_User();
                $user->getData()
                    ->set('id', $id)
                    ->set('login', $row['login'])
                    ->set('email', str_replace('#', '@', $row['email']));

                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('user_detail', array(
                        'id' => (int)$id
                    )
                );

                if($result->rowCount() > 0) {
                    $row = $result->fetchRow();
                    $user->getData()
                        ->set('first_name', $row['first_name'])
                        ->set('last_name', $row['last_name'])
                        ->set('middle_name', $row['middle_name'])
                        ->set('logo', $row['logo'])
                        ->setDirty(false);

                }
            }
        }


        return $user;
    }
}