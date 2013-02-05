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
        $this->_modelClass = 'HM_Model_Account_User';
    }
}
