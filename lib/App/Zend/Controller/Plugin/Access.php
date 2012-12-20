<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Проверка доступа к ресурсам системы: Страницам и Операциям (Api)
 */
final class App_Zend_Controller_Plugin_Access extends Zend_Controller_Plugin_Abstract
{
    /**
     * Доступ к ресурсу запрещен
     */
    const EXCEPTION_ACCESS_DENIED = 'EXCEPTION_ACCESS_DENIED';

    /**
     * Настройка Роутера
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->_settingRoutes();
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_handleAccessUri($request);
    }

    /**
     * Настройка маршрутов
     */
    protected function _settingRoutes()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();

        $routes = array(
            'api' => new Zend_Controller_Router_Route_Regex(
                'api/(\d+)/([a-z]+)/(?:(?:(\d+)(?:/([a-z\-]+))?|([a-z\-]+)))(?:/.*)*',
                array(
                    'module'        => 'service',
                    'controller'    => 'rest',
                    'action'        => 'dispatch'
                ),
                array(
                    1   => 'version',
                    2   => 'entity',
                    3   => 'id',
                    4   => 'operation',
                    5   => 'operation'
                )
            ),
        );

        $router->addRoutes($routes);
    }

    /**
     * Обработчик доступа к URI ресурсам
     * @param Zend_Controller_Request_Abstract $request
     */
    protected function _handleAccessUri(Zend_Controller_Request_Abstract $request)
    {
        // Если отключен вывод ошибок - отключить обработку
        $frontController = Zend_Controller_Front::getInstance();
        if ($frontController->getParam('noErrorHandler')) {
            return;
        }

        // Если обнаружено внутреннее исключение, то пропустить данную обработку
        // предоставив обработку ошибки плагину Zend_Controller_Plugin_ErrorHandler
        if(!$this->getResponse()->isException()) {
            $error = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
            $error->request = clone $request;

            // Получаем запрошенный URI
            $uri = implode('/', array(
                    $request->getModuleName(),
                    $request->getControllerName(),
                    $request->getActionName()
                )
            );

            // Если испольхуется REST архитектура
            if($uri == 'service/rest/dispatch'){
                $uriParts = array('api');
                $uriParts[] = $request->getParam('entity');
                $uriParts[] = array_key_exists('operation', $request->getParams()) ? $request->getParam('operation') : strtolower($request->getMethod());
                $uri = implode('/', $uriParts);
            }

            // Определяем текущую операцию
            $currentOperation = HM_Model_Account_Access::getInstance()
                ->getOperation($uri);

            if($currentOperation instanceof App_Core_Model_Store_Data){
                $accessHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('access');
                foreach($this->_getUriRoles($uri) as $role){
                    if($this->_isUserInheritedRole($role)){
                        // Закончить обработку
                        // В дальнейшем доступность конкретных ресурсов определяется внутри вызываемой операции (Zend_Controller_Action)
                        return;
                    }
                }
                // Error 403
                $error->exception = new HM_Model_Account_Access_Exception('Access to the resource is denied', 403);
                $error->type = self::EXCEPTION_ACCESS_DENIED;
            } else {
                // Error 404
                $error->exception = new Zend_Controller_Router_Exception('Resource not fount', 404);
                $error->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE;
            }

            $errorHandlerPlugin = Zend_Controller_Front::getInstance()->getPlugin('Zend_Controller_Plugin_ErrorHandler');

            $request->setParam('error_handler', $error)
                ->setModuleName($errorHandlerPlugin->getErrorHandlerModule())
                ->setControllerName($errorHandlerPlugin->getErrorHandlerController())
                ->setActionName($errorHandlerPlugin->getErrorHandlerAction());
                //->setDispatched(false); // Повторить диспетченизацию
        }
    }

    /**
     * Получить список разрешенных для URI ролей
     * @param string $uri
     * @return array
     */
    protected function _getUriRoles($uri)
    {
        $operation = HM_Model_Account_Access::getInstance()
            ->getOperation($uri);

        $operationRoles = array();

        if($operation instanceof App_Core_Model_Store_Data){
            $result = App::getResource('FnApi')
                ->execute('possibility_get_roles_by_operation', array(
                    'id_operation' => $operation->getId()
                )
            );
            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $operationRoles[] = HM_Model_Account_Access::getInstance()->getRole((int)$row['o_id_role']);
                }
            }
        }

        return $operationRoles;
    }

    /**
     * Проверить принадлежность Ролей текущего Пользователя к переданной Роли $role
     * @param App_Core_Model_Store_Data $role
     * @return bool
     */
    protected function _isUserInheritedRole(App_Core_Model_Store_Data $role)
    {
        $access = HM_Model_Account_Access::getInstance();
        $roleIdentifiers = array();

        if(HM_Model_Account_Auth::getInstance()->isAuth()){
            $account = HM_Model_Account_Auth::getInstance()->getAccount();
            $user = HM_Model_Account_User::load($account['user']);
            $roleIdentifiers = array_keys($user->getRoles());
        } else {
            $roleIdentifiers[] = $access::EMPTY_ROLE;
        }

        foreach($roleIdentifiers as $roleIdentifier){
            if($access->getAcl()->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier === $role->get('code')) {
                return true;
            }
        }

        return false;
    }
}