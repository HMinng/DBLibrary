<?php
namespace HMinng\DBLibrary\AtsQuery;

use HMinng\DBLibrary\AtsDoctrine\AtsDoctrine;

class AtsQuery
{
    /**
     * 数据库连接名
     * @var string
     */
    private $connectionName;
    /**
     * 实例
     * @var array array('name1' => AtsQuery, 'name2' => AtsQuery)
     */
    private static $instances = array();
    /**
     * @param string|null $connectionName 数据库连接名。如为null，使用当前默认数据库连接
     */
    protected function __construct($connectionName = null)
    {
        $this->connectionName = $connectionName;
    }
    
    /**
     * 获取实例
     * @param string|null $connectionName 数据库连接名。如为null，使用当前默认数据库连接
     * @return AtsQuery
     */
    public static function getInstance($connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = AtsDoctrine::DEFAULT_CONNECTION_NAME;
        }
        if(isset(self::$instances[$connectionName])) {
            return self::$instances[$connectionName];
        }
        self::$instances[$connectionName] = new self($connectionName);
        return self::$instances[$connectionName];
    }
    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection()
    {
        return AtsDoctrine::getInstance()->getConnection($this->connectionName);
    }

    /**
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function query()
    {
        return $this->getConnection()->query();
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function create()
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * @param string $sql The SQL statement to prepare.
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function prepare($sql)
    {
        return $this->getConnection()->prepare($sql);
    }

    /**
     * @param string $query The SQL query to execute.
     * @param array $params The parameters to bind to the query, if any.
     * @param array $types
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function executeQuery($query, $params, $types = array())
    {
        return $this->getConnection()->executeQuery($query, $params, $types);
    }

    /**
     * @param string $table The name of the table to insert data into.
     * @param array $fields An associative array containing column-value pairs.
     * @return integer The number of affected rows.
     */
    public function save($table, array $fields)
    {
        return $this->getConnection()->insert($table, $fields);
    }

    /**
     * Executes an SQL UPDATE statement on a table.
     *
     * @param string $table The name of the table to update.
     * @param array $identifier The update criteria. An associative array containing column-value pairs.
     * @return integer The number of affected rows.
     */
    public function update($table, $fields, $where)
    {
        return $this->getConnection()->update($table, $fields, $where);
    }
    
    /**
     * Executes an SQL DELETE statement on a table.
     *
     * @param string $table The name of the table on which to delete.
     * @param array $identifier The deletion criteria. An associateve array containing column-value pairs.
     * @return integer The number of affected rows.
     */
    public function delete($table, $where)
    {
        return $this->getConnection()->delete($table, $where);
    }
    
    /**
     * Quotes a given input parameter.
     *
     * @param mixed $input Parameter to be quoted.
     * @param string $type Type of the parameter.
     * @return string The quoted parameter.
     */
    public function quote($value, $type = NULL)
    {
        return $this->getConnection()->quote($value);
    }
    
    /**
     * Returns the ID of the last inserted row, or the last value from a sequence object,
     * depending on the underlying driver.
     *
     * Note: This method may not return a meaningful or consistent result across different drivers,
     * because the underlying database may not even support the notion of AUTO_INCREMENT/IDENTITY
     * columns or sequences.
     *
     * @param string $seqName Name of the sequence object from which the ID should be returned.
     * @return string A string representation of the last inserted ID.
     */
    public function getlastInsertId($seqName = NULL)
    {
        return $this->getConnection()->lastInsertId($seqName);
    }
}