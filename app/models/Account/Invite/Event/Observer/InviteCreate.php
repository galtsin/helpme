<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Обработчик события Активации пользователя
 */
class HM_Model_Account_Invite_Event_Observer_InviteCreate implements SplObserver
{
    public function update(SplSubject $subject)
    {
        $guest = $subject->getGuest();

        $mail = new App_Core_Mail();
        $mail->getView()->assign('guest', $guest);
        $mail->setTemplate('account/invite-add');
        $mail->setRecipient($guest->getData('email'), $guest->getData('last_name') . ' ' . $guest->getData('first_name') . ' ' . $guest->getData('middle_name'));
        $mail->setSubject("Приглашение в систему HELPME");
        $mail->send();
    }
}