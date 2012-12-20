<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * TODO: Route
 * Плагин организации Query-запросов к сервису
 * @deprecated
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
            ),
            'api_default' => new Zend_Controller_Router_Route_Regex(
                'api/(\d+)/([a-z]+)/(?:(?:(\d+)(?:/([a-z\-]+))?|([a-z\-]+)))(?:/.*)*',
                array(
                    'module'        => 'service',
                    'controller'    => 'rest',
                    'action'        => 'dispatch'
                ),
                array(
                    1   => 'version',
                    2   => 'entity',
                    3   => 'id',
                    4   => 'operation',
                    5   => 'operation'
                )
            ),
        );

        $router->addRoutes($routes);
    }
}
