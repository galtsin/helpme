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


    private $_inheritanceFromRole = null;

    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'accessible');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'possibility');

        $class = get_called_class(); //  Для доступа наследуемым классам
        $this->setType($class::OBJECT_TYPE);
    }

    /**
     * Установить фильтр доступа к объектам, используя права доступа и их наследование
     * Результирующая выборка произойдет по всем разрешенным правам с соединением ресурсов
     * @deprecated
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
            if($access->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier == $role->get('code')) {
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
            $this->_objectType = HM_Model_Account_Access::getInstance()
                ->getType($typeIdentifier)
                ->get('code');
        } catch (Exception $ex) {
            $class = get_called_class();
            $this->_objectType = $class::OBJECT_TYPE;
        }

        return $this;
    }

    /**
     * Фильтр ограничения объектов досутпных пользователю
     * params: array('user', 'role', 'company', 'object_type')
     * @deprecated
     * @return array
     */
    protected function _doAccessibleEqualFilterCollection()
    {
        $ids = $possibilities = array();
        $possibilityColl = new HM_Model_Account_Access_Possibility_Collection();
        if(count($this->getEqualFilterValues('accessible')) > 0) {

            foreach($this->getEqualFilterValues('accessible') as $accessible){
                $possibilityColl->addEqualFilter('urc', $accessible);
            }
            $possibilityColl->getCollection();
            foreach($possibilityColl->getObjectsIterator() as $_possibility) {
                $ids = array_merge($ids, $_possibility->getObjects(HM_Model_Account_Access::getInstance()->getType($this->_objectType)));
            }
        }

        $this->_possibilities = $possibilityColl->getObjectsIterator();
        return array_unique($ids);
    }

    /**
     * Получить объекты Привелегий
     * @deprecated
     * @return array
     */
    public function getPossibilities()
    {
        return $this->_possibilities;
    }

    /**
     * Установить отсечение ролей ниже указанной
     * @param $roleIdentifier
     */
    public function setInheritanceFromRole($roleIdentifier)
    {
        $access = HM_Model_Account_Access::getInstance();
        if($access->getRole($roleIdentifier) instanceof App_Core_Model_Data_Store) {
            $this->_inheritanceFromRole = $roleIdentifier;
        }
    }

    /**
     * TODO: Актуальная схема реализации
     * Фильтр по Possibility
     * @return array
     */
    protected function _doPossibilityEqualFilterCollection()
    {
        $ids = array();

        $access = HM_Model_Account_Access::getInstance();
        if(count($this->getEqualFilterValues('possibility')) > 0) {
            foreach($this->getEqualFilterValues('possibility') as $possibility){
                if(!$possibility instanceof HM_Model_Account_Access_Possibility_Collection) {
                    $possibilityCollection = new HM_Model_Account_Access_Possibility_Collection();
                    if($possibility instanceof HM_Model_Account_Access_Possibility) {
                        $possibilityCollection->addToCollection($possibility);
                    }
                } else {
                    $possibilityCollection = $possibility;
                }

                foreach($possibilityCollection->getObjectsIterator() as $_possibility) {
                    // Общая проверка доступности роли и ресурса без привязки к привилегиям

                    // TODO:
                    // Start
                    if(null !== $this->_inheritanceFromRole) {
                        if($access->getAcl()->inheritsRole($_possibility->getData()->getRole()->get('code'), $access->getRole($this->_inheritanceFromRole)->get('code'))
                            || $_possibility->getData()->getRole()->get('code') == $access->getRole($this->_inheritanceFromRole)->get('code')
                        ) {

                        }
                    }

/*                    if(null !== $this->_company) {

                    }*/

                    // End

                    if(    $access->getAcl()->isAllowed($_possibility->getData()->getRole()->get('code'), $this->_objectType, 'W')
                        || $access->getAcl()->isAllowed($_possibility->getData()->getRole()->get('code'), $this->_objectType, 'R')
                    ) {
                        $_objects = $_possibility->getObjects($this->_objectType);
                        foreach($_objects as $_object) {
                            $ids[] = $_object->getId();
                        }
                    }
                }
            }
        }

        return array_unique($ids);
    }

    /**
     * Переопределяем родительский метод
     */
    public function getObjectsIterator()
    {
        $access = HM_Model_Account_Access::getInstance();
        $objectsCollection = parent::getObjectsIterator();
        foreach($objectsCollection as $key => $object) {
            foreach($this->getEqualFilterValues('possibility') as $possibility){
                if(!$possibility instanceof HM_Model_Account_Access_Possibility_Collection) {
                    $possibilityCollection = new HM_Model_Account_Access_Possibility_Collection();
                    if($possibility instanceof HM_Model_Account_Access_Possibility) {
                        $possibilityCollection->addToCollection($possibility);
                    }
                } else {
                    $possibilityCollection = $possibility;
                }

                foreach($possibilityCollection->getObjectsIterator() as $_possibility) {
                    // Общая проверка доступности роли и ресурса без привязки к привилегиям
                    if($access->getAcl()->isAllowed($_possibility->getData()->getRole()->get('code'), $this->_objectType, 'W')) {
                        $object->getData()->setWritable(true);
                    } elseif($access->getAcl()->isAllowed($_possibility->getData()->getRole()->get('code'), $this->_objectType, 'R')) {
                        $object->getData()->setWritable(false);
                    } else {
                        unset($objectsCollection[$key]);
                    }
                }
            }
        }
        return $objectsCollection;
    }
}