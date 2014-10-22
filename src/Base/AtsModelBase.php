<?php
use HMinng\DBLibrary\AtsQuery\AtsQueryWhere;
use HMinng\DBLibrary\AtsDoctrine\AtsDoctrine;

abstract class AtsModelBase
{
    const SHARING = 'lock in share mode';
    const EXCLUSIVE = 'for update';
    
    public static function begin($connectionName = null)
    {
        AtsDoctrine::getInstance()->getConnection($connectionName)->beginTransaction();
    }
    
    public static function rollback($connectionName = null)
    {
    	AtsDoctrine::getInstance()->getConnection($connectionName)->rollBack();
    }
    
    public static function commit($connectionName = null)
    {
    	AtsDoctrine::getInstance()->getConnection($connectionName)->commit();
    }
    
    /**
     * @return AtsQueryWhere
     */
    protected static function getWhere()
    {
        return AtsQueryWhere::getInstance();
    }

    protected static function getWhereIn($array)
    {
        self::checkConditionIf(! $array || ! is_array($array), '参数为空或不是数组');

        $results = array();

        $field = key($array);

        $results = array(
            'key'   => $field . ' IN (?)',
            'value' => $array[$field]
        );

        return $results;
    }

    /**
     * 返回分页数据
     * @param array $results
     */
    protected static function getPager($results)
    {
        self::checkConditionIf(!$results || !is_array($results) || count($results) != 2, '参数为空或不是数组');

        return $results;
    }

    /**
     * 对返回真值的条件抛出异常
     * @param string $condition
     * @param string $message
     */
    protected static function checkConditionUnless($condition, $message = NULL)
    {
        if (! $condition)
        {
            $message =  NULL === $message ? 'Service 层出错，请检查' : $message;
            throw new \AtsException($message);
        }
    }

    /**
     * 对返回假值的条件抛出异常
     * @param string $condition
     * @param string $message
     */
    protected static function checkConditionIf($condition, $message = NULL)
    {
        if ($condition)
        {
            $message =  NULL === $message ? 'Service 层出错，请检查' : $message;
           throw new \AtsException($message);
        }
    }
}