<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 * Базовая фильтрация по идентификаторам сущностей
 */
class App_Core_Model_Collection_Filter extends App_Core_Model_CollectionAbstract
{
    /**
     * "Точное совпадение"
     */
    const EQUAL_FILTER = 'equal';

    /**
     * Флаг исключения пустых значений
     * Если значение "false" - то фильтр работает на исключение.
     * Пустые массивы, возвращаемые фильтрами считаются отсутствием записей в БД и следовательно - ненайденным совпадением
     * Напривер: пересечение [1,2][2,3][] вернет []
     *
     * Если значение "true" - то фильтр работает на ограничивающее исключение.
     * Пустые массивы игнорируются и не участвуют в конечном формировании результатов. Исключение происходит только по действительным значениям
     * НапримерЖ пересечение [1,2][2,3][] вернет [2]
     * @var bool
     */
    private $_excludingEmpty = false;

    /**
     * Типы фильтров
     * @var array
     */
    private $_filterTypes = array(
        self::EQUAL_FILTER => '='
    );

    /**
     * Фильтры типа "="
     * @var array
     */
    protected $_equalFilters = array();


    /**
     * Инициализация фильтра
     */
    public function __construct()
    {
        parent::__construct();
        $this->_addFilterName(App_Core_Model_Collection_Filter::EQUAL_FILTER, 'id');
    }

    /**
     * @param string $type
     * @param string $name
     */
    protected function _addFilterName($type, $name)
    {
        // Добавление Фильтра по значению
        if(array_key_exists($type, $this->_filterTypes)
            && method_exists($this, "_do" . ucfirst($name) . ucfirst($type) . "FilterCollection")
        ) {
            $filter = "_" . $type . "Filters";
            if(!array_key_exists($name, $this->{$filter})) {
                $this->{$filter}[$name] = array();
            }
        }
    }

    /**
     * Установить режим пересечения фильтров
     * "false" - то фильтр работает на исключение.
     * "true" - то фильтр работает на ограничивающее исключение.
     * @param bool $flag
     * @return App_Core_Model_Collection_Filter
     */
    public function setExcludingEmpty(/* TODO: boolean*/ $flag = false)
    {
        if(is_bool($flag)) {
            $this->_excludingEmpty = $flag;
        }

        return $this;
    }

    /**
     * Добавить значение фильтра equal ("Точное совпадение")
     * @param string $name
     * @param mixed $value
     * @return App_Core_Model_Collection_Filter
     */
    public function addEqualFilter($name, $value)
    {
        if(array_key_exists($name, $this->_equalFilters)) {
            if(is_string($value) || is_int($value)) {
                // Исключить повторяющиеся значения
                if(!in_array($value, $this->_equalFilters[$name])) {
                    array_push($this->_equalFilters[$name], $value);
                }
            } else {
                array_push($this->_equalFilters[$name], $value);
            }
        }

        return $this;
    }

    /**
     * Получить значения фильтра equal ("Точное совпадение")
     * @param $name
     * @return mixed
     */
    public function getEqualFilterValues($name)
    {
        return $this->_equalFilters[$name];
    }

    /**
     * По результатам работы фильтров получить коллекцию идентификаторов
     * @return array
     */
    protected function _doCollection()
    {
        $idsA = array();

        // Загружаем результат работы фильтров
        foreach(array_keys($this->_filterTypes) as $key) {
            foreach(array_keys($this->{"_" . $key . "Filters"}) as $filterName) {
                $method = "_do" . ucfirst($filterName) . ucfirst($key) . "FilterCollection";
                // Используем только фильтры, задействованными в коллекции (у которых есть значения)
                if(method_exists($this, $method) && count($this->{"_" . $key . "Filters"}[$filterName]) > 0) {
                    $result = $this->{$method}();
                    if(count($result) == 0 && false === $this->_excludingEmpty) {
                        return array();
                    }
                    $idsA = $this->idsIntersect($idsA, $result);
                }
            }
        }
        return $idsA;
    }

    /**
     * Сброс фильтров
     * @return App_Core_Model_Collection_Filter
     */
    public function resetFilters()
    {
        foreach(array_keys($this->_filterTypes) as $type) {
            foreach(array_keys($this->{"_" . $type . "Filters"}) as $filterName) {
                $this->{"_" . $type . "Filters"}[$filterName] = array();
            }
        }
        return $this;
    }

    /**
     * Фильтр по идентификаторам
     * @return array
     */
    protected function _doIdEqualFilterCollection()
    {
        return $this->getEqualFilterValues('id');
    }

    /**
     * TODO: Сделать метод статическим!
    * Определение пересечения массивов.
    * Внимание: Пустой массив исключается из пересечения
    * @param array $val_1
    * @param array $val_2
    * @return array
    */
    public function idsIntersect(array $val_1, array $val_2)
    {
        if((count((array)$val_1) * count((array)$val_2)) > 0) {
            return array_intersect($val_1, $val_2);
        }

        // В одном из значений присутствует ноль. Проверяем в каком
        return count($val_1) - count($val_2) >= 0 ? $val_1 : $val_2;
    }
}