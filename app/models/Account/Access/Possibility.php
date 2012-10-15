<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 15.10.12
 */
/**
 * ru:
 */
class HM_Model_Account_Access_Possibility extends App_Core_Model_Data_Entity
{
    private $_writable = false;

    private $_possibilityObjects = null;


    // user, role, company
    public function isWritable($id){}
    public function setType(){}

    // Восстанавливают объекты
    public function getUser(){}
    public function getRole(){}
    public function getCompany(){}

}
