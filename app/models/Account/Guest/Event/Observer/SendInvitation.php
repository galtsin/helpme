<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Наблюдатель при создании пользователя
 * Инициирует создание и отправку письма с приглашение пользователя в Систему
 */
class HM_Model_Account_Guest_Event_Observer_SendInvitation implements SplObserver
{
    public function update(SplSubject $subject)
    {
        $guest = $subject->getGuest();
        if($guest instanceof HM_Model_Account_Guest){
            $mail = new App_Core_Mail();
            $mail->getView()->assign('guest', $guest);
            $mail->setTemplate('account/send-invitation-guest');
            $mail->setRecipient($guest->getData('email'), $guest->getData('last_name') . ' ' . $guest->getData('first_name') . ' ' . $guest->getData('middle_name'));
            $mail->setSubject("Приглашение в систему HELPME");
            $mail->send();
        }
    }
}
