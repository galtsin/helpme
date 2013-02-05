<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 *
 */
class Api_UserController extends Service_RestController
{
    /**
     * Инициализируем модель
     */
    public function init()
    {
        parent::init();
        $this->_modelCollection = 'HM_Model_Account_User_Collection';
        $this->_modelClass = 'HM_Model_Account_User';
    }
}
