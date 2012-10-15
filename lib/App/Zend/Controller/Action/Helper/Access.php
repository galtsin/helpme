<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 15.10.12
 */
/**
 * ru:
 */
class App_Zend_Controller_Action_Helper_Access extends Zend_Controller_Action_Helper_Abstract
{
    public function _t($entities, HM_Model_Account_Access_Possibility $possibility)
    {
        $_possibility = $possibility->getData('possibility');
        foreach($entities as $entity) {
            if(in_array($entity->get('id'), $_possibility['read'])) {
                $entity->setWritable(false);
            }
        }
    }
}