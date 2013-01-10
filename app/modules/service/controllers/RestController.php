<?php
/**
 * Product: HELPME
 * @author: Galtsinak
 */
/**
 * TODO: Перенести назначение статусов, данных и результатов в общий Action
 */
class Service_RestController extends Zend_Rest_Controller
{
    /**
     * Статус доступности ответа
     * Выдавать соответствующие коды и дублировать их в status переменной
     * http://dojotoolkit.org/reference-guide/1.8/quickstart/rest.html#id18
     * 200: (Ok) GET
     * 201: (Created) POST
     * 202: (Accepted) PUT
     * 204: (No Content) DELETE
     * @var string
     */
    private $_status = 'error';

    /**
     * Статус готовности
     */
    const STATUS_OK = 'ok';

    /**
     * Статус ошибки
     */
    const STATUS_ERROR = 'error';

    /**
     * Результат выполнения оцерации.
     * Используется для изменяющих POST, PUT, DELETE запросов
     * @var int
     */
    private $_result = -1;

    /**
     * Контейнер с данными GET-запросы
     * @var array
     */
    private $_data = array();

    /**
     * Статус ошибок
     * @var int
     */
    private $_error = array();

    /**
     * Назначить модель-коллекцию для запроса компаний
     * @var null|string App_Core_Model_Collection_Filter
     */
    protected $_modelCollection = null;

    /**
     * Тип данных для экземпляров объектов
     * @var null|string
     */
    protected $_type = null;

    /**
     * Инициализируем
     * Чтобы POST-данные распозновались сервером и попадаль в $_POST необходимо указывать в заголовках
     * Content-Type: application/x-www-form-urlencoded;
     * В противном случае данные необходимо будет парсить вручную из объекта getRawBody();
     */

    public function init()
    {
        $this->getHelper('ContextSwitch')
            ->addActionContext($this->getRequest()->getActionName(), 'json')
            ->initContext('json');

        // TODO:

    }

    /**
     * Отдать ответ в соответствующем виде
     */
    public function postDispatch()
    {
        if(strtoupper($this->getRequest()->getMethod()) == 'GET'){
            $this->view->assign('data', $this->_data);
        } else {
            $this->view->assign('result', $this->_result);
        }

        $this->view->assign(array(
                'status'    => $this->_status,
                'error'     => $this->_error
            )
        );
    }

    /**
     * Выполнить диспетчеризацию запроса
     */
    public function dispatchAction()
    {
        $operation = $this->_getParam('operation');
        $entity = $this->_getParam('entity');
        $request = $this->getRequest();

        if(empty($operation)){
            $operation = strtolower($this->getRequest()->getMethod());
        }

        $currentOperation = HM_Model_Account_Access::getInstance()
            ->getOperation('api' . '/' . $entity . '/' . $operation);

/*        if($this->_handleAccess($currentOperation, $this->_type)){
            $this->_forward($method, $prefix, 'api');
        } else {
            $error = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
            $error->request = clone $request;
            $error->exception = new HM_Model_Account_Access_Exception('Access to the resource is denied', 403);
            $error->type = App_Zend_Controller_Plugin_Access::EXCEPTION_ACCESS_DENIED;
            $errorHandlerPlugin = Zend_Controller_Front::getInstance()->getPlugin('Zend_Controller_Plugin_ErrorHandler');

            $request->setParam('error_handler', $error)
                ->setModuleName($errorHandlerPlugin->getErrorHandlerModule())
                ->setControllerName($errorHandlerPlugin->getErrorHandlerController())
                ->setActionName($errorHandlerPlugin->getErrorHandlerAction())
                ->setDispatched(false);
        }*/
        $this->_forward($operation, $entity, 'api');
    }

    /**
     * 1. Получаем список ролей, разрешенных для текущей операции
     * 2. Определить роли пользователя, которые проходят под ограничение ролей на шаге 1
     * 3.1 Если мы имеем дело с экземпляром конкретного объекта, то выбираем по разрешенным на шаге 2 ролям компании,
     *  и по каждой из них изпользуя связку (user + разрешенная роль + компания + тип объекта) получаем доступные экземпляры объектов
     * 3.2 Проверем входит ли идентификатор запрошенного экземпляра объекта в полученные на шаге 3.1
     * TODO: Возможно шаги 1 и 2 в случае, если мы используем плагин App_Zend_Controller_Plugin_Access
     * @param App_Core_Model_Store_Data $operation
     * @param $type
     * @return bool
     */
    private function _handleAccess(App_Core_Model_Store_Data $operation, $type)
    {
        $access = HM_Model_Account_Access::getInstance();
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $user = HM_Model_Account_User::load($account['user']);

        $result = App::getResource('FnApi')
            ->execute('possibility_get_roles_by_operation', array(
                'id_operation' => $operation->getId()
            )
        );

        if($result->rowCount() > 0) {
            foreach($result->fetchAll() as $row) {
                $operationRoleIdentifier = $access->getRole((int)$row['o_id_role'])->get('code');
                $possibilities = $user->getPossibilities();
                foreach($possibilities as $possibility){
                    $userRoleIdentifier = $access->getRole($possibility->getData('role'))->get('code');
                    if($access->getAcl()->inheritsRole($userRoleIdentifier, $operationRoleIdentifier) || $userRoleIdentifier === $operationRoleIdentifier) {
                        if($this->_getParam('id') && null !== $type){
                            // Проверка для экземпляра объекта
                            // TODO: источник возможных задержек при большом количестве данных
                            if($possibility->_has($type, $this->_getParam('id'))) {
                                return true;
                            }
                        } else {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Установить результат ответа
     * @param $result
     * @return App_Zend_Controller_Action
     */
    public function setAjaxResult($result)
    {
        $this->_result = $result;
        return $this;
    }

    /**
     * Установить результат ответа
     * @param $data
     * @return App_Zend_Controller_Action
     */
    public function setAjaxData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * @param $status
     * @return App_Zend_Controller_Action
     */
    public function setAjaxStatus($status)
    {
        switch(strtolower($status)) {
            case self::STATUS_OK:
                $this->_status = self::STATUS_OK;
                break;
            default:
                $this->_status = self::STATUS_ERROR;
        }

        return $this;
    }

    /**
     * Получить информацию о сущности
     * Метод является заглушкой для получения экземпляра объекта get
     */
    public function indexAction()
    {
        $this->_forward('get');
    }

    /**
     * Использование коллекций и фильтров для получения данных
     * App_Core_Model_CollectionAbstract
     * TODO: Использовать Content-Range: для передачи страниц страницы
     * filters[equal][company_owner][]
     */
    public function queryAction()
    {
        $modelCollection = new $this->_modelCollection();
        if(null !== $modelCollection && $modelCollection instanceof App_Core_Model_Collection_Filter){
            $filters = $this->getRequest()->getParam('filters');
            if(!empty($filters) && count($filters) > 0) {
                foreach($filters as $filterType => $filter) {
                    $method = 'add' . ucfirst(trim($filterType)) . 'Filter';
                    if(method_exists($modelCollection, $method)) {
                        foreach($filter as $name => $values) {
                            foreach($values as $value) {
                                $modelCollection->{$method}($name, trim($value));
                            }
                        }
                    }
                }
                $this->setAjaxData($modelCollection->getCollection()->toArray());
            }
            $this->setAjaxStatus(self::STATUS_OK);
        }
    }

    /**
     * Добавление экземпляра объекта
     */
    public function postAction()
    {
        //echo "post";
        $this->getResponse()->setHttpResponseCode(201);
    }

    /**
     * Обновление данных экземпляра объекта
     */
    public function putAction()
    {
        //echo "put";
        $this->getResponse()->setHttpResponseCode(202);
    }

    /**
     * Удаление экземпляра объекта
     * http://dojotoolkit.org/reference-guide/1.8/quickstart/rest.html#id18
     */
    public function deleteAction()
    {
        $this->getResponse()->setHttpResponseCode(204);
    }

    /**
     * Получить экземпляр объекта
     */
    public function getAction()
    {
        $modelCollection = new $this->_modelCollection();
        if(null !== $modelCollection && $modelCollection instanceof App_Core_Model_Collection_Filter){
            $modelCollection->load($this->_getParam('id'));
            $this->setAjaxData($modelCollection->getCollection()->toArray());
            $this->setAjaxStatus(self::STATUS_OK);
        }
    }
}
