<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Коллекция по сущностям Пользователь
 */
class HM_Model_Account_User_Collection extends App_Core_Model_Collection_Filter
{
    /**
     * Инициализация
     */
    protected function _init()
    {
        $this->setModelRestore('HM_Model_Account_User');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'email');
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'login');
    }

    /**
     * Фильтр по электронной почте
     * @return array
     */
    protected function _doEmailEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('email')) > 0) {
            foreach($this->getEqualFilterValues('email') as $email) {
                $result = App::getResource('FnApi')
                    ->execute('user_by_email', array(
                        'email' => $email
                    )
                );
                $row = $result->fetchRow();
                if((int)$row['user_by_email'] != -1) {
                    $ids[] = (int)$row['user_by_email'];
                }
            }
        }

        return $ids;
    }

    /**
     * Фильтр по логину
     * @return array
     */
    protected function _doLoginEqualFilterCollection()
    {
        $ids = array();

        if(count($this->getEqualFilterValues('login')) > 0) {
            foreach($this->getEqualFilterValues('login') as $login) {
                $result = App::getResource('FnApi')
                    ->execute('user_by_login', array(
                        'login' => $login
                    )
                );
                $row = $result->fetchRow();
                if((int)$row['user_by_login'] != -1) {
                    $ids[] = (int)$row['user_by_login'];
                }
            }
        }

        return $ids;
    }
}