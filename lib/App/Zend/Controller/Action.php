<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
class App_Zend_Controller_Action extends Zend_Controller_Action
{
    /**
     * ru: Результат выполнения
     * @var int
     */
    private $_result = -1;

    /**
     * Контейнер с данными Данные по умолчанию
     * @var array
     */
    private $_data = array();

    /**
     * ru: Статус ошибок
     * @var int
     */
    private $_error = -1;

    /**
     * ru: Статус доступности
     * @var string ok | error
     */
    private $_status = 'ok';

    /**
     * Инициализация контекста
     */
    public function preDispatch()
    {
        $this->_helper->getHelper('AjaxContext')
            ->addActionContext($this->getRequest()->getActionName(), array('json', 'html'))
            ->initContext();
    }

    /**
     * ru: Выводим сообщения в конце диспетчеризации при условии, что мы работает с AJAX-запросами
     * и типов возвращаемых данных JSON
     */
    public function postDispatch()
    {
        if($this->getRequest()->isXmlHttpRequest() && $this->_helper->getHelper('AjaxContext')->getCurrentContext() == 'json') {
            $vars = array(
                'status'=> $this->_status,
                'error' => $this->_error
            );
            if($this->getRequest()->isPost()){
                $vars['result'] = $this->_result;
            } else {
                $vars['data'] = $this->_data;
            }

        }
    }

    /**
     * ru: Установить результат ответа
     * @param $result
     * @return App_Zend_Controller_Action
     */
    public function setAjaxResult($result)
    {
        $this->_result = $result;
        return $this;
    }

    /**
     * @param $status
     * @return App_Zend_Controller_Action
     */
    public function setAjaxStatus($status)
    {
        if(in_array(strtolower($status), array('ok', 'error'))) {
            $this->_status = strtolower($status);
        }
        return $this;
    }

    /**
     * ru: Формат данных 'system' => array('textCode' => 'description')
     * @param array $error
     * @return App_Zend_Controller_Action
     */
    public function addAjaxError(array $error)
    {
        if(count($error) > 0) {
            if($this->_error == -1) {
                $this->_error = array(
                    'messages' => array()
                );
            }
            $this->_error['messages'] = array_merge_recursive((array)$this->_error['messages'], $error);
        }
        return $this;
    }

    /**
     * @param bool $finally
     */
    protected function _redirectToReferer($finally = false)
    {
        if(array_key_exists('ref', $this->getRequest()->getParams())) {
            try{
                $referer = new App_Core_Referer();
                $referer->registerKey($this->_getParam('ref'));
                $ref = $referer->popReferer();
                $this->_redirect($ref['url']);
            } catch(Exception $ex) {
                $this->_redirect($this->view->baseUrl('account/access/logout'));
            }
        } elseif ($finally == true) {
            // Homepage разруливает все правила
            $this->_redirect($this->view->baseUrl());
        }
    }
}
