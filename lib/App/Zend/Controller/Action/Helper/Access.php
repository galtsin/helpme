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
    /**
     * Для операций
     */
    public function getUriRoles()
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
                    'id_operation' => $currentOperation->getId()
                )
            );

            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $roles[] = HM_Model_Account_Access::getInstance()->getRole((int)$row['o_id_role']);
                }
            }
        }

        // TODO: Можно исключить наследуемые роли т.е. выбрать минимальные роли

        return $roles;
    }
}