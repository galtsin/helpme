<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Query-Сервис для получения коллекций данных
 * @deprecated
 */
class Service_QueryController extends App_Zend_Controller_Action
{
    /**
     * Список классов Коллекций Объектов
     * @var array
     */
    private $_collections;

    /**
     * Инициализация
     */
    public function init()
    {
        $this->_helper->getHelper('AjaxContext')->initContext('json');

        // TODO: Только для зарегистрированных пользователей!!!!!!!!!

        // Инициализируем коллекции, использую нотацию: Тип сущности - Класс Коллекции
        $this->_collections = array(
            'user'      => 'HM_Model_Account_User_Collection',
            'line'      => 'HM_Model_Counseling_Structure_Line_Collection',
            'level'     => 'HM_Model_Counseling_Structure_Level_Collection',
            'company'   => 'HM_Model_Billing_Company_Collection',
            'guest'     => 'HM_Model_Account_Guest_Collection'
        );
    }

    /**
     * Запросы на выборку объектов. Используются коллекции данных
     */
    public function queryAction()
    {
        $entity = $this->getRequest()->getParam('entity');
        $filters = $this->getRequest()->getParam('filters'); // filters[equal][id][]
        $possibility = $this->getRequest()->getParam('possibility'); // role:company (ADMIN:15)


        if(!empty($entity)) {
            if(array_key_exists($entity, $this->_collections)) {

                $class = $this->_collections[$entity];
                $entityCollection = new $class();

                // Поиск по фильтрам
                if(!empty($filters) && count($filters) > 0) {
                    foreach($filters as $filterType => $filter) {
                        $method = "add" . ucfirst($filterType) . "Filter";
                        if(method_exists($entityCollection, $method)) {
                            foreach($filter as $name => $values) {
                                foreach($values as $value) {
                                    if(!empty($value)) {
                                        $entityCollection->{$method}($name, trim($value));
                                    }
                                }
                            }
                        } else {
                            $this->addAjaxError(array(
                                    'app'  => array(
                                        'incorrectInputData'  => "Некорректные входные параметры. Фильтр не найден."
                                    )
                                )
                            );
                        }
                    }
                }

/*                // Accessible block
                // TODO: Проверка на доступность только те объекты которые присутствуют в системе
                if(HM_Model_Account_Access::getInstance()->getType(strtoupper($entity)) instanceof App_Core_Model_Store_Data) {
                    // Get Possibility
                    $possibilityOptions = explode(":", $possibility);
                    // Get Account
                    $account = HM_Model_Account_Auth::getInstance()->getAccount();

                    // Accessible
                    $accessibleCollection = new HM_Model_Account_Access_Collection();
                    $accessibleCollection->setType(strtoupper($entity))->setAccessFilter(
                        App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user']), // user
                        HM_Model_Account_Access::getInstance()->getRole($possibilityOptions[0]), //Role
                        $possibilityOptions[1] // company
                    );

                    // Выбрать пересечение идентификаторов
                    $result = $accessibleCollection->idsIntersect(
                        $accessibleCollection->getCollection()->getIdsIterator(),
                        $entityCollection->getCollection()->getIdsIterator()
                    );

                    // Заново заполнить коллекцию
                    $entityCollection->clear();
                    foreach($result as $id) {
                        $entityCollection->addEqualFilter('id', $id);
                    }
                }*/
                $this->setAjaxData($entityCollection->getCollection()->toArray());
            }
        }
    }
}