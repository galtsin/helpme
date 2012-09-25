<?php
/**
 * Zend_Form
 *
 * @category   Zend
 * @package    Zend_Form
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 24156 2011-06-27 14:57:44Z ezimuel $
 */
class App_Zend_Form extends Zend_Form
{
    /**
     * ru: Патч, согласующий имена субформ с их названиями belongTo
     *
     * @param Zend_Form $form
     * @param $name
     * @param null $order
     * @return App_Zend_Form
     */
    public function addSubForm(Zend_Form $form, $name, $order = null)
    {
        parent::addSubForm($form, $name, $order);
        $form->setName($name)->setElementsBelongTo($name);
        return $this;
    }

    /**
     * ru: Получить массив ошибок полей формы для использования в AJAX ответах
     * @return array
     */
    public function getAjaxMessages()
    {
        $ar = array();
        $msg = $this->getMessages();
        if(count($msg) > 0) {
            $this->prepare($msg[$this->getElementsBelongTo()], '', $ar, $this);
        }
        return $ar;
    }

    /*
     * ru: Подготовка сообщений
     */
    function prepare($msg, $label, &$ar, $form)
    {
        if($label == '') {
            $belong = $form->getElementsBelongTo();
        } else {
            $belong = $label . "[". $form->getElementsBelongTo() . "]";
        }

        foreach($msg as $name => $value) {
            if($form->getSubForm($name)) {
                $this->prepare($msg[$name], $belong, $ar, $form->getSubForm($name));
            } else {
                $ar[$belong . '[' . $name . ']'] = $form->getElement($name)->getMessages();
            }
        }
        return $ar;
    }
}
