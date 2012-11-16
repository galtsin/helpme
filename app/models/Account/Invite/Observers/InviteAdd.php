<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 23.03.12
 */
/**
 * ru: Обработчик события Активации пользователя
 */
class HM_Model_Account_Invite_Observers_InviteAdd implements SplObserver
{
    public function update(SplSubject $subject)
    {
        // Получаем опции события
        $subjectOptions = $subject->getOptions();
        $guest = $subjectOptions['guest'];

        $mail = new App_Core_Mail();
        $mail->setTemplate('account/invite-add');
        $mail->assign(array(

            )
        );
        $mail->setRecipient('galtsin@gmail.com'/*$guest->getData('email')*/,
            $guest->getData('last_name') . " " . $guest->getData('first_name') . " " . $guest->getData('middle_name'));
        $mail->setSubject("Приглашение в систему HELPME");
        $mail->send();
    }
}