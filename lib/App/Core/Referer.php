<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 05.05.12
 */
/**
 * TODO: Сделать добавление параметров!!
 * ru: Объект пересылок между объектами с сохранением состояния
 * Модель события в системе
 */
class App_Core_Referer
{
    /**
     * @var string Уникальный ключ referer
     */
    private $_key = null;

    /**
     * Пространство имен, с которым работают рефералы
     * @var string
     */
    private $_namespace = 'core';

    /**
     * Зарезервированное имя переменной
     * @var string
     */
    private $_refererName = 'referer';

    /**
     * Экземпляр сессии
     * @var null|Zend_Session_Namespace(
     */
    private $_session = null;

    /**
     */
    public function __construct()
    {
        $this->_session = new Zend_Session_Namespace($this->_namespace);
        // Создать пространство 'referer', если оно не существует
        if(is_null($this->_session->{$this->_refererName})) {
            $this->_session->{$this->_refererName} = array();
        }
    }

    /**
     * Получить реферал
     * @return mixed
     * @throws Exception
     */
    public function getReferer()
    {
        if(true === $this->isRegisteredKey($this->_key)) {
            return $this->_getSession()->{$this->_refererName}[$this->_key];
        }
        throw new Exception('Referer key is not registered (Ключ не зарегистрирован)');
    }

    /**
     * Сохранить реферал
     * @param $referer
     * @return App_Core_Referer
     * @throws Exception
     */
    public function setReferer($referer)
    {
        if(true === $this->isRegisteredKey($this->_key)) {
            $this->_getSession()->{$this->_refererName}[$this->_key] = $referer;
            return $this;
        }
        throw new Exception('Referer key is not registered (Ключ не зарегистрирован)');
    }

    /**
     * Проверить зарегистрирован ли ключ?
     * @param string $key
     * @return bool
     */
    public function isRegisteredKey($key)
    {
        if(array_key_exists($key, $this->_getSession()->{$this->_refererName})) {
           return true;
        }
        return false;
    }

    /**
     * Получить сессию
     * @return null|Zend_Session_Namespace
     * @throws Exception
     */
    private function _getSession()
    {
        if(!$this->_session instanceof Zend_Session_Namespace) {
            throw new Exception("Session is not defined");
        }
        return $this->_session;
    }

    /**
     * ru: Фабрика
     * @static
     * @return null|array
     */
    public static function factory()
    {
        $className = __CLASS__;
        $referer = new $className();

        try {
            $referer->registerKey(md5(date('U')));
        } catch (Exception $ex) {
            return null;
        }

        return $referer;
    }

    /**
     * ru: Удалить реферал
     */
    public function remove()
    {
        if(true === $this->isRegisteredKey($this->_key)) {
            unset($this->_getSession()->{$this->_refererName}[$this->_key]);
        }
    }


    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Зарегистрировать ключ в глобальном пространстве
     * @param int $key
     * @return App_Core_Referer
     */
    public function registerKey($key)
    {
        $this->_key = $key;
        if(false === $this->isRegisteredKey($key)) {
            $this->_getSession()->{$this->_refererName}[$key] = array();
            $this->setReferer(array(
                    'tag'       => '',
                    'url_stack' => array()
                )
            );
        }
        return $this;
    }

    /**
     * ru: Получить тег реферала
     * @return mixed
     */
    public function getTag()
    {
        $referer = $this->getReferer();
        return $referer['tag'];
    }

    /**
     * ru: Пометить реферал тегом
     * @param string $tag
     */
    public function setTag($tag)
    {
        $referer = $this->getReferer();
        $referer['tag'] = $tag;
        $this->setReferer($referer);
    }

    /**
     * ru: Добавить URI-значение в стек
     * @param string $url
     * @param array $params
     * @return App_Core_Referer
     */
    public function pushReferer($url, array $params = array())
    {
        $referer = $this->getReferer();
        array_push($referer['url_stack'], array(
                'url'       => $url,
                'params'    => $params
            )
        );
        return $this->setReferer($referer);
    }

    /**
     * ru: Вернуть последнее URI значение стека
     * @return mixed
     */
    public function popReferer()
    {
        $referer = $this->getReferer();
        $popStack = array_pop($referer['url_stack']);
        $this->setReferer($referer);
        return $popStack;
    }

    /**
     * Вернуть колчество перессылок внутри реферала
     * @return int
     */
    public function count()
    {
        $referer = $this->getReferer();
        return count($referer['url_stack']);
    }
}