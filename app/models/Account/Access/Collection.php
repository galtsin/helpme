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
     *
     */
    private $_possibilities = array();

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
                        'role'          => $access->getRole($roleIdentifier)->getId(), // Привязка идет по ролям пользователя, а не переданной в параметре
                        'company'       => $company
                    );
                    $this->addEqualFilter('accessible', $possibility);
                }
            }
        }
        return $this;
    }

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
        $ids = $possibilities = array();

        if(count($this->getEqualFilterValues('accessible')) > 0) {
            foreach($this->getEqualFilterValues('accessible') as $accessible){
                $type = HM_Model_Account_Access::getInstance()->getType($this->_objectType);

                // Получить Possibility
                $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                    ->execute('possibility_get_identity', array(
                        'id_user'       => $accessible['user'],
                        'id_role'       => $accessible['role'],
                        'id_company'    => $accessible['company'],
                    )
                );

                if($result->rowCount() > 0){
                    $row = $result->fetchRow();
                    $possibility = new HM_Model_Account_Access_Possibility();
                    $possibility->getData()
                        ->set('id', $row['o_id_possibility'])
                        ->set('user', $accessible['user'])
                        ->set('role', $accessible['role'])
                        ->set('company', $accessible['company'])
                        ->set('type', $type->getId());

                    unset($result);
                    unset($row);

                    $possibilities[] = $possibility;

                    $result = $this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE)
                        ->execute('possibility_get_objects', array(
                            'id_possibility'    => $possibility->getData('id'),
                            'id_object_type'    => $type->get('id')
                        )
                    );

                    if($result->rowCount() > 0){
                        foreach($result->fetchAll() as $row){
                            $ids[] = (int)$row['o_id_object'];
                            'W' === $row['o_rw'] ? $possibility->addWrite((int)$row['o_id_object']) : $possibility->addRead((int)$row['o_id_object']);
                        }
                    }
                }
            }
        }

        $this->_possibilities = $possibilities;
        return array_unique($ids);
    }

    /**
     * @return array
     */
    public function getPossibilities()
    {
        return $this->_possibilities;
    }
}