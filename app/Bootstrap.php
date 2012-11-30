<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Настройка и инициализация приложения перед запуском
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Инициализация глобальной конфигурации приложения.
     * Регистрация конфигурации в Реестре
     */
    protected function _initConfigs()
    {
        $config = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/application.ini',
            'development'
        );

        //@deprecated
        $validate = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/validate.ini'
          );

        $filterInput = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/filter-input.ini'
        );

        // Инициализация адаптера БД
        Zend_Db_Table::setDefaultAdapter(
            new Zend_Db_Adapter_Pdo_Pgsql($config->resources->db->general->params)
        );

        // Инициализация Локали
        Zend_Locale::setDefault('ru_RU');

        // Инициализация системы переводов
        $translate = new Zend_Translate(
            array(
                'adapter' => 'array',
                'content' => APPLICATION_PATH . '/languages/ru.php',
                'locale'  => 'ru_RU'
            )
        );

        // Переводчик для валидации "по умолчанию"
        Zend_Validate::setDefaultTranslator($translate);

        // Регистрируем настройки в Реестре
        Zend_Registry::set('translate', $translate);
        Zend_Registry::set('configs', $config);
        Zend_Registry::set('validate', $validate); // @deprecated
        Zend_Registry::set('filter_input', $filterInput);
        Zend_Registry::set('acl', new Zend_Acl());
    }

    /**
     * Автозагрузка ресурсов
     */
    protected function _initAutoloads()
    {
        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
            'basePath'  => APPLICATION_PATH,
            'namespace' => 'HM',
        ));

        $resourceLoader->addResourceType('models', 'models/', 'Model');

        // Добавление ресурсов к текущемим ресурсам Zend_Application_Module_Autoloader
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');

        foreach ($front->getControllerDirectory() as $module => $directory) {
            $module = ucfirst($module);
            $loader = new Zend_Application_Module_Autoloader(array(
                'namespace' => $module,
                'basePath'  => dirname($directory),
            ));

            $loader->addResourceType('validate', 'validate', 'Validate');
        }
    }

    /*
    * Инициализация плагинов
    */
    protected function _initPlugins()
    {
        $front = $this->getResource('frontController');
        // Плагин подключения слоев для Модулей
        $front->registerPlugin(new App_Zend_Controller_Plugin_Layout());
        // Плагин доступа к страницам
        //$front->registerPlugin(new App_Zend_Controller_Plugin_Access());
        // Плагин QUERY-Сервиса
        $front->registerPlugin(new App_Zend_Controller_Plugin_Query());
    }

    /**
     * Инициализация хэлперов
     */
    protected function _initHelpers()
    {
        // http://framework.zend.com/manual/1.12/en/zend.controller.actionhelpers.html
        //Zend_Controller_Action_HelperBroker::addHelper(new App_Zend_Controller_Action_Helper_Access());
        // Помощник Доступа
        Zend_Controller_Action_HelperBroker::addHelper(new App_Zend_Controller_Action_Helper_Access());
        // Помощник Переадресации
        Zend_Controller_Action_HelperBroker::addHelper(new App_Zend_Controller_Action_Helper_Referer());

    }

    /**
     * Инициализация помощников вида для контроллеров
     */
    protected function _initViewHelpers()
    {
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $layout->setLayout('default');

        $view = $layout->getView();
        $view->addHelperPath(APPLICATION_PATH . '/../lib/App/Zend/View/Helper', 'App_Zend_View_Helper_');

        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8');
        $view->headLink()->appendStylesheet($this->baseUrl.'/theme/bootstrap/css/bootstrap.css');
        $view->headLink()->appendStylesheet($this->baseUrl.'/theme/default/js/dojo/dijit/themes/dijit.css');
        $view->headLink()->appendStylesheet($this->baseUrl.'/theme/default/js/dojo/dijit/themes/hm/hm.css');
        $view->headLink()->appendStylesheet($this->baseUrl.'/theme/default/css/template.css');

        // Библиотека Dojo
        //$view->headScript()->appendFile($this->baseUrl.'/theme/default/js/dojo/dojo/dojo.js', $type = 'text/javascript', $attrs = array());
        $view->headScript()->appendFile($this->baseUrl.'/theme/default/js/dojo-release-1.8.1/dojo/dojo.js', $type = 'text/javascript', $attrs = array());
        // Шаблонизатор http://handlebarsjs.com/
        $view->headScript()->appendFile($this->baseUrl.'/theme/default/js/handlebars/base.js', $type = 'text/javascript', $attrs = array());
    }


    /**
     * Настройка Почтового транспорта
     */
    protected function _initEmailTransport()
    {
        // Настройка из глобального пространства
/*        $configOptions = $this->getOptions();
        $tr = new Zend_Application_Resource_Mail($configOptions['resources']['mail']);
        $tr->init();*/

        $transport = new Zend_Mail_Transport_Smtp('smtp.yandex.ru', array(
                'auth'  => 'login',
                'port'  => 25,
                'username'  => 'galtsin@yandex.ru',
                'password'  => 'AGzDINQz'
            )
        );

        Zend_Mail::setDefaultFrom('galtsin@yandex.ru');
        Zend_Mail::setDefaultTransport($transport);
    }

    /**
     * Инициализация ресурсов
     */
    protected function _initResources()
    {
        // Инициализация Postgres функций
        $files = glob(APPLICATION_PATH . "/configs/fn/*.xml", GLOB_BRACE);
        $config = null;
        foreach($files as $file) {
            if(null === $config) {
                $config = new Zend_Config_Xml($file, null , array('allowModifications' => true));
            } else {
                $config->merge(new Zend_Config_Xml($file));
            }
        }

        //@deprecated
        Zend_Registry::set(App_Core_Resource_DbApi::RESOURCE_NAMESPACE, $config);

        // Регистрируем ресурс в системе
        App::registerResource('FnApi', new App_Core_Resource_DbApi());
    }

    /**
     * Инициализация событий
     */
    protected function _initEvents()
    {
        $config = new Zend_Config_Xml(APPLICATION_PATH . "/configs/events.xml", null);
        $events = array();

        foreach($config->toArray() as $event => $options) {

            if(array_key_exists('subject', $options)){
                $subject = new $options['subject']();
            } else {
                $subject = new App_Core_Event_Subject();
            }

            if(is_array($options['observer'])){
                foreach($options['observer'] as $observer){
                    $subject->attach(new $observer());
                }
            } else {
                $subject->attach(new $options['observer']());
            }

            $events[$event] = $subject;
        }

        Zend_Registry::set('events', $events);
    }
}