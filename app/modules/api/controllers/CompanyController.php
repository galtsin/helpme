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
     * Cписок контрагентов компании, с которыми заключен договор
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
     * В методе используются фильтры
     * filters[client] Получить договора с компанией-клиентом
     */
    public function getAgreementsAction()
    {
        $company = HM_Model_Billing_Company::load($this->_getParam('id'));
        if($company instanceof HM_Model_Billing_Company){
            $agreementColl = new HM_Model_Billing_Agreement_Collection();

            // Фильтры
            $filters = $this->_getParam('filters');
            if(!empty($filters) && count($filters) > 0) {
                foreach($filters as $filter => $value) {
                    switch(trim($filter)) {
                        case 'client':
                            $agreements = $company->getOwnerAgreements();
                            foreach($agreements as $agreement){
                                if($agreement->getData('company_client') == $value) {
                                    $agreementColl->addToCollection($agreement);
                                }
                            }
                        break;
                    }
                }
            } else {
                $agreementColl->addToCollection($company->getOwnerAgreements());
            }

            $this->setAjaxData($agreementColl->toArray());
            $this->setAjaxStatus('ok');
        }
    }

}
