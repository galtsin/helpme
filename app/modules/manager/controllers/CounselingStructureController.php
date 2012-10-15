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
     * TODO: Использует права доступа
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
                    Zend_Debug::dump($accessColl->getPossibilities());
                }
            }

        }
        $this->view->assign('data', $data);
    }

    public function groupsAction()
    {
        $groupColl = new HM_Model_Counseling_Structure_Group_Collection();
        $groups = $groupColl->addEqualFilter('company', 12)->getCollection()->getIdsIterator();
        $data = array();
        $data[] = array('company' => 12, 'groups' => $groups);
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
    public function getLineLevelsAction()
    {
        $request = $this->getRequest();
        $line = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Line_Factory')
            ->restore($request->getParam('line'));
        if($line instanceof HM_Model_Counseling_Structure_Line) {
            $this->view->assign('data', $line->getLevels());
            $this->view->assign('is_writable', true);
        }
    }

    /**
     * Получить панель управления уровнем
     */
    public function getLevelBoardAction()
    {
        $request = $this->getRequest();
        $level = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Level_Factory')
            ->restore($request->getParam('level'));
        if($level instanceof HM_Model_Counseling_Structure_Level) {
            $this->view->assign('data', $level->getData());
        }
    }

    /**
     * TODO: Супер пример для подражания
     * Отредактировать информацию о уровне
     */
    public function editLevelInfoAction()
    {
        $request = $this->getRequest();
        $level = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Level_Factory')
            ->restore($request->getParam('level'));
        if($level instanceof HM_Model_Counseling_Structure_Level){
            if($this->getRequest()->isPost()){
                if(array_key_exists('level', $request->getPost())){
                    // Предпроверка данных
                    $valid = new App_Zend_Controller_Action_Helper_Validate('level');
                    if($valid->isValid($request->getPost('level'))){
                        foreach($request->getPost('level') as $key => $value) {
                            if($key !== 'id' && $level->getData($key) !== $value) {
                                $level->getData()->set($key, $value);
                            }
                        }
                        // Сохранить данные
                        if(true === $level->save()) {
                            $this->setAjaxResult($level->getData('id'));
                        }
                    } else {
                        $this->addAjaxError($valid->getMessages(true));
                    }
                }
            } else {
                // Получить форму для редактирования данных
                $this->view->assign('data', $level->getData());
                $this->view->assign('is_writable', false);
            }
        }
    }

    /**
     * Получить список групп
     */
    public function getLevelGroupsAction()
    {
        $request = $this->getRequest();
        $level = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Level_Factory')
            ->restore($request->getParam('level'));
        if($level instanceof HM_Model_Counseling_Structure_Level) {
            $this->view->assign('data', $level->getGroups());
        }
    }

    /**
     * TODO: Пример
     * Отредактировать правила переадресации уровня
     */
    public function editLevelForwardingRulesAction()
    {
        $request = $this->getRequest();
        $level = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Level_Factory')
            ->restore($request->getParam('level'));
        if($level instanceof HM_Model_Counseling_Structure_Level) {
            if($request->isPost()){
                if(array_key_exists('rules', $request->getPost())) {
                    $rulesDirty = $request->getPost('rules');
                    $rulesOrigin = $level->getRules();
                    $rulesUpdated = array();
                    foreach($rulesDirty as $id => $params) {
                        // Дополняем значение чекбоксов формы, когда checkbox отключен
                        if(!array_key_exists('is_enabled',$params)) {
                            $params['is_enabled'] = false;
                        }

                        foreach($params as $key => $value) {
                            // Проверяем, изменились ли значения
                            if($rulesOrigin[$id]->get($key) !== $value) {
                                $rulesOrigin[$id]->set($key, $value);
                                $rulesUpdated[] = $id;
                            }
                        }
                    }
                    $level->updateRules();
                    $this->setAjaxResult($rulesUpdated);
                }
            } else {
                $this->view->assign('level', $level->getData());
                $this->view->assign('data', $level->getRules());
            }
        }
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
                $levelData = $request->getPost('level');
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

    /**
     * Добавить группу на Уровень
     */
    public function addGroupAction()
    {
        $request = $this->getRequest();
        $level = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Level_Factory')
            ->restore($request->getParam('level'));
        if($level instanceof HM_Model_Counseling_Structure_Level) {
            if($request->isPost()){
                if(array_key_exists('group', $request->getPost())) {
                    // Предпроверка данных
                    $valid = new App_Zend_Controller_Action_Helper_Validate('group');
                    if($valid->isValid($request->getPost('group'))){
                        $groupData = $request->getPost('group');
                        $group = $level->addGroup(array(
                                'name'          => $groupData['name'],
                                'company_owner' => $groupData['company_owner']
                            )
                        );
                        if($group instanceof HM_Model_Counseling_Structure_Group){
                            $this->setAjaxResult($group->getData('id'));
                            return;
                        }
                    } else {
                        $this->addAjaxError($valid->getMessages(true));
                    }
                }
            } else {

            }
        }
    }

    /**
     * Обновить информацию о группе
     */
    public function editGroupInfoAction()
    {
        $request = $this->getRequest();
        $group = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Group_Factory')
            ->restore($request->getParam('group'));
        if($group instanceof HM_Model_Counseling_Structure_Group){
            if($request->isPost()){
                if(array_key_exists('group', $request->getPost())) {
                    // Предпроверка данных
                    $validate = new App_Zend_Controller_Action_Helper_Validate('group');
                    $filterInput = new Zend_Filter_Input($validate->getFilters(), $validate->getValidators());
                    $filterInput->setData($request->getPost('group'));

                    if($filterInput->isValid()){
                        // Сохранить результаты
                        foreach($request->getPost('group') as $key => $value) {
                            if($key !== 'id' && $group->getData($key) !== $value) {
                                $group->getData()->set($key, $value);
                            }
                        }
                        if($group->save()) {
                            $this->setAjaxResult($group->getData('id'));
                        }
                    } else {
                        $this->addAjaxError($filterInput->getMessages(), 'group');
                    }
                }
            } else{
                // Форма на редактирование
                $this->view->assign('data', $group);
            }
        }
    }

    /**
     * Получить панель управления Группой
     */
    public function getGroupBoardAction()
    {
        $request = $this->getRequest();
        $group = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Group_Factory')
            ->restore($request->getParam('group'));
        if($group instanceof HM_Model_Counseling_Structure_Group){
            $this->view->assign('data', $group->getData());
        }
    }

    /**
     * Получить список специалистов в группе
     */
    public function getGroupExpertsAction()
    {
        $request = $this->getRequest();
        $group = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Group_Factory')
            ->restore($request->getParam('group'));
        if($group instanceof HM_Model_Counseling_Structure_Group){
            $this->view->assign(array('data' => $group->getExperts(), 'is_writable' => (bool)$request->getQuery('is_writable')));
        }
    }

    /**
     * Добавить специалистов в группу
     */
    public function addGroupExpertsAction()
    {
        $request = $this->getRequest();
        $group = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Group_Factory')
            ->restore($request->getParam('group'));
        if($group instanceof HM_Model_Counseling_Structure_Group){
            if($this->getRequest()->isPost()) {
                if(array_key_exists('users', $request->getPost())){
                    $attaching = array();
                    $userFactory = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory');
                    foreach($request->getPost('users') as $userId) {
                        $user = $userFactory->restore($userId);
                        if($user instanceof HM_Model_Account_User) {
                            $expert = $group->attachExpert($user);
                            if($expert != -1){
                                $attaching[] = $expert;
                            }
                        }
                    }
                    if(count($attaching) > 0) {
                        $this->setAjaxResult($attaching);
                    }
                }
            } else {

            }
        }
    }

    /**
     * Удалить специалиста из группы
     */
    public function removeGroupExpertsAction()
    {
        $request = $this->getRequest();
        $group = App_Core_Model_Factory_Manager::getFactory('HM_Model_Counseling_Structure_Group_Factory')
            ->restore($request->getParam('group'));
        if($group instanceof HM_Model_Counseling_Structure_Group){
            if($this->getRequest()->isPost()) {
                if(array_key_exists('experts', $request->getPost())){
                    $detaching = array();
                    $userFactory = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory');
                    foreach($request->getPost('experts') as $userId) {
                        $user = $userFactory->restore($userId);
                        if($user instanceof HM_Model_Account_User) {
                            $expert = $group->detachExpert($user);
                            if($expert != -1){
                                $detaching[] = $expert;
                            }
                        }
                    }
                    if(count($detaching) > 0) {
                        $this->setAjaxResult($detaching);
                    }
                }
            } else {

            }
        }
    }
}