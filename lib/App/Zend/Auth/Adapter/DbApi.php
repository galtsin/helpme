<?php
/**
 *         $adapter = new App_Zend_Auth_Adapter_DbApi(new App_Core_Resource_DbApi());
$adapter->setLogin('ivan');
$adapter->setPassword('1');
$auth = Zend_Auth::getInstance();
$result = $auth->authenticate($adapter);
Zend_Debug::dump($auth->getIdentity());
//$auth->clearIdentity();
// Zend_Debug::dump($result->isValid());
 */
class App_Zend_Auth_Adapter_DbApi implements Zend_Auth_Adapter_Interface
{
    /**
     * Логин пользователя
     * @var string
     */
    private $_login;

    /**
     * Пароль пользователя
     * @var string
     */
    private $_password;

    /**
     * Ресурс App_Core_Resource_DbApi
     * @var App_Core_Resource_DbApi|null
     */
    private $_resource = null;

    /**
     * Установить логин
     * @param string $login
     * @return App_Zend_Auth_Adapter_DbApi
     */
    public function setLogin($login)
    {
        $this->_login = strtolower($login);
        return $this;
    }

    /**
     * Установить пароль
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }

    /**
     * Контсруктор
     * @param App_Core_Resource_DbApi $resource
     */
    public function __construct(App_Core_Resource_DbApi $resource)
    {
        $this->_resource = $resource;
    }

    /**
     * Аутендификация
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $authResult = $this->_resource->execute('user_auth', array(
                'login'     => $this->_login,
                'password'  => $this->_password
            )
        );
        if($authResult->rowCount() > 0) {
            $authRow = $authResult->fetchRow();
            if((int)$authRow['user_auth'] > 0) {
                $userResult = $this->_resource->execute('user_identity', array(
                        'id' => $authRow['user_auth']
                    )
                );
                if($userResult->rowCount() > 0) {
                    $userRow = $userResult->fetchRow();

                    // Регенерация хэша сессии
                    Zend_Session::rememberMe();

                    // array('account');
                    return new Zend_Auth_Result(
                        Zend_Auth_Result::SUCCESS,
                        array(
                            'login'     => $userRow['login'],
                            'token'     => md5($userRow['password'] . $userRow['login']),
                            'is_auth'   => true,
                            'user'      => $authRow['user_auth']
                        ),
                        array()
                    );
                }
            }
        }
        return new Zend_Auth_Result(
            Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
            array(),
            array('Account does not exist (Учетная запись отсутствует)')
        );
    }


}
