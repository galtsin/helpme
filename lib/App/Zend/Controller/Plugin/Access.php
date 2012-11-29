<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Проверка доступа к ресурсам системы: Страницам и Операциям (Api)
 */
class App_Zend_Controller_Plugin_Access extends Zend_Controller_Plugin_Abstract
{
    const EXCEPTION_ACCESS_DENIED = 'EXCEPTION_ACCESS_DENIED';

    /**
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_handleAccessUri($request);
    }

    /**
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

            // Определяем текущую операцию
            $currentOperation = HM_Model_Account_Access::getInstance()
                ->getOperation($uri);

            if($currentOperation instanceof App_Core_Model_Store_Data){
                $accessHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('access');
                foreach($accessHelper->getUriRoles() as $role){
                    if($this->_isUserInheritedRole($role)){
                        // Закончить обработку
                        // В дальнейшем доступные ресурсы определяются внутри вызываемой операции
                        return;
                    }
                }
                // Ошибка 403
                $error->exception = new HM_Model_Account_Access_Exception('Access to the resource is denied', 403);
                $error->type = self::EXCEPTION_ACCESS_DENIED;
            } else {
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
     * Проверить принадлежность Ролей текущего Пользователя к переданной Роли $role
     * @param App_Core_Model_Store_Data $role
     * @return bool
     */
    protected function _isUserInheritedRole(App_Core_Model_Store_Data $role)
    {
        $acl = HM_Model_Account_Access::getInstance()->getAcl();
        $roleIdentifiers = array();

        if(HM_Model_Account_Auth::getInstance()->isAuth()){
            $account = HM_Model_Account_Auth::getInstance()->getAccount();
            $user = HM_Model_Account_User::load($account['user']);
            $roleIdentifiers = array_keys($user->getRoles());
        } else {
            $roleIdentifiers[] = HM_Model_Account_Access::EMPTY_ROLE;
        }

        foreach($roleIdentifiers as $roleIdentifier){
            if($acl->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier === $role->get('code')) {
                return true;
            }
        }

        return false;
    }
}