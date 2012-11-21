<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Инициирует отправку письма в случае успешной активации Гостя в системе
 */
class HM_Model_Account_Guest_Event_Observer_SuccessActivate implements SplObserver
{
    /**
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject)
    {
        $guest = $subject->getGuest();
        if($guest instanceof HM_Model_Account_Guest){
            $mail = new App_Core_Mail();
            $mail->getView()->assign('guest', $guest);
            $mail->setTemplate('account/success-activate-guest');
            $mail->setRecipient($guest->getData('email'), $guest->getData('last_name') . ' ' . $guest->getData('first_name') . ' ' . $guest->getData('middle_name'));
            $mail->setSubject("Успешная активация в системе HELPME");
            $mail->send();
        }
    }
}
