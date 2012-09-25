<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * ru: Модель системы Авторизации и Аутендификации пользователей системы
 */
class HM_Model_Account_Auth extends App_Core_Model_ModelAbstract
{
    /**
     * Пространство имен для аутендефикации
     */
    const AUTH_NAMESPACE = 'Auth';

    /**
     * @var HM_Model_Account_Auth
     */
    protected static $_instance = null;

    /**
     * @var Zend_Auth
     */
    private $_auth = null;

    /**
     * Переопределяем родительский конструктор
     */
    public function __construct()
    {
        $this->addResource(new App_Core_Resource_DbApi(), App_Core_Resource_DbApi::RESOURCE_NAMESPACE);
        $this->addResource(new Zend_Auth_Storage_Session(self::AUTH_NAMESPACE, 'account'), 'account');
        $this->addResource(new Zend_Auth_Storage_Session(self::AUTH_NAMESPACE, 'account_settings'), 'account_settings');
    }

    /**
     * Реализация паттерна Singleton
     * @return HM_Model_Account_Auth|null
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Назначить модель авторизации
     * @return null|Zend_Auth
     */
    public function getAuth()
    {
        if (null === $this->_auth) {
            $this->setAuth();
        }
        return $this->_auth;
    }

    /**
     * Получить модель авторизации
     * @return HM_Model_Account_Auth
     */
    public function setAuth()
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage($this->getResource('account'));
        $this->_auth = $auth;
        return $this;
    }

    /**
     * Аутендефикация пользователя
     * @param string $login
     * @param string $password
     * @return bool
     */
    public function authenticate($login, $password)
    {
        $adapter = new App_Zend_Auth_Adapter_DbApi($this->getResource(App_Core_Resource_DbApi::RESOURCE_NAMESPACE));
        $adapter->setLogin($login)
            ->setPassword($password);

        return $this->getAuth()->authenticate($adapter)->isValid();
    }

    /**
     *
     * 'login'     => $userRow['login'],
     * 'token'     => md5($userRow['password'] . $userRow['login']),
     * 'is_auth'   => true,
     * 'user'      => $authRow['user_auth']
     *
     * @static
     * @return mixed|null
     */
    public function getAccount()
    {
        if(self::isAuth()) {
            return $this->getAuth()->getIdentity();
        }
        return null;
    }

    /**
     * Сохранить настройки аккаунта
     * @param mixed $settings
     * @return HM_Model_Account_Auth
     */
    public function setSettings($settings)
    {
        $this->getResource('account_settings')->write($settings);
        return $this;
    }

    /**
     * олучить настройки аккаунта
     * @return mixed
     */
    public function getSettings()
    {
        return $this->getResource('account_settings')->read();
    }

    /**
     * Назначить привилегии для аккаунта
     * @param array $possibility
     * @return HM_Model_Account_Auth
     */
    public function setPossibility(array $possibility)
    {
        $settings = $this->getSettings();
        $settings['possibility'] = $possibility;
        return $this->setSettings($settings);
    }

    /**
     * Получить привелегии для аккаунта
     * @return mixed
     */
    public function getPossibility()
    {
        $settings = $this->getSettings();
        return $settings['possibility'];
    }

    /*
     * en: Quick check on the authorization
     * ru: Быстрая проверка на авторизацию
     * @return bool
     */
    public function isAuth()
    {
        return $this->getAuth()->hasIdentity();
    }

    /*
     * ru: Разрушить сессию.
     * Жесткий выход из системы пользователя из системы
     */
    public static function destroy()
    {
        Zend_Session::destroy(true, true);
    }

    /*
     * ru: Зачистить пространство имен 'user'
     * Применяется, когда необходимо произвести очистку пространства имен пользоватля
     * Удаление настроек и профиля пользователя из сессии
     */
    public function unsetAuth()
    {
        $this->getAuth()->clearIdentity();
        $this->getResource('account_settings')->clear();
    }
}