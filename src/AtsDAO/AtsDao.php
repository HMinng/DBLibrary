<?php
namespace HMinng\DBLibrary\AtsDAO;

use HMinng\DBLibrary\AtsQuery\AtsQuery;
use HMinng\DBLibrary\AtsValidator\AtsDaoValidation;
use HMinng\DBLibrary\AtsDoctrine\AtsDoctrine;

class AtsDao
{
    private $sqlQueries = array();
    private $table = NULL;

    /**
     * @var AtsQuery
     */
    private $atsQuery;

    public function __construct($table, $connectionName = null)
    {
        $this->table = $table;
        $this->atsQuery = AtsQuery::getInstance($connectionName);
    }

    /**
     * 获取该表所有记录
     * @param string $select
     * @return array
     */
    public function getAll($select = '*')
    {
        $sql = "SELECT $select FROM " . $this->table;
        array_push($this->sqlQueries, $sql);

        $statement = $this->atsQuery->prepare($sql);
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 获得单条记录
     * @param array $where
     * @param string $select
     * @return array
     */
    public function get($where, $select = '*', $lock = '')
    {
        $this->setValidator(array(
            AtsDaoValidation::DAO_WHERE => array('value' => $where),
        ));

        $q = $this->atsQuery->create()
            ->from($this->table, 't')
            ->select($select)
            ->where($where['key']);

        $sql = $q->getSQL() . ' ' . $lock;
        
        array_push($this->sqlQueries, $sql);
        
        $statement = $this->atsQuery->executeQuery($sql, $where['value']);
        
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 通过 WHERE IN(?) 方式获取数据
     * @param array $whereIn
     * @param string $select
     * @return array
     */
    public function getIn($whereIn, $select = '*')
    {
        $this->setValidator(array(
            AtsDaoValidation::DAO_FIELD => array('required' => TRUE, 'value' => $whereIn)
        ));

        $statement = $this->atsQuery->executeQuery("SELECT $select FROM " .  $this->table . ' WHERE ' . $whereIn['key'],
            array($whereIn['value']),
            array(101)
        );

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 复杂的查询语句，获得多条记录
     * @param string $select
     * @param array $where
     * @param array $orderby
     * @param integer $limit
     * @param integer $offset
     * @param string $groupby
     * @param array $having
     * @return array
     */
    public function gets($select = '*', $where = array(), $orderby = array(), $limit = NULL, $offset = NULL, $groupby = NULL, $having = array())
    {
        $this->setValidator(array(
            AtsDaoValidation::DAO_WHERE  => array('value' => $where),
            AtsDaoValidation::DAO_LIMIT  => array('value' => $limit),
            AtsDaoValidation::DAO_HAVING => array('value' => $having),
            AtsDaoValidation::DAO_OFFSET => array('value' => $offset)
        ));

        $q = $this->atsQuery->create()
            ->from($this->table, 't')
            ->select($select);

        $where && $q->where($where['key'])->setParameters($where['value']);

        if ($orderby) {
            $i = 0;
            foreach ($orderby as $sort => $order) {
                if ($i == 0) {
                    $q->orderBy($sort, $order);
                } else {
                    $q->addOrderBy($sort, $order);
                }
                $i++;
            }
        }

        $limit   && $q->setMaxResults($limit);
        $offset  && $q->setFirstResult($offset);
        $groupby && $q->groupBy($groupby);
        $having  && $q->having($having);

        array_push($this->sqlQueries, $q->getSQL());

        return $q->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 返回记录条数
     * @param array $where
     * @param string $groupby
     * @return integer returns count of the table rows.
     */
    public function count($where = array(), $groupby = NULL)
    {
        $this->setValidator(array(
            AtsDaoValidation::DAO_WHERE => array('value' => $where)
        ));

        $q = $this->atsQuery->create()
            ->from($this->table, 't')
            ->select("COUNT(*)");

        $groupby && $q->groupBy($groupby);

        $where && $q->where($where['key'])->setParameters($where['value']);

        array_push($this->sqlQueries, $q->getSQL());

        return $groupby ? count($q->execute()->fetchAll(\PDO::FETCH_NUM)) : $q->execute()->fetchColumn() ;
    }

    /**
     * 检查记录是否存在
     * @param array $where
     * @return boolean returns true if the table rows exists, false otherwise.
     */
    public function has($where = array())
    {
        return $this->count($where) ? TRUE : FALSE;
    }

    /**
     * 插入数据
     * @param array $fields
     * @return integer returns 1 if successful save, 0 otherwise.
     */
    public function save($fields)
    {
        $this->setValidator(array(
            AtsDaoValidation::DAO_FIELD => array('required' => TRUE, 'value' => $fields)
        ));

        return $this->atsQuery->save($this->table, $fields);
    }

    /**
     * 使用复杂的WHERE语句对记录作更新操作
     * @param array $fields
     * @param array $where
     * @param array $literal 值为字面意义上的值，不转译
     * @return integer returns 1 if successful execute, 0 otherwise.
     */
    public function update($fields, $where = array(), $literal = array())
    {
        $this->setValidator(array(
            AtsDaoValidation::DAO_FIELD => array('required' => TRUE,  'value' => $fields),
            AtsDaoValidation::DAO_WHERE => array('value' => $where),
        ));

        $q = $this->atsQuery->create()
            ->update($this->table);

        foreach ($fields as $key => $value) {
            if (in_array($key, $literal)) {
            	$q->set($key, $value);
            } else {
                $q->set($key, $this->atsQuery->quote($value));
            }
        }

        $where && $q->where($where['key'])->setParameters($where['value']);

        array_push($this->sqlQueries, $q->getSQL());

        return $q->execute();
    }

    /**
     * 获取最后插入主键值
     * @param string $seqName
     * @return integer
     */
    public function getlastInsertId($seqName = NULL)
    {
        return $this->atsQuery->getlastInsertId($seqName);
    }

    /**
     * 对单条记录作删除操作
     * @param array $where
     * @return integer returns 1 if successful execute, 0 otherwise.
     */
    public function delete($where = array())
    {
        $this->setValidator(array(
            AtsDaoValidation::DAO_WHERE => array('required' => TRUE,  'value' => $where),
        ));

        $q = $this->atsQuery->create()
            ->delete($this->table);

        $where && $q->where($where['key'])->setParameters($where['value']);

        array_push($this->sqlQueries, $q->getSQL());

        return $q->execute();
    }

    public function setValidator(array $params)
    {
        new AtsDaoValidation($params);
    }

    /**
     * 开启SQL语句输出
     * @param boolean $on
     */
    public function debug($on = TRUE)
    {
        if ($on) {
            $html = '<div style="font:10px/1.2em Verdana,sans-serif; font-size:10px; color:#666666; background-color:#EEEEEE; line-height:1.4em; margin:0; padding:4px 10px"><h4>SQL Queries ('.count($this->sqlQueries).')</h4><ul>';

            foreach ($this->sqlQueries as $sql) {
                $html .= sprintf('<li>%s</li>', self::formatSql($sql));
            }

            $html .= "</ul></div><br />";

            echo $html;
        }
    }

    private static function formatSql($sql)
    {
        return preg_replace('/\b(UPDATE|SET|SELECT|FROM|AND|OR|AS|LIMIT|OFFSET|ASC|DESC|COUNT|SUM|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|NOT|IS|DISTINCT|DELETE|INSERT|INTO|VALUES)\b/', '<strong>\\1</strong>', $sql);
    }
}
