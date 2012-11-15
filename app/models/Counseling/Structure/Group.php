<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Группа
 */
class HM_Model_Counseling_Structure_Group extends App_Core_Model_Data_Entity
{
    /**
     * Список специалистов
     * @var null|array HM_Model_Account_User
     */
    private $_experts = null;

    /**
     * @param int $id
     * @return HM_Model_Counseling_Structure_Group|null
     */
    public static function load($id)
    {
        if(isset($id)) {
            $result = App::getResource('FnApi')
                ->execute('group_get_identity', array(
                    'id_group' => (int)$id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $group = new self();
                $group->getData()
                    ->set('id', $id)
                    ->set('name', $row['o_name'])
                    ->set('level', $row['o_id_level'])
                    ->set('company_owner', $row['o_id_company_owner'])
                    ->setDirty(false);

                return $group;
            }
        }

        return null;
    }

    /**
     * Добавить группу на уровень
     * @return int
     */
    protected function _insert()
    {
        $result = App::getResource('FnApi')
            ->execute('group_add', array(
                'id_level'          => $this->getData('level'),
                'id_company_owner'  => $this->getData('company_owner'),
                'name'              => $this->getData('name')
            )
        );

        if($result->rowCount() > 0) {
            $row = $result->fetchRow();
            return (int)$row['group_add'];
        }

        return parent::_insert();
    }

    /**
     * Обновить группу
     * @return int
     */
    protected function _update()
    {
        if($this->getData()->isDirty()) {
            $result = App::getResource('FnApi')
                ->execute('group_update_identity', array(
                    'id_group'  => $this->getData('id'),
                    'name'      => $this->getData('name')
                )
            );
            $row = $result->fetchRow();
            if($row['o_id_group'] !== -1) {
                return $this->getData('id');
            }
        }

        return parent::_update();
    }

    /**
     * Получить списов специалистов
     * @return array|null HM_Model_Account_User
     */
    public function getExperts()
    {
        if(null === $this->_experts){
            if($this->isIdentity()){
                $experts = array();
                $result = App::getResource('FnApi')
                    ->execute('group_get_users', array(
                        'id_group'  => $this->getData('id')
                    )
                );
                if($result->rowCount() > 0){
                    foreach($result->fetchAll() as $row){
                        $experts[] = HM_Model_Account_User::load($row['o_id_user']);
                    }
                }
                $this->_experts = $experts;
            }
        }
        return $this->_experts;
    }

    /**
     * Присоединить специалиста к группе
     * @param HM_Model_Account_User $user
     * @return int
     */
    public function attachExpert(HM_Model_Account_User $user)
    {
        if($this->isIdentity()){
            $result = App::getResource('FnApi')
                ->execute('group_add_user', array(
                    'id_group'  => $this->getData('id'),
                    'id_user'   => $user->getData('id')
                )
            );
            if($result->rowCount() > 0){
                $row = $result->fetchRow();
                if($row['o_id_expert'] !== -1) {
                    return (int)$row['o_id_expert'];
                }
            }
        }

        return parent::_insert();
    }

    /**
     * Исключить пользователя из группы
     * @param HM_Model_Account_User $user
     * @return int
     */
    public function detachExpert(HM_Model_Account_User $user)
    {
        if($this->isIdentity()){
            $result = App::getResource('FnApi')
                ->execute('group_delete_user', array(
                    'id_group'  => $this->getData('id'),
                    'id_user'   => $user->getData('id')
                )
            );
            if($result->rowCount() > 0){
                $row = $result->fetchRow();
                if($row['o_id_expert'] !== -1) {
                    return (int)$row['o_id_expert'];
                }
            }
        }

        return parent::_remove();
    }
}
