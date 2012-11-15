<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Пользователь
 */
class HM_Model_Account_User extends App_Core_Model_Data_Entity
{
    /**
     * Восстановить сущность Пользователь
     * @param int $id
     * @return HM_Model_Account_User|null
     */
    public static function load($id)
    {
        if(isset($id)) {
            $result = App::getResource('FnApi')
                ->execute('user_identity', array(
                    'id' => (int)$id
                )
            );

            if($result->rowCount() > 0) {
                $row = $result->fetchRow();
                $user = new self();
                $user->getData()
                    ->set('id', $id)
                    ->set('login', $row['login'])
                    ->set('email', str_replace('#', '@', $row['email']));

                $result = App::getResource('FnApi')
                    ->execute('user_detail', array(
                        'id' => (int)$id
                    )
                );

                // Восстановим дополнительную информацию о пользователе
                if($result->rowCount() > 0) {
                    $row = $result->fetchRow();
                    $user->getData()
                        ->set('first_name', $row['first_name'])
                        ->set('last_name', $row['last_name'])
                        ->set('middle_name', $row['middle_name'])
                        ->set('logo', $row['logo'])
                        ->setDirty(false);
                }

                return $user;
            }
        }

        return null;
    }

    /**
     * @return int
     */
    protected function _insert()
    {
        $result = App::getResource('FnApi')
            ->execute('user_add', array(
                'login' => $this->getData('login')
            )
        );

        if($result->rowCount() > 0) {
            return (int)$result['user_add'];
        }

        return parent::_insert();
    }

    /**
     * Получить роли пользователя к привязке к компаниям в которых ему разрешено администрирование
     * Массив связок: "Роль - Список компаний". Например: array('ADMIN' => array(1,2 .. ))
     * @return array|null
     */
    public function getRoles()
    {
        $property = 'roles';
        if($this->isIdentity()){
            if(null == $this->getProperty($property)) {
                $roles = array();
                $result = App::getResource('FnApi')
                    ->execute('possibility_user_get_roles', array(
                        'id_user'       => $this->getData('id')
                    )
                );

                if($result->rowCount() > 0) {
                    foreach($result->fetchAll() as $row) {
                        $roles[$row['o_role_code']][] = (int)$row['o_id_company'];
                    }
                }

                $this->setProperty($property, $roles);
            }
        }

        return $this->getProperty($property);
    }

    /**
     * Получить коллекцию Possibility
     * @return HM_Model_Account_Access_Possibility[]|null
     */
    public function getPossibilities()
    {
        $property = 'possibilities';
        if($this->isIdentity()) {
            if(null == $this->getProperty($property)) {
                $possibilities = array();
                $companyCollection = new HM_Model_Billing_Company_Collection();
                $possibilityCollection = new HM_Model_Account_Access_Possibility_Collection();
                foreach($this->getRoles() as $roleIdentifier => $companies) {
                    foreach($companies as $company) {
                        $possibilityCollection->resetFilters();
                        $possibilityCollection->clear();
                        $possibilityCollection->addEqualFilter('urc', array(
                                'user'      => $this->getData('id'),
                                'role'      => $roleIdentifier,
                                'company'   => $company
                            )
                        );
                        // TODO: совпадение 1:1. Но на всякий случай ...
                        // Экономим на экземплярах объектов User и Company
                        foreach($possibilityCollection->getCollection()->getIdsIterator() as $id) {
                            $possibility = new HM_Model_Account_Access_Possibility();
                            $possibility->setUser($this)
                                ->setRole($roleIdentifier)
                                ->setCompany($companyCollection->load($company));
                            $possibility->getData()
                                ->set('id', $id)
                                ->setDirty(false);
                            $possibilities[] = $possibility;
                        }
                    }
                }
                $this->setProperty($property, $possibilities);
            }
        }

        return $this->getProperty($property);
    }
}