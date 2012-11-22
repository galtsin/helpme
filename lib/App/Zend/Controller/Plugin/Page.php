<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 *
 */
class App_Zend_Controller_Plugin_Page extends Zend_Controller_Plugin_Abstract
{

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        // Сформировать запрашиваемый URI
        $uri = implode('/', array(
                $request->getParam('module'),
                $request->getParam('controller'),
                $request->getParam('action')
            )
        );

        // Определить текущую страницу
        $currentPage = HM_Model_Account_Access::getInstance()
            ->getPages()
            ->findOneBy('privilege', $uri);

        if($currentPage instanceof Zend_Navigation_Page){

            // Загрузить разрешенные для текущей страницы Роли
            $result = App::getResource('FnApi')
                ->execute('possibility_get_roles_by_page', array(
                    'id_page' => (int)$currentPage->getId()
                )
            );

            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $rolePage = HM_Model_Account_Access::getInstance()->getRole($row['o_id_role']);
                    if($this->isAllowed($rolePage)){
                        // Отобразить страницу
                        return;
                    }
                }
            }
            // Переадресуемся на ошибку доступа
            // Только для доступных в системе страниц
            // Дальнейший доступ проверяется в модуле Доступа
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->gotoUrlAndExit('account/access/denied');
        }

        return;
    }

    /**
     * Проверка на доступ текущей роли
     * TODO: Перенести в HM_Model_Account_Access
     * @deprecated
     * @param App_Core_Model_Store_Data $role
     * @return bool
     */
    public function isAllowed(App_Core_Model_Store_Data $role)
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
