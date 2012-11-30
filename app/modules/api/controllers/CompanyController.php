<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 *
 */
class Api_CompanyController extends Service_RestController
{
    /**
     * Cписок контрагентов компании
     */
    public function getClientsAction()
    {
        $company = HM_Model_Billing_Company::load($this->_getParam('id'));
        if($company instanceof HM_Model_Billing_Company){
            $companyColl = new HM_Model_Billing_Company_Collection();
            foreach($company->getOwnerAgreements() as $agreements) {
                $companyColl->load($agreements->getData('company_client'));
            }
            $this->setAjaxData($companyColl->toArray());
            $this->setAjaxStatus('ok');
        }
    }

    /**
     * Список договоров компании
     */
    public function getAgreementsAction()
    {
        $company = HM_Model_Billing_Company::load($this->_getParam('id'));
        if($company instanceof HM_Model_Billing_Company){
            $agreementColl = new HM_Model_Billing_Agreement_Collection();
            $agreementColl->addToCollection($company->getOwnerAgreements());
            $this->setAjaxData($agreementColl->toArray());
            $this->setAjaxStatus('ok');
        }
    }
}
