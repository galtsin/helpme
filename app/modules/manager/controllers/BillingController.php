<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Управление Биллингом
 */
class Manager_BillingController extends App_Zend_Controller_Action
{
    public function agreementAction()
    {
        $request = $this->getRequest();
        if(HM_Model_Billing_Agreement::load($request->getParam('id'))){
            $this->view->assign(array(
                    'agreement' => HM_Model_Billing_Agreement::load($request->getParam('id'))
                )
            );
        }
    }

    public function agreementsAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();
        $pageRole = 'ADM_COMPANY';
        $admin = HM_Model_Account_User::load($account['user']);

        $companyColl = new HM_Model_Billing_Company_Collection();
        foreach($admin->getRoles() as $roleIdentifier => $companies) {
            if($access->getAcl()->inheritsRole($roleIdentifier, $pageRole) || $roleIdentifier == $pageRole) {
                foreach($companies as $company) {
                    $companyColl->load($company);
                }
            }
        }

        $this->view->assign('companies', $companyColl->getObjectsIterator());
    }

    public function agreements2Action()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();
        $pageRole = 'ADM_COMPANY';
        $admin = HM_Model_Account_User::load($account['user']);

        $companyColl = new HM_Model_Billing_Company_Collection();
        foreach($admin->getRoles() as $roleIdentifier => $companies) {
            if($access->getAcl()->inheritsRole($roleIdentifier, $pageRole) || $roleIdentifier == $pageRole) {
                foreach($companies as $company) {
                    $companyColl->load($company);
                }
            }
        }

        $this->view->assign('companies', $companyColl->getObjectsIterator());
    }

    public function getCompaniesClientsAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();
        $accessHelper = $this->getHelper('access');
        $admin = HM_Model_Account_User::load($account['user']);

        $companyColl = new HM_Model_Billing_Company_Collection();
        foreach($accessHelper->getUriRoles() as $operationRole){
            foreach($admin->getRoles() as $adminRoleIdentifier => $companies) {
                if($access->getAcl()->inheritsRole($adminRoleIdentifier, $operationRole->get('code')) || $adminRoleIdentifier == $operationRole->get('code')) {
                    foreach($companies as $company) {
                        $companyColl->load($company);
                    }
                }
            }
        }

        $store = array();
        foreach($companyColl->getObjectsIterator() as $companyOwner) {
            $companyClientsIds = array();
            foreach($companyOwner->getOwnerAgreements() as $agreements) {
                $companyColl->load($agreements->getData('company_client'));
                $companyClientsIds[] = $agreements->getData('company_client');
            }
            $store['companyOwnerClientBundle'][] = array(
                'owner' => $companyOwner->getData()->getId(),
                'clients' => $companyClientsIds
            );
        }
        $store['companies'] = $companyColl->toArray();
        $this->setAjaxData($store);
    }




    /**
     * Получить контрагентов компании
     * @deprecated use getCompaniesClients
     */
    public function getCompanyOwnerAgreementsAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();
        $accessHelper = $this->getHelper('access');
        $pageRole = 'ADM_COMPANY';
        $admin = HM_Model_Account_User::load($account['user']);

        $companyOwnerColl = new HM_Model_Billing_Company_Collection();
        foreach($accessHelper->getUriRoles() as $pageRole){
            foreach($admin->getRoles() as $roleIdentifier => $companies) {
                if($access->getAcl()->inheritsRole($roleIdentifier, $pageRole->get('code')) || $roleIdentifier == $pageRole->get('code')) {
                    foreach($companies as $company) {
                        $companyOwnerColl->load($company);
                    }
                }
            }
        }


        $store = array();
        foreach($companyOwnerColl->getObjectsIterator() as $companyOwner) {
            $companyClientColl = new HM_Model_Billing_Company_Collection();
            foreach($companyOwner->getOwnerAgreements() as $agreements) {
                $companyClientColl->load($agreements->getData('company_client'));
            }
            $store[] = array_merge($companyOwner->getData()->toArray(), array(
                'company_clients' => $companyClientColl->toArray()
            ));
        }
        $this->setAjaxData($store);
    }

    /**
     * Добавить новый договор
     */
    public function addAgreementAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            $agreementParams = $request->getPost('agreement');
            $agreement = new HM_Model_Billing_Agreement();
            $agreement->getData()
                ->set('tariff', $agreementParams['tariff'])
                ->set('date_end', $agreementParams['date_end']['day'] . '.' . $agreementParams['date_end']['month'] . '.' . $agreementParams['date_end']['year']);
            if(empty($agreementParams['invoice'])) {
                $companyClient = HM_Model_Billing_Company::load($request->getParam('company_client'));
                $invoice = $companyClient->addInvoice();
                if($invoice > 0) {
                    $agreement->getData()
                        ->set('invoice', $invoice);
                }
            } else {
                $agreement->getData()
                    ->set('invoice', $agreementParams['invoice']);
            }

            if($agreement->save()) {
                $this->setAjaxResult($agreement->getData()->getId());
                $this->setAjaxStatus('ok');
            }

        } else {
            $companyClient = HM_Model_Billing_Company::load($request->getParam('company_client'));
            $companyOwner = HM_Model_Billing_Company::load($request->getParam('company_owner'));
            $this->view->assign('companyClient', $companyClient);
            $this->view->assign('companyOwner', $companyOwner);
        }
    }

    /**
     * Добавить новый договор
     */
    public function createAgreementAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            $agreementParams = $request->getPost('agreement');
            $agreement = new HM_Model_Billing_Agreement();
            $agreement->getData()
                ->set('tariff', $agreementParams['tariff'])
                ->set('date_end', $agreementParams['date_end']['day'] . '.' . $agreementParams['date_end']['month'] . '.' . $agreementParams['date_end']['year']);
            if(empty($agreementParams['invoice'])) {
                $companyClient = HM_Model_Billing_Company::load($request->getParam('company_client'));
                $invoice = $companyClient->addInvoice();
                if($invoice > 0) {
                    $agreement->getData()
                        ->set('invoice', $invoice);
                }
            } else {
                $agreement->getData()
                    ->set('invoice', $agreementParams['invoice']);
            }

            if($agreement->save()) {
                $this->setAjaxResult($agreement->getData()->getId());
                $this->setAjaxStatus('ok');
            }

        } else {
            $companyClient = HM_Model_Billing_Company::load($request->getParam('company_client'));
            $companyOwner = HM_Model_Billing_Company::load($request->getParam('company_owner'));
            $this->view->assign('companyClient', $companyClient);
            $this->view->assign('companyOwner', $companyOwner);
        }
    }

    /**
     * Получить список договоров контрагента
     */
    public function getCompanyClientAgreementsAction()
    {
        $request = $this->getRequest();
        $companyColl = new HM_Model_Billing_Company_Collection();
        $companyClient = $companyColl->load($request->getParam('company_client'));
        //$companyOwner = $companyColl->load($request->getParam('company_owner'));
        $agreements = array();
        if($companyClient instanceof HM_Model_Billing_Company) {
            // Отобразить договора, которые заключены только между компанией Owner(Партнером) и Client(Контрагентом)
            foreach($companyClient->getClientAgreements() as $agreement) {
                if($agreement->getData('company_owner') == $request->getParam('company_owner')) {
                    $agreements[] = $agreement;
                }
            }
        }
        $this->view->assign('agreements', $agreements);
    }

    /**
     * Поиск организации
     */
    public function searchCompanyAction()
    {

    }

    /**
     * Загрузить панель Управление договором
     */
    public function getAgreementBoardAction()
    {
        $request = $this->getRequest();
        $agreement = HM_Model_Billing_Agreement::load($request->getParam('agreement'));
        if($agreement instanceof HM_Model_Billing_Agreement) {
            $this->view->assign('agreement', $agreement);
        }
    }

    /**
     * Загрузить подписчиков на Договор
     * TODO: getSubscription
     */
    public function getSubscribersAction()
    {
        $request = $this->getRequest();
        $agreement = HM_Model_Billing_Agreement::load($request->getParam('agreement'));
        if($agreement instanceof HM_Model_Billing_Agreement) {
            $this->view->assign('agreement', $agreement);
        }
    }

    /**
     * ФормаДобавить подписчика
     */
    public function addSubscriberAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            // Определить
            $userParams = $request->getPost('user');
            $userColl = new HM_Model_Account_User_Collection();
            if(array_key_exists('id', $userParams) && isset($userParams['id'])) {
                if($userColl->load($userParams['id']) instanceof HM_Model_Account_User) {

                }
            } else {
                // TODO: Сделать проверку на существование пользователя
            }
            $this->setAjaxResult(2);
            $this->setAjaxStatus('ok');
        } else {
            $agreement = HM_Model_Billing_Agreement::load($request->getParam('agreement'));
            if($agreement instanceof HM_Model_Billing_Agreement) {
                $this->view->assign('agreement', $agreement);
            }
        }
    }

    /**
     * Добавить Пользователя в Подписку на Договор
     */
    public function subscribeUserAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            $user = HM_Model_Account_User::load($request->getPost('user'));
            if($user instanceof HM_Model_Account_User) {
                $agreement = HM_Model_Billing_Agreement::load($request->getPost('agreement'));
                if($agreement instanceof HM_Model_Billing_Agreement){
                    if($agreement->getSubscription()->addUser($user) == $user->getData()->getId()){
                        $events = Zend_Registry::get('events');
                        $events['agreement_subscribe_user']
                            ->setUser($user)
                            ->setAgreement($agreement)
                            ->notify();

                        $this->setAjaxResult($user->getData()->getId());
                        $this->setAjaxStatus('ok');
                    }
                }
            }
        }
    }

    /**
     * Добавить Гостя в Подписку на Договор
     */
    public function subscribeGuestAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {

            if(is_array($request->getPost('guest'))) {
                // Регистрируем нового гостя
                $guestParams = $request->getPost('guest');
                $guest = new HM_Model_Account_Guest();
                $guest->getData()
                    ->set('email', $guestParams['email'])
                    ->set('first_name', $guestParams['first_name'])
                    ->set('middle_name', $guestParams['middle_name'])
                    ->set('last_name', $guestParams['last_name']);
                if($guest->save()){
                    // Отправить письмо с подпиской
                    // Оповестить наблюдателей о событии. В частности сделать рассылку
                    $events = Zend_Registry::get('events');
                    $events['account_send_register_invitation']
                        ->setGuest($guest)
                        ->notify();
                }
            } else {
                $guest = HM_Model_Account_Guest::load($request->getPost('guest'));
            }

            if($guest instanceof HM_Model_Account_Guest && $guest->isIdentity()) {
                $agreement = HM_Model_Billing_Agreement::load($request->getPost('agreement'));
                if($agreement instanceof HM_Model_Billing_Agreement){
                    if($agreement->getSubscription()->addGuest($guest) == $guest->getData()->getId()){
                        $this->setAjaxResult($guest->getData()->getId());
                        $this->setAjaxStatus('ok');
                    }
                }
            }
        }
    }

    /**
     * Исключить Пользователя из Подписки на Договор
     */
    public function unsubscribeUserAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            foreach($request->getPost('users') as $id){
                $user = HM_Model_Account_User::load($id);
                if($user instanceof HM_Model_Account_User) {
                    $agreement = HM_Model_Billing_Agreement::load($request->getPost('agreement'));
                    if($agreement instanceof HM_Model_Billing_Agreement){
                        if($agreement->getSubscription()->removeUser($user) == $user->getData()->getId()){
                            $events = Zend_Registry::get('events');
                            $events['agreement_unsubscribe_user']
                                ->setUser($user)
                                ->setAgreement($agreement)
                                ->notify();

                            $this->setAjaxResult($user->getData()->getId());
                            $this->setAjaxStatus('ok');
                        }
                    }
                }
            }
        }
    }

    /**
     * Исключить Гостя из Подписки на Договор
     */
    public function unsubscribeGuestAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            foreach($request->getPost('guests') as $id){
                $guest = HM_Model_Account_Guest::load($id);
                if($guest instanceof HM_Model_Account_Guest) {
                    $agreement = HM_Model_Billing_Agreement::load($request->getPost('agreement'));
                    if($agreement instanceof HM_Model_Billing_Agreement){
                        if($agreement->getSubscription()->removeGuest($guest) == $guest->getData()->getId()){
                            $this->setAjaxResult($guest->getData()->getId());
                            $this->setAjaxStatus('ok');
                        }
                    }
                }
            }
        }
    }

    /**
     * Повторная отправка Гостю Подписки на Договор
     */
    public function resendSubscribeGuestAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            foreach($request->getPost('guests') as $id){
                $guest = HM_Model_Account_Guest::load($id);
                if($guest instanceof HM_Model_Account_Guest) {
                    $agreement = HM_Model_Billing_Agreement::load($request->getPost('agreement'));
                    if($agreement instanceof HM_Model_Billing_Agreement){
                        $events = Zend_Registry::get('events');
                        $events['account_send_register_invitation']
                            ->setGuest($guest)
                            ->notify();
                        $this->setAjaxResult($guest->getData()->getId());
                        $this->setAjaxStatus('ok');
                    }
                }
            }
        }
    }

    /**
     * Загрузить информацию по Договору
     */
    public function editAgreementInfoAction()
    {
        $request = $this->getRequest();
        $agreement = HM_Model_Billing_Agreement::load($request->getParam('agreement'));
        if($agreement instanceof HM_Model_Billing_Agreement) {
            $this->view->assign('agreement', $agreement);
        }
    }
}