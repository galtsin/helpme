<?php
/**
 *
 */
/**
 * Created by JetBrains PhpStorm.
 * User: GaltsinAK
 * Date: 16.03.12 8:41
 */
class App_Zend_Controller_Plugin_Access extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        // Получаем запрошенный URI
        $uri = implode('/', array(
                $request->getParam('module'),
                $request->getParam('controller'),
                $request->getParam('action')
            )
        );

        // Определяем тип запрошенного ресурса: Операция (Operation) или Страница (Page)

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
                    $rolePage = HM_Model_Account_Access::getInstance()->getRole($row['o_id_role']);
                    if($this->isAllowedRole($rolePage)){
                        // Закончить обработку
                        return;
                    }
                }
            }

            // Переадресуемся на ошибку доступа
            // Только для доступных в системе страниц
            // Дальнейший доступ проверяется в модуле Доступа
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->gotoUrlAndExit('account/access/denied');


        } else {

            // TODO: ЗАменить на хэлперы App_Zend_Controller_Action_Helper_Access
            // Определяем текущую операцию
            $currentOperation = HM_Model_Account_Access::getInstance()
                ->getOperation($uri);

            if($currentOperation instanceof App_Core_Model_Store_Data){
                // Загрузить разрешенные для текущей страницы Роли
                $result = App::getResource('FnApi')
                    ->execute('possibility_get_roles_by_operation', array(
                        'id_operation' => (int)$currentOperation->getId()
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $rolePage = HM_Model_Account_Access::getInstance()->getRole($row['o_id_role']);
                        if($this->isAllowedRole($rolePage)){
                            // Закончить обработку
                            return;
                        }
                    }
                }

                $this->getRequest()
                    ->setModuleName('account')
                    ->setControllerName('access')
                    ->setActionName('denied');

            }

        }
        // Считаем, что запрошенный ресурс не определен в системе
        // Пропускаем запрос как есть
    }

    /**
     * Проверка на доступ текущей роли
     * @param App_Core_Model_Store_Data $role
     * @return bool
     */
    public function isAllowedRole(App_Core_Model_Store_Data $role)
    {
        $acl = HM_Model_Account_Access::getInstance()->getAcl();
        $roleIdentifiers = array();

        if(HM_Model_Account_Auth::getInstance()->isAuth()){
            $account = HM_Model_Account_Auth::getInstance()->getAccount();
            $user = HM_Model_Account_User::load($account['user']);
            $roleIdentifiers = array_keys($user->getRoles());
        } else {
            $roleIdentifiers[] = 'GUEST';
        }

        foreach($roleIdentifiers as $roleIdentifier){
            if($acl->inheritsRole($roleIdentifier, $role->get('code')) || $roleIdentifier === $role->get('code')) {
                return true;
            }
        }

        return false;
    }
}