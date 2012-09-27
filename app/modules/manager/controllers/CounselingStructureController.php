<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Управление структурой Линий Консультации: ЛК, уровни
 */
class Manager_CounselingStructureController extends App_Zend_Controller_Action
{
    /**
     * Загрузить список доступных ЛК с привязкой по компаниям
     * HTML Context
     */
    public function indexAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Узнать по какой роли стоит осуществлять поиск
        // Мы знаем URL-адрес
        $pageRole = $access->getRole('ADM_TARIFF'); // TODO: Как то нужно узнавать!
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user']);

        $accessColl = new HM_Model_Account_Access_Collection();
        $accessColl->setType('LINE')
            ->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory'));

        $data = array();

        foreach($user->getRoles() as $roleIdentifier => $companies) {
            if($access->getAcl()->inheritsRole($roleIdentifier, $pageRole->get('code')) || $roleIdentifier === $pageRole->get('code')) {
                foreach($companies as $company){
                    $accessColl->resetFilters();
                    $accessColl->setAccessFilter($user, $pageRole, $company)->getCollection();
                    $data[] = array('company' => $company, 'lines' => $accessColl->getIdsIterator());
                }
            }

        }
        $this->view->assign('data', $data);
    }

    /**
     * Управление ЛК
     * HTML Context
     */
    public function lineAction()
    {
        $lineColl = new HM_Model_Counseling_Structure_Line_Collection();
        $line = $lineColl->load((int)$this->getRequest()->getParam('id'));
    }

    /**
     * Отредактировать Линию консультации
     * Ajax Context
     */
    public function editLineAction()
    {
        if($this->getRequest()->isPost()){
            // Сохранить данные
            // Предпроверка данных
        } else {
            // Получить форму для редактирования данных
            $lineColl = new HM_Model_Counseling_Structure_Line_Collection();
            $line = $lineColl->load((int)$this->getRequest()->getParam('id'));
            $this->view->assign('data', $line->getData());
        }
    }

    public function getLevelsAction()
    {
        $levelColl = new HM_Model_Counseling_Structure_Level_Collection();
        $levelColl->addEqualFilter('line', (int)$this->getRequest()->getParam('id'))->getCollection();
        $levelColl->getDataIterator();
        $this->view->assign('data', $levelColl->getDataIterator());
    }

    public function addLevelAction(){}
    public function removeLevelAction(){}
    public function editRulesAction(){}
    public function editLevelAction(){}
    public function getGroups(){}
    public function addGroup(){}
    public function removeGroup(){}
}