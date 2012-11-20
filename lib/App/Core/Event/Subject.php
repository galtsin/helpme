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
     * Опции подписки
     * @var array
     */
    protected $_options = array();

    /**
     * Инициализация
     */
    public function __construct()
    {
        $this->_observers = new SplObjectStorage();
    }

    /**
     * Магический метод позволяющий получять и устанавливать опции События
     * Например: setUser(10); getUser();
     * @param string $method
     * @param mixed $params
     * @return App_Core_Event_Subject
     */
    public function __call($method, $params)
    {
        $methodType = substr($method, 0, 3);

        if($methodType == 'get') {
            return $this->_options[lcfirst(substr($method, 3))];
        } elseif ($methodType == 'set') {
            $this->_options[lcfirst(substr($method, 3))] = array_shift($params);
        }

        return $this;
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
        foreach($this->_observers as $observer) {
            $observer->update($this);
        }
    }
}
