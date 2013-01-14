<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 *
 */
class Api_GuestController extends Service_RestController
{
    /**
     * Инициализируем модель
     */
    public function init()
    {
        parent::init();
        $this->_modelCollection = 'HM_Model_Account_Guest_Collection';
    }

    /**
     * Создать Гостя
     */
    public function postAction()
    {
        $request = $this->getRequest();
        $guestParams = $request->getParam('guest');
        $guest = new HM_Model_Account_Guest();
        $guest->getData()
            ->set('email', $guestParams['email'])
            ->set('first_name', $guestParams['first_name'])
            ->set('middle_name', $guestParams['middle_name'])
            ->set('last_name', $guestParams['last_name']);

        if($guest->save()){
            $events = Zend_Registry::get('events');
            $events['account_send_register_invitation']
                ->setGuest($guest)
                ->notify();
            $this->setAjaxResult($guest->getData()->getId());
            $this->setAjaxStatus(self::STATUS_OK);
        }

        parent::postAction();
    }
}
