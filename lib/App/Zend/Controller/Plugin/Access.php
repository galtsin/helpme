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
     * TODO: Сделать все в виде Exception наподобие ErrorHandler
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_handleAccessUri($request);
    }

    /**
     * Перенаправление на дефолтовую страницу с ошибками
     * @param Zend_Controller_Request_Abstract $request
     */
    protected function _handleAccess(Zend_Controller_Request_Abstract $request)
    {
        // Если отключен вывод ошибок - отключить обработку
        $frontController = Zend_Controller_Front::getInstance();
        if ($frontController->getParam('noErrorHandler')) {
            return;
        }

        $response = $this->getResponse();

/*        // Получаем запрошенный URI
        $uri = implode('/', array(
                $request->getModuleName(),
                $request->getControllerName(),
                $request->getActionName()
            )
        );

        // Определяем тип запрошенного ресурса: Страница (Page) или Операция (Operation)

        // Определить текущую страницу
        $currentPage = HM_Model_Account_Access::getInstance()
            ->getPages()
            ->findOneBy('privilege', $uri);

        if($currentPage instanceof Zend_Navigation_Page) {
            // Загрузить список Ролей для текущей Cтраницы
            $result = App::getResource('FnApi')
                ->execute('possibility_get_roles_by_page', array(
                    'id_page' => (int)$currentPage->getId()
                )
            );

            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $role = HM_Model_Account_Access::getInstance()->getRole($row['o_id_role']);
                    if($this->_isUserInheritedRole($role)){
                        return; // Закончить обработку
                    }
                }
            }

            // Ошибка 403
            $exception = new HM_Model_Account_Access_Exception('Access to the resource is denied', 403);

        } else {

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
                $exception = new HM_Model_Account_Access_Exception('Access to the resource is denied', 403);
            }
        }

        // Ошибка 404
        // TODO: Во время отладки приложения необходимо комментировать


        // Считаем, что запрошенный ресурс не определен в системе, а значет недоступен

        $error->exception = $exception;
        $error->type = self::EXCEPTION_ACCESS_DENIED;
        $error->request = clone $this->getRequest();

        // Forward to the error handler
        $this->getRequest()->setParam('error_handler', $error)
            ->setModuleName($errorHandlerPlugin->getErrorHandlerModule())
            ->setControllerName($errorHandlerPlugin->getErrorHandlerController())
            ->setActionName($errorHandlerPlugin->getErrorHandlerAction());*/

    }


    protected function _handleAccessUri(Zend_Controller_Request_Abstract $request)
    {
        // Если отключен вывод ошибок - отключить обработку
        $frontController = Zend_Controller_Front::getInstance();
        if ($frontController->getParam('noErrorHandler')) {
            return;
        }

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

        $request->setParam('error_handler', $error)
            ->setModuleName(Zend_Controller_Front::getInstance()->getDefaultModule())
            ->setControllerName('error')
            ->setActionName('error');
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