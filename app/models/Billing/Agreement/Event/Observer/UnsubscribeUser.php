<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Оповестить Пользователя о добавлении его в Подписку на Договор
 */
class HM_Model_Billing_Agreement_Event_Observer_UnsubscribeUser implements SplObserver
{
    /**
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject)
    {
        $user = $subject->getUser();
        $agreement = $subject->getAgreement();

        if($user instanceof HM_Model_Account_User && $agreement instanceof HM_Model_Billing_Agreement){
            $mail = new App_Core_Mail();
            $mail->getView()->assign('user', $user);
            $mail->getView()->assign('agreement', $agreement);

            $mail->setTemplate('billing/agreement-unsubscribe-user');
            $mail->setRecipient($user->getData('email'), implode(' ', array(
                        $user->getData('last_name'),
                        $user->getData('first_name'),
                        $user->getData('middle_name')
                    )
                )
            );
            $mail->setSubject("Отписка от договора #" . $agreement->getData()->getId());
            $mail->send();
        }
    }
}
