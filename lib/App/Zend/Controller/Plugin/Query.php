<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Плагин организации Query-запросов к сервису
 */
class App_Zend_Controller_Plugin_Query extends Zend_Controller_Plugin_Abstract
{
    /**
     * Настройка Роутера
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();

        $routes = array(
            'query_default' => new Zend_Controller_Router_Route(
                'service/query/:entity/*',
                array(
                    'module'        => 'service',
                    'controller'    => 'query',
                    'action'        => 'query'
                )
            )
        );

        $router->addRoutes($routes);
    }
}
