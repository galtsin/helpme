<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Реферальная система
 */
class App_Zend_Controller_Action_Helper_Referer extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * URL ключ для отлавливания в URL-запросах
     */
    const URL_PARAM_KEY = 'r';

    /**
     * Ключ
     * @var null
     */
    private $_hash = null;

    /**
     * Пространство имен, с которым работают рефералы
     * @var string
     */
    private $_namespace = 'referer';

    /**
     * Экземпляр сессии
     * @var null|Zend_Session_Namespace
     */
    private $_session = null;


    public function __construct()
    {
        $this->_session = new Zend_Session_Namespace($this->_namespace);
    }

    /**
     * Инициализация Referer
     * При отсутсвии параметра self::URL_PARAM_KEY в строке запроса будет сгенерирован новый хэш Referer, который будет
     * являться ключом в сессиии
     * Внимание. Данная функция является потенциальном источником проблемы преполнения сессии.
     * Оптимальный вариант использования:
     * if($this->getHelper('Referer')->hasReferer()) {
     *      $this->getHelper('Referer')->initialize();
     *      $this->getHelper('Referer')->jump();
     * }
     */
    public function initialize()
    {
        if($this->hasReferer()){
            $this->_hash = $this->getRequest()->getParam(self::URL_PARAM_KEY);
        } else {
            $this->_register();
        }
    }

    /**
     * Зарегистрировать новый Referer
     */
    private function _register()
    {
        $this->_hash = md5(date('U'));
        $this->_session->{$this->_hash} = array();
    }

    /**
     * Сделать прыжок на последний Url в адресе стека возврата
     * Внимание: переход по адресам осуществляется "как есть"
     * Т.о. пришел url вида 'module/controller/action' он не будет преобразован в 'http://domain.com/...'
     */
    public function jump()
    {
        if($this->_isInitialized()){
            $referer = $this->pop();

            // Автоудаление пустого Referer
            if(count($this->_session->{$this->_hash}) == 0){
                $this->remove();
            }

            if(is_array($referer) && count($referer) > 0) {
                $this->getActionController()
                    ->getHelper('Redirector')
                    ->gotoUrlAndExit($referer['url']);
            }
        }
    }

    /**
     * Перейти на страницу, с которой будет совершен в последующем реферал (jump)
     * @param string $url
     */
    public function go($url)
    {
        if($this->_isInitialized()){
            $url .= '/' . self::URL_PARAM_KEY . '/' . $this->_hash;
            //$this->_session->unsetAll();
            $this->getActionController()
                ->getHelper('Redirector')
                ->gotoUrlAndExit($url);
        }
    }

    /**
     * Добавить в стек
     * @param string $url
     * @param array $options
     * @return App_Zend_Controller_Action_Helper_Referer
     */
    public function push($url, array $options = array())
    {
        if($this->_isInitialized()){
            $this->_session->{$this->_hash}[] = array(
                'url'       => $url,
                'options'   => $options
            );
        }

        return $this;
    }

    /**
     * Извлечь из стека
     * @return array
     */
    public function pop()
    {
        if($this->_isInitialized()){
            return array_pop($this->_session->{$this->_hash});
        }

        return null;
    }

    /**
     * Удалить текущий Referer
     */
    public function remove()
    {
        if($this->_isInitialized()){
            unset($this->_session->{$this->_hash});
            $this->_hash = null;
        }
    }

    /**
     * Проверить, является ли Referer инициализированным
     * @return bool
     */
    private function _isInitialized()
    {
        if(null != $this->_hash){
            return true;
        }
        return false;
    }
    /**
     * Проверить существование текущего Referer (по параметру r в строке запроса)
     * domain.com/model/controller/action/r/8ec27de36034b72343e3260548cb0951
     * @return bool
     */
    public function hasReferer()
    {
        if(array_key_exists(self::URL_PARAM_KEY, $this->getRequest()->getParams())){
            if(array_key_exists($this->getRequest()->getParam(self::URL_PARAM_KEY), $this->_session->getIterator())){
                return true;
            }
        }

        return false;
    }

    /**
     * Отладчик утечки
     */
    public function debug()
    {
        //$this->_session->unsetAll();
        Zend_Debug::dump($this->_session->getIterator());
    }
}
