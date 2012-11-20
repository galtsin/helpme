<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 20.11.12
 */
/**
 * Оповещение пользователя о добавлении его в подписку на Договор
 */
class HM_Model_Billing_Agreement_Event_Observer_SubscriptionAddUser implements SplObserver
{
    public function update(SplSubject $subject)
    {
        $user = $subject->getUser();
        $agreement = $subject->getAgreement();

        if($user instanceof HM_Model_Account_User && $agreement instanceof HM_Model_Billing_Agreement){
            $mail = new App_Core_Mail();
            $mail->getView()->assign('user', $user);
            $mail->getView()->assign('agreement', $agreement);

            $mail->setTemplate('billing/agreement-subscription-add-user');
            $mail->setRecipient($user->getData('email'), implode(' ', array(
                        $user->getData('last_name'),
                        $user->getData('first_name'),
                        $user->getData('middle_name')
                    )
                )
            );
            $mail->setSubject("Подписка на договор #" . $agreement->getData()->getId());
            $mail->send();
        }
    }
}
