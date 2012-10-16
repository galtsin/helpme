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
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * Добавить группу на уровень
     * @return int
     */
    protected function _insert()
    {
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
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
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('group_update_identity', array(
                    'id_group'  => $this->getData('id'),
                    'name'      => $this->getData('name')
                )
            );
            $row = $result->fetchRow();
            if($row['o_id_group'] !== -1) {
                $this->getData()->unmarkDirty();
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
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('group_get_users', array(
                        'id_group'  => $this->getData('id')
                    )
                );
                if($result->rowCount() > 0){
                    foreach($result->fetchAll() as $row){
                        $experts[] = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($row['o_id_user']);
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
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
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
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
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
        return parent::remove();
    }
}