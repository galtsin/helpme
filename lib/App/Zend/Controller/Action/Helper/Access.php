<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 *
 */
class App_Zend_Controller_Action_Helper_Access extends Zend_Controller_Action_Helper_Abstract
{
    public function getUriRoles($type)
    {
        $request = $this->getRequest();
        $roles = array();

        // Получаем запрошенный URI
        $uri = implode('/', array(
                $request->getParam('module'),
                $request->getParam('controller'),
                $request->getParam('action')
            )
        );


    }

    public function getOperationRoles()
    {
        $request = $this->getRequest();
        $roles = array();

        // Получаем запрошенный URI
        $uri = implode('/', array(
                $request->getParam('module'),
                $request->getParam('controller'),
                $request->getParam('action')
            )
        );

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
                    $roles[] = HM_Model_Account_Access::getInstance()->getRole($row['o_id_role']);
                }
            }
        }

        return $roles;
    }

    public function getPageRoles()
    {
        $request = $this->getRequest();
        $roles = array();

        // Получаем запрошенный URI
        $uri = implode('/', array(
                $request->getParam('module'),
                $request->getParam('controller'),
                $request->getParam('action')
            )
        );

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
                    $roles[] = HM_Model_Account_Access::getInstance()->getRole($row['o_id_role']);
                }
            }
        }

        return $roles;
    }

}