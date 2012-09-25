<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 * @version: 23.03.12
 */
/**
 * ru: Подготавливает результаты выборки из БД через PDO
 */
class App_Core_Resource_DbApi_Result
{
    /**
     * @var null|Zend_Db_Statement
     */
    protected $_stmt = null;

    /**
     * @param Zend_Db_Statement $stmt
     * @param int $mode
     */
    public function __construct(Zend_Db_Statement $stmt, $mode = PDO::FETCH_ASSOC)
    {
        $this->_stmt = $stmt;
        $this->_stmt->setFetchMode($mode);
    }

    /**
     * Количество строк в результирующей выборке
     * @return int
     */
    public function rowCount()
    {
        return $this->_stmt->rowCount();
    }

    /**
     * Выбрать одну запись из результатов запроса
     * @return array|mixed
     */
    public function fetchRow()
    {
        if($this->_stmt->rowCount() === 0) {
            return array();
        }

        return $this->_stmt->fetch();
    }

    /**
     * Выбрать все записи из результатов запроса
     * @return array
     */
    public function fetchAll()
    {
        return $this->_stmt->fetchAll();
    }
}