<?php
/**
 * Created by JetBrains PhpStorm.
 * User: GaltsinAK
 * Date: 16.03.12 8:41
 */
class App_Zend_Controller_Plugin_Query extends Zend_Controller_Plugin_Abstract
{

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
