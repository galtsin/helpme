<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru:
 */
class HM_Model_Account_User_Manager extends HM_Model_Account_User
{
    /**
     * @var null|App_Core_Model_CollectionAbstract
     */
    private $_possibilityCollection = null;

    /**
     * Получить
     * @return App_Core_Model_CollectionAbstract|null
     */
    public function getPossibilityCollection()
    {
        if($this->isIdentity()) {
            if(null === $this->_roles) {
                $collection = new HM_Model_Account_Access_Possibility_Collection();
                foreach($this->getRoles() as $roleIdentifier => $companies) {
                    foreach($companies as $company) {
                        $collection->addEqualFilter('urc', array(
                                'user'      => $this->getData('id'),
                                'role'      => $roleIdentifier,
                                'company'   => $company
                            )
                        );
                    }
                }
                $this->_possibilityCollection = $collection->getCollection();
            }
        }
        return $this->_possibilityCollection;
    }
}
