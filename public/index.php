<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../app'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development')); // production

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../lib'),
    get_include_path()
)));

// Zend_Application
require_once 'Zend/Application.php';
require_once 'Zend/Session.php';
require_once APPLICATION_PATH . '/App.php';

$defaultNamespace = new Zend_Session_Namespace();

// Защита от угона сессии
if (!isset($defaultNamespace->initialized)) {
    Zend_Session::regenerateId();
    $defaultNamespace->initialized = true;
}

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()
            ->run();