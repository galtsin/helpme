<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Реализация интерфейса SplSubject библиотеки SPL
 * Модель событий
 */
class App_Core_Event_Subject implements SplSubject
{
    /**
     * Список наблюдателей
     * @var SplObjectStorage
     */
    protected $_observers;

    /**
     * Название события
     * @var string
     */
    protected $_name;

    /**
     * Параметры события
     * @var array
     */
    protected $_options = array();

    /**
     * Заблокировать событие. Отложить оповещение
     * Метод notify не срабатывает
     * @var bool
     */
    protected $_lock = false;

    /**
     * @param string
     */
    public function __construct($name)
    {
        $this->_observers = new SplObjectStorage();
        $this->_name = $name;
    }

    /**
     * Вернуть имя события
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Присоединить наблюдателя
     * @param SplObserver $observer
     */
    public function attach(SplObserver $observer)
    {
        $this->_observers->attach($observer);
    }

    /**
     * Удалить наблюдателя
     * @param SplObserver $observer
     */
    public function detach(SplObserver $observer)
    {
        $this->_observers->detach($observer);
    }

    /**
     * Оповестить наблюдателей о событии
     */
    public function notify()
    {
        if(false == $this->_lock) {
            foreach($this->_observers as $observer) {
                $observer->update($this);
            }
        }
    }

    /**
     * en: Set options for Observers
     * Attention! The merger follows the rules of the function array_merge_recursive
     * It is possible duplicate records
     *
     * ru: Установить переменные(параметры) для наблюдателей
     * Внимание! Слияние идет по правилам массива
     * Возможно дублирование записей
     *
     * @param array $option
     * @return App_Core_Event_Subject
     */
    public function setOption(array $option)
    {
        $this->_options = array_merge_recursive((array)$option, (array)$this->_options);
        return $this;
    }

    /**
     * ru: Получить параметры события
     * @return mixed
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * ru: Заблокировать оповещение
     */
    public function lock()
    {
        $this->_lock = true;
    }

    /**
     * ru: Разблокировать оповещение
     */
    public function unlock()
    {
        $this->_lock = false;
    }

    /**
     * ru: Получить наблюдателя по имени класса
     * @param string $className
     * @return SplObserver
     */
    public function getObserver($className)
    {
        foreach($this->_observers as $observer) {
            if($observer instanceof $className) {
                return $observer;
            }
        }

        return null;
    }
}
