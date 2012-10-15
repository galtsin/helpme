<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * TODO: Можно использовать как самостоятельный тип, либо в качестве родителя для наследования!!!
 * При этом необходимо установить фабрику и тип объекта
 * Фильтр позволяет отобрать только досутпные пользователю объекты
 * Чтобы ограничить объекты определенного типа, необходимо расширить коллекцию этих типов от текущей коллекции
 * Например: Тип объекта - ЛИНИЯ КОНСУЛЬТАЦИИ. Соответственно Line_Collection extends Access_Collection
 */
class HM_Model_Account_Access_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Пустой тип данных по "умолчанию"
     */
    const OBJECT_TYPE = 'EMPTY';

    /**
     * @var string
     */
    private $_objectType = null;

    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'accessible');

        $class = get_called_class(); //  Для доступа наследуемым классам
        $this->setType($class::OBJECT_TYPE);
    }

    /**
     * Установить фильтр доступа к объектам, используя права доступа и их наследование
     * Результирующая выборка произойдет по всем разрешенным правам с соединением ресурсов
     * @param HM_Model_Account_User $user
     * @param App_Core_Model_Data_Store $role Роль для которой необходимо проверить права пользователя
     * @param int $company
     * @return HM_Model_Account_Access_Collection
     */
    public function setAccessFilter(HM_Model_Account_User $user, App_Core_Model_Data_Store $role, $company)
    {
        $access = HM_Model_Account_Access::getInstance();
        $userRoles = $user->getRoles();
        foreach($userRoles as $roleIdentifier => $companies) {
            // Учитывается наследование Ролей
            if($access->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier === $role->get('code')) {
                $key = array_search($company, $companies);
                if(is_bool($key) === false){
                    $possibility = array(
                        'user'          => $user->getData('id'),
                        'role'          => $access->getRole($roleIdentifier)->getId(), // Привязка идет по рролям пользователя
                        'company'       => $company
                    );
                    $this->addEqualFilter('accessible', $possibility);
                }
            }
        }
        return $this;
    }

    /**
     * Возможность загрузить ресурсы для всех компаний.
     * Функция не безопасна из-зи $company = null - можем получить доступ ко всем компаниям
     * Установить фильтр доступа к объектам, используя права доступа и их наследование
     * Результирующая выборка произойдет по всем разрешенным правам с соединением ресурсов
     * @param HM_Model_Account_User $user
     * @param App_Core_Model_Data_Store $role
     * @param null|int $company
     * @return HM_Model_Account_Access_Collection
     */
/*    public function setAccessFilter(HM_Model_Account_User $user, App_Core_Model_Data_Store $role, $company = null)
    {
        $access = HM_Model_Account_Access::getInstance();
        $userRoles = $user->getRoles();
        foreach($userRoles as $roleIdentifier => $companies) {
            // Учитывается наследование Ролей
            if($access->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier === $role->get('code')) {
                if(null !== $company) {
                    $_companies = array();
                    if(in_array($company, $companies)) {
                        $_companies[] = $company;
                    }
                } else {
                    $_companies = $companies;
                }
                if(count($companies) > 0) {
                    foreach($_companies as $_company){
                        $possibility = array(
                            'user'          => $user->getData('id'),
                            'role'          => $access->getRole($roleIdentifier)->getId(), // Привязка идет по ролям пользователя
                            'company'       => $_company
                        );
                        $this->addEqualFilter('accessible', $possibility);
                    }
                }
            }
        }
        return $this;
    }*/

    /**
     * Установить тип объекта по которому идет ограничение
     * @param $typeIdentifier
     * @return HM_Model_Account_Access_Collection
     */
    public function setType($typeIdentifier)
    {
        try{
            $this->_objectType = HM_Model_Account_Access::getInstance()->getType($typeIdentifier)->get('code');
        } catch (Exception $ex) {
            $class = get_called_class();
            $this->_objectType = $class::OBJECT_TYPE;
        }

        return $this;
    }

    /**
     * Фильтр ограничения объектов досутпных пользователю
     * params: array('user', 'role', 'company', 'object_type')
     * @return array
     */
    protected function _doAccessibleEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('accessible')) > 0) {
            foreach($this->getEqualFilterValues('accessible') as $accessible){
                $type = HM_Model_Account_Access::getInstance()->getType($this->_objectType);
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('possibility_get_objects', array(
                        'id_user'       => $accessible['user'],
                        'id_role'       => $accessible['role'],
                        'id_company'    => $accessible['company'],
                        'id_object_type'=> $type->get('id')
                    )
                );
                if($result->rowCount() > 0){
                    foreach($result->fetchAll() as $row){
                        $ids[] = (int)$row['id_object'];
                    }
                }
            }
        }

        return array_unique($ids);
    }
}