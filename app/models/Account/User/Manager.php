<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Менеджер
 */
class HM_Model_Account_User_Manager extends HM_Model_Account_User
{
    /**
     * Загрузить менеджера на основании Пользователя
     * @param int $id
     * @return HM_Model_Account_User_Manager|null
     */
    public static function load($id)
    {
        $user = parent::load($id);

        if($user instanceof HM_Model_Account_User){
            $manager = new HM_Model_Account_User_Manager();
            $manager->setData($user->getData()->toArray());
            unset($user);
            return $manager;
        }

        return null;
    }
}
