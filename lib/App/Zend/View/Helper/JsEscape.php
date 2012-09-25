<?php
/**
 * Helper for making easy links and getting urls that depend on the routes and router
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class App_Zend_View_Helper_JsEscape extends Zend_View_Helper_Abstract
{
    /**
     * ru: Экранировать вывод в JavaScript.
     * Удаление перевода встрок, табуляции
     *
     * @param $string
     * @return string
     */
    public function jsEscape($string)
    {
        $str = str_replace(array("\x0D\x0A", "\x0A"), array('', ''), $string);
        return $str;
    }
}
