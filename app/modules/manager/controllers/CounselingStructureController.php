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
    public function linesAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Узнать по какой роли стоит осуществлять поиск
        // Мы знаем URL-адрес
        $pageRole = $access->getRole('ADM_LINE'); // TODO: Как то нужно узнавать!
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
     * Получить панель управления Линией консультации
     */
    public function getLineBoardAction()
    {
        // Проверить принадлежность
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();

        // Узнать по какой роли стоит осуществлять поиск
        // Мы знаем URL-адрес
        $pageRole = $access->getRole('ADM_LINE'); // TODO: Как то нужно узнавать!
        $user = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')->restore($account['user']);

        $accessColl = new HM_Model_Account_Access_Collection();
        $accessColl->setType('LINE')
            ->setFactory(App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory'));

        $data = array();
        $lineColl = new HM_Model_Counseling_Structure_Line_Collection();
        $line = $lineColl->load((int)$this->getRequest()->getParam('line'));
        $this->view->assign('line', $line->getData());
    }

    /**
     * Отредактировать информацию Линию консультации
     * Ajax Context
     */
    public function editLineInfoAction()
    {
        if($this->getRequest()->isPost()){
            // Сохранить данные
            // Предпроверка данных
        } else {
            // Получить форму для редактирования данных
            $lineColl = new HM_Model_Counseling_Structure_Line_Collection();
            $line = $lineColl->load((int)$this->getRequest()->getParam('line'));
            $this->view->assign('data', $line->getData());
            $this->view->assign('is_writable', false);
        }
    }

    /**
     * Получить список Уровней Линии Консультации
     */
    public function getLevelsAction()
    {
        $levelColl = new HM_Model_Counseling_Structure_Level_Collection();
        $levelColl->addEqualFilter('line', (int)$this->getRequest()->getParam('line'))->getCollection();
        $levelColl->getDataIterator();
        $this->view->assign('data', $levelColl->getDataIterator());
        $this->view->assign('is_writable', true);
    }

    /**
     * Получить панель управления уровнем
     */
    public function getLevelBoardAction()
    {
        $request = $this->getRequest();
        $level = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Level_Factory')
            ->restore($request->getParam('level'));
        $this->view->assign('data', $level->getData());
    }

    /**
     * TODO: Пример для подражания
     * Добавить Уровень на Линию Консультации
     * POST - добавить данные
     * GET - получить форму для добавления
     */
    public function addLevelAction()
    {
        $request = $this->getRequest();
        if($request->isPost()){
            $line = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory')
                ->restore($request->getParam('line'));
            if($line instanceof HM_Model_Counseling_Structure_Line) {
                $levelData = $request->getParam('level');
                $level = $line->addLevel(array(
                        'name'  => $levelData['name']
                    )
                );
                if($level instanceof HM_Model_Counseling_Structure_Level){
                    $this->setAjaxResult($level->getData('id'));
                    return;
                }
            }
        } else {
            $this->view->assign('data', array(
                    'line'  => $request->getParam('line')
                )
            );
        }
    }

    public function removeLevelAction(){}
    public function editLevelAction(){}
    public function getGroups(){}
    public function addGroup(){}
    public function removeGroup(){}

    /**
     * TODO: Проверить и доработать
     * Отредактировать правила переадресации
     */
    public function editRulesAction()
    {
        $lineColl = new HM_Model_Counseling_Structure_Line_Collection();
        $line = $lineColl->load($this->getRequest()->getParam('line'));

        if($this->getRequest()->isPost()) {
            $rulesDirty = $this->getRequest()->getParam('rules');
            $rulesOrigin = $line->getRules();
            foreach($rulesDirty as $id => $params) {
                // Дополняем значение чекбоксов формы, когда checkbox отключен
                if(!array_key_exists('is_enabled',$params)) {
                    $params['is_enabled'] = false;
                }
                foreach($params as $key => $value) {
                    if($rulesOrigin[$id]->get($key) !== $value) {
                        $rulesOrigin[$id]->set($key, $value);
                    }
                }
            }
            $line->updateRules();
        } else {
            $this->view->assign('line', $line->getData('id'));
            $this->view->assign('data', $line->getRules());
        }
    }

}