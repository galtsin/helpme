<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 *
 */
class HM_Model_Billing_InviteActivation implements SplObserver
{
    public function update(SplSubject $subject)
    {
        $guest = $subject->getGuest();
        if($guest instanceof HM_Model_Account_Guest){
            // Получить все инвайты
            $result = App::getResource('FnApi')
                ->execute('agreement_get_invites_by_guest', array(
                    'id_guest' => $guest->getData()->getId()
                )
            );

            if($result->rowCount() > 0) {
                foreach($result->fetchAll() as $row) {
                    $ids[] = $row['id_agreement'];
                }
            }


        }
    }
}
