<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Почтовый класс
 * Использует вспомогательные классы Zend_Mail, Zend_Layout, Zend_View, Zend_Registry
 */
class App_Core_Mail
{
    /**
     * Шаблон для писем
     * @var Zend_Layout
     */
    protected $_layout;

    /**
     * @var Zend_View
     */
    protected $_view;

    /**
     * @var string
     */
    protected $_subject;

    /**
     * @var array Переменные письма
     */
    protected $_templateVariables = array();

    /**
     * @var string
     */
    protected $_templateName;

    /**
     * @var Zend_Mail
     */
    protected $_mail;

    /**
     * @var array Получатели
     */
    protected $_recipient = array();

    /**
     * Инициализация
     */
    public function __construct()
    {
        $this->_mail = new Zend_Mail('utf-8');
        $this->_layout = new Zend_Layout();
        $this->_view = new Zend_View();

        $this->_init();
    }

    protected function _init()
    {
        $config = Zend_Registry::get('configs');

        // Layout Init
        $this->_layout->setLayoutPath($config->email->layoutPath);
        $this->_layout->setLayout('mail');

        // View Init
        $emailPath = $config->email->templatePath;
        $this->_view->setScriptPath($emailPath);
    }

    /**
     * Set variables for use in the templates
     *
     * @param string $name  The name of the variable to be stored
     * @param mixed  $value The value of the variable
     */
    public function __set($name, $value)
    {
        $this->_templateVariables[$name] = $value;
    }

    /**
     * Получить экземпляр Вида
     * @return Zend_View
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Set the template file to use
     *
     * @param string $filename Template filename
     */
    public function setTemplate($filename)
    {
        $this->_templateName = $filename;
    }

    /**
     * Указать тему письма
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }

    /**
     * Назначить получателя
     * @param string $email
     * @param string $name
     */
    public function setRecipient($email, $name)
    {
        $this->_recipient['email'] = $email;
        $this->_recipient['name'] = $name;
    }

    /**
     * Отправить письмо
     * @return mixed
     */
    public function send()
    {
        foreach ($this->_templateVariables as $key => $value) {
            $this->_view->assign($key, $value);
        }

        if(isset($this->_templateName)){
            $this->_layout->content = $this->_view->render($this->_templateName . '.phtml');
        }

        $this->_layout->subject = $this->_subject;
        $html = $this->_layout->render();

        $this->_mail->setBodyHtml($html);
        // TODO: брать из конфига
        //$this->_mail->setFrom('mail@helpme.ru', 'HELPME');
        $this->_mail->addTo($this->_recipient['email'], $this->_recipient['name']);
        $this->_mail->setSubject($this->_subject);
        $this->_mail->send();
    }

}
