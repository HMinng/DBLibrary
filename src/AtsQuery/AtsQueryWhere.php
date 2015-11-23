<?php
namespace HMinng\DBLibrary\AtsQuery;

class AtsQueryWhere
{
    protected $where = array();
    protected $whereParts = array();

    private function __construct() {}

    /**
     * @return AtsQueryWhere
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * @param $where
     * @param array $params
     * @return AtsQueryWhere
     */
    public function where($where, $params = array())
    {
        $this->where = array();

        if (is_array($params)) {
            $this->where = $params;
        } else {
            $this->where[] = $params;
        }

        return $this->_addWherePart($where);
    }

    /**
     * @param $where
     * @param array $params
     * @return AtsQueryWhere
     */
    public function andWhere($where, $params = array())
    {
        if (is_array($params)) {
            $this->where = array_merge($this->where, $params);
        } else {
            $this->where[] = $params;
        }

        if ($this->_hasWherePart()) {
            $this->_addWherePart('AND', TRUE);
        }

        return $this->_addWherePart($where, TRUE);
    }

    public function addWhere($where, $params = array())
    {
        return $this->andWhere($where, $params);
    }

    /**
     * @param $where
     * @param array $params
     * @return AtsQueryWhere
     */
    public function orWhere($where, $params = array())
    {
        if (is_array($params)) {
            $this->where = array_merge($this->where, $params);
        } else {
            $this->where[] = $params;
        }

        if ($this->_hasWherePart()) {
            $this->_addWherePart('OR', TRUE);
        }

        return $this->_addWherePart($where, TRUE);
    }

    /**
     * @param $expr
     * @param array $params
     * @param bool $not
     * @return AtsQueryWhere
     */
    public function whereIn($expr, $params = array(), $not = false)
    {
        return $this->andWhereIn($expr, $params, $not);
    }

    /**
     * @param $expr
     * @param array $params
     * @param bool $not
     * @return AtsQueryWhere
     */
    public function andWhereIn($expr, $params = array(), $not = false)
    {
        // if there's no params, return (else we'll get a WHERE IN (), invalid SQL)
        if (isset($params) and (count($params) == 0)) {
            return $this;
        }

        if ($this->_hasWherePart()) {
            $this->_addWherePart('AND', true);
        }

        return $this->_addWherePart($this->_processWhereIn($expr, $params, $not), true);
    }

    /**
     * @param $expr
     * @param array $params
     * @param bool $not
     * @return AtsQueryWhere
     */
    public function orWhereIn($expr, $params = array(), $not = false)
    {
        // if there's no params, return (else we'll get a WHERE IN (), invalid SQL)
        if (isset($params) and (count($params) == 0)) {
            return $this;
        }

        if ($this->_hasWherePart()) {
            $this->_addWherePart('OR', true);
        }

        return $this->_addWherePart($this->_processWhereIn($expr, $params, $not), true);
    }

    protected function _processWhereIn($expr, $params = array(), $not = false)
    {
        $params = (array) $params;

        // if there's no params, return (else we'll get a WHERE IN (), invalid SQL)
        if (count($params) == 0) {
            throw new \Exception('You must pass at least one parameter when using an IN() condition.');
        }

        $a = array();
        foreach ($params as $k => $value) {
            if ($value instanceof \Doctrine\DBAL\Query\QueryBuilder) {
                $value = $value->getSql();
                unset($params[$k]);
            } else {
                $value = '?';
            }
            $a[] = $value;
        }

        $this->where = array_merge($this->where, $params);

        return $expr . ($not === true ? ' NOT' : '') . ' IN (' . implode(', ', $a) . ')';
    }

    /**
     * @param $expr
     * @param array $params
     * @return AtsQueryWhere
     */
    public function whereNotIn($expr, $params = array())
    {
        return $this->whereIn($expr, $params, true);
    }

    /**
     * @param $expr
     * @param array $params
     * @return AtsQueryWhere
     */
    public function andWhereNotIn($expr, $params = array())
    {
        return $this->andWhereIn($expr, $params, true);
    }

    public function orWhereNotIn($expr, $params = array())
    {
        return $this->orWhereIn($expr, $params, true);
    }

    protected function _hasWherePart()
    {
        return count($this->whereParts) > 0;
    }

    protected function _addWherePart($queryPart, $append = FALSE)
    {
        if ($queryPart === NULL) {
            throw new \Exception('Cannot define NULL as part of query when defining \'' . $queryPart . '\'.');
        }

        if ($append) {
            $this->whereParts['where'][] = $queryPart;
        } else {
            $this->whereParts['where'] = array($queryPart);
        }

        return $this;
    }

    public function end()
    {
        $where   = NULL;
        $results = array();

        foreach ($this->whereParts['where'] as $part) {
            if (strpos($part, '?') !== FALSE) {
                $where .= $part;
            } else {
                $where .= " $part ";
            }
        }

        $results['value'] = $this->where;
        $results['key'] = $where;

        return $results;
    }
}