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
    public function agreementsAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();
        $pageRole = 'ADM_COMPANY';
        $admin = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);

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


    /**
     * Получить соглашения
     */
    public function getCompanyOwnerAgreementsAction()
    {
        // Получить текущего пользователя
        $account = HM_Model_Account_Auth::getInstance()->getAccount();
        $access = HM_Model_Account_Access::getInstance();
        $pageRole = 'ADM_COMPANY';
        $admin = App_Core_Model_Factory_Manager::getFactory('HM_Model_Account_User_Factory')
            ->restore($account['user']);

        $companyOwnerColl = new HM_Model_Billing_Company_Collection();
        foreach($admin->getRoles() as $roleIdentifier => $companies) {
            if($access->getAcl()->inheritsRole($roleIdentifier, $pageRole) || $roleIdentifier == $pageRole) {
                foreach($companies as $company) {
                    $companyOwnerColl->load($company);
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
                $companyClient = App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Company_Factory')
                    ->restore($request->getParam('company_client'));
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


            $companyClient = App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Company_Factory')
                ->restore($request->getParam('company_client'));
            $companyOwner = App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Company_Factory')
                ->restore($request->getParam('company_owner'));
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
        $agreement = App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Agreement_Factory')
            ->restore($request->getParam('agreement'));
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
        $agreement = App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Agreement_Factory')
            ->restore($request->getParam('agreement'));
        if($agreement instanceof HM_Model_Billing_Agreement) {
            $this->view->assign('agreement', $agreement);
        }
    }

    /**
     * Добавить подписчика
     */
    public function addSubscriberAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
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
            $agreement = App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Agreement_Factory')
                ->restore($request->getParam('agreement'));
            if($agreement instanceof HM_Model_Billing_Agreement) {
                $this->view->assign('agreement', $agreement);
            }
        }
    }

    /**
     * Удалить подписчика
     */
    public function removeSubscriberAction()
    {

    }

    /**
     * Загрузить информацию по Договору
     */
    public function editAgreementInfoAction()
    {
        $request = $this->getRequest();
        $agreement = App_Core_Model_Factory_Manager::getFactory('HM_Model_Billing_Agreement_Factory')
            ->restore($request->getParam('agreement'));
        if($agreement instanceof HM_Model_Billing_Agreement) {
            $this->view->assign('agreement', $agreement);
        }
    }
}