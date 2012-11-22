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
     * TODO: Переделать в HM_Model_Account_Access::getType()
     * @var string
     */
    private $_objectType = null;

    /**
     * Ограничение наследования от роли
     * @var App_Core_Model_Store_Data|null
     */
    private $_restrictionByInheritanceFromRole = null;

    /**
     * Ограничение на соответствие компании
     * @var int|null
     */
    private $_restrictionByCompany = null;

    /**
     * Привилегии доступа чтение/запись объектов
     * @var array
     */
    private $_privileges = array(
        'W' => array(),
        'R' => array()
    );

    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'possibility');
        $class = get_called_class(); //  Для доступа наследуемым классам
        $this->setType($class::OBJECT_TYPE);
    }

    /**
     * Установить тип объекта по которому идет ограничение
     * @param $typeIdentifier
     * @return self
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

/*        try {
            $this->getData()->set('role', HM_Model_Account_Access::getInstance()->getType($roleIdentifier));
        } catch(Exception $ex) {
            $this->getData()->set('role', HM_Model_Account_Access::getInstance()->getRole(HM_Model_Account_Access::EMPTY_ROLE));
        }*/

        return $this;
    }

    /**
     * Установить отсечение ролей ниже указанной
     * @param $roleIdentifier
     * @return self
     */
    public function setRestrictionByInheritanceFromRole($roleIdentifier)
    {
        $access = HM_Model_Account_Access::getInstance();
        if($access->getRole($roleIdentifier) instanceof App_Core_Model_Store_Data) {
            $this->_restrictionByInheritanceFromRole = $roleIdentifier;
        }

        return $this;
    }

    /**
     * Установить ограничение на соответствие компании
     * @param int $company
     * @return self
     */
    public function setRestrictionByCompany($company)
    {
        $this->_restrictionByCompany = (int)$company;
        return $this;
    }

    /**
     * Фильтр по Possibility
     * @return array
     */
    protected function _doPossibilityEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('possibility')) > 0) {
            foreach($this->getEqualFilterValues('possibility') as $possibility){
                if(!$possibility instanceof HM_Model_Account_Access_Possibility_Collection) {
                    $possibilityCollection = new HM_Model_Account_Access_Possibility_Collection();
                    $possibilityCollection->addToCollection($possibility);
                } else {
                    $possibilityCollection = $possibility;
                }

                foreach($possibilityCollection->getObjectsIterator() as $_possibility) {
                    if($this->_checkRestrictions($_possibility)) {
                        $_objects = $_possibility->getObjects($this->_objectType);
                        foreach($_objects as $_object) {
                            $ids[] = $_object->getId();
                            // Выставляем привилегии объектов
                            if($_object->isWritable()) {
                                $this->_privileges['W'][] = $_object->getId();
                            } else {
                                $this->_privileges['R'][] = $_object->getId();
                            }
                        }
                    }
                }
            }
        }

        return array_unique($ids);
    }

    /**
     * Проверка ограничений
     * @param HM_Model_Account_Access_Possibility $possibility
     * @return bool
     */
    private function _checkRestrictions(HM_Model_Account_Access_Possibility $possibility)
    {
        if($this->_checkRestrictionByAclAllowed($possibility)
            && $this->_checkRestrictionByCompany($possibility)
            && $this->_checkRestrictionByInheritanceFromRole($possibility)) {
            return true;
        }
        return false;
    }

    /**
     * Проверить на разрешение
     * @param HM_Model_Account_Access_Possibility $possibility
     * @return bool
     */
    private function _checkRestrictionByAclAllowed(HM_Model_Account_Access_Possibility $possibility) {
        $access = HM_Model_Account_Access::getInstance();
        if($access->getAcl()->isAllowed($possibility->getData('role')->get('code'), $this->_objectType, $possibility::WRITE)
            || $access->getAcl()->isAllowed($possibility->getData('role')->get('code'), $this->_objectType, $possibility::READ)) {
                return true;
        }
        return false;
    }

    /**
     * Проверить наследование от роли
     * @param HM_Model_Account_Access_Possibility $possibility
     * @return bool
     */
    private function _checkRestrictionByInheritanceFromRole(HM_Model_Account_Access_Possibility $possibility)
    {
        if(null !== $this->_restrictionByInheritanceFromRole) {
            $access = HM_Model_Account_Access::getInstance();
            if($access->getAcl()->inheritsRole($possibility->getData('role')->get('code'), $access->getRole($this->_restrictionByInheritanceFromRole)->get('code'))
                || $possibility->getData('role')->get('code') == $access->getRole($this->_restrictionByInheritanceFromRole)->get('code')) {
                    return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Проверить наличие ограничения на компанию
     * @param HM_Model_Account_Access_Possibility $possibility
     * @return bool
     */
    private function _checkRestrictionByCompany(HM_Model_Account_Access_Possibility $possibility)
    {

        if(null !== $this->_restrictionByCompany) {
            if($this->_restrictionByCompany == $possibility->getData('company')->getData('id')) {
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Переопределяем родительский метод
     * Пометить генерируемые из коллекции объекты на привилегии чтения/записи
     * @return array
     */
    public function getObjectsIterator()
    {
        $objectsCollection = parent::getObjectsIterator();
        foreach($objectsCollection as $object) {
            // $access->getAcl()->isAllowed($_possibility->getData('role')->get('code'), $this->_objectType, 'W')
            if(in_array($object->getData('id'), $this->_privileges['W'])) {
                $object->getData()->setWritable(true);
            } elseif (in_array($object->getData('id'), $this->_privileges['R'])) {
                $object->getData()->setWritable(false);
            }
        }
        return $objectsCollection;
    }
}