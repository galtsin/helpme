<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Активировать подписку Гостя на Договор
 */
class HM_Model_Billing_Agreement_Event_Observer_ActivateSubscriptionGuest implements SplObserver
{
    /**
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject)
    {
        $guest = $subject->getGuest();

        if($guest instanceof HM_Model_Account_Guest){
            // Получить все инвайты
            $result = App::getResource('FnApi')
                ->execute('agreement_get_agreements_by_guest', array(
                    'id_guest' => $guest->getData()->getId()
                )
            );

            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $agreement = HM_Model_Billing_Agreement::load((int)$row['o_id_agreement']);
                    $agreement->getSubscription()
                        ->addUser($guest->getActivatedUser());
                }
            }
        }
    }
}