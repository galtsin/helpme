<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Пользователь
 */
class HM_Model_Account_User extends App_Core_Model_Data_Entity
{
    /**
     * Массив ролей пользователя в привязке к компаниям
     * Роль - Компании. Например: array('ADMIN' => array(1,2 .. ));
     * @var array|null
     */
    protected $_roles = null;

    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
    }

    /**
     * @return int
     */
    protected function _insert()
    {
        $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
            ->execute('user_add', array(
                'login' => $this->getData('login')
            )
        );

        if($result->rowCount() > 0) {
            return (int)$result['user_add'];
        }

        return parent::_insert();
    }

    /**
     * Получить роли пользователя к привязке к компаниям в которых ему разрешено администрирование
     * Массив связок: "Роль - Список компаний". Например: array('ADMIN' => array(1,2 .. ))
     * @return array|null
     * @throws Exception
     */
    public function getRoles()
    {
        if(null === $this->getData('id')) {
            throw new Exception(parent::DATA_STORE_NOT_FOUND);
        }

        if(null === $this->_roles) {
            $roles = array();
            $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                ->execute('possibility_user_get_roles', array(
                    'id_user'       => $this->getData('id')
                )
            );
            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $roles[$row['o_role_code']][] = (int)$row['o_id_company'];
                }
            }

            $this->_roles = $roles;
        }

        return $this->_roles;
    }
}