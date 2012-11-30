<?php
/**
 * Product: HELPME
 * @author: Galtsinak
 * @version: 30.11.12
 */
/**
 * ru:
 */
class Service_RestController extends Zend_Rest_Controller
{
    /**
     * Статус доступности ответа
     * @var string
     */
    private $_status = 'error';

    /**
     * Статус готовности
     */
    const STATUS_OK = 'ok';

    /**
     * Статус ошибки
     */
    const STATUS_ERROR = 'error';

    /**
     * Результат выполнения оцерации.
     * Используется для изменяющих POST, PUT, DELETE запросов
     * @var int
     */
    private $_result = -1;

    /**
     * Контейнер с данными GET-запросы
     * @var array
     */
    private $_data = array();

    /**
     * Статус ошибок
     * @var int
     */
    private $_error = array();

    /**
     * Инициализируем
     */
    public function init()
    {
        $this->getHelper('ContextSwitch')
            ->addActionContext($this->getRequest()->getActionName(), 'json')
            ->initContext('json');

        // TODO:
        // Чтобы POST-данные распозновались сервером и попадаль в $_POST необходимо указывать в заголовках
        //Content-Type: application/x-www-form-urlencoded;
    }

    /**
     * Отдать ответ
     */
    public function postDispatch()
    {
        if(strtoupper($this->getRequest()->getMethod()) == 'GET'){
            $this->view->assign('data', $this->_data);
        } else {
            $this->view->assign('result', $this->_result);
        }

        $this->view->assign(array(
                'status'    => $this->_status,
                'error'     => $this->_error
            )
        );
    }

    /**
     * Установить результат ответа
     * @param $result
     * @return App_Zend_Controller_Action
     */
    public function setAjaxResult($result)
    {
        $this->_result = $result;
        return $this;
    }

    /**
     * Установить результат ответа
     * @param $data
     * @return App_Zend_Controller_Action
     */
    public function setAjaxData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * @param $status
     * @return App_Zend_Controller_Action
     */
    public function setAjaxStatus($status)
    {
        switch(strtolower($status)) {
            case self::STATUS_OK:
                $this->_status = self::STATUS_OK;
                break;
            default:
                $this->_status = self::STATUS_ERROR;
        }

        return $this;
    }

    /**
     * Обработать запрос и передать конечному получателю
     */
    public function dispatchAction()
    {
        $method = $this->_getParam('method');
        $prefix = $this->_getParam('prefix');

        if(empty($method)){
            $method = strtolower($this->getRequest()->getMethod());
        }

        $this->_forward($method, $prefix, 'api');
    }

    public function indexAction()
    {
        echo "get";
    }

    public function postAction()
    {
        echo "post";
    }

    public function putAction()
    {
        echo "put";
    }

    public function deleteAction()
    {
        echo "delete";
    }

    public function getAction()
    {
        echo "get";
    }
}
