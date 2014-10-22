<?php
use HMinng\DBLibrary\AtsDAO\AtsDao;

abstract class AtsInterfaceBase
{
    /**
     * @param string $table
     * @param string|null $connectionName string=连接名; null=使用当前默认连接
     * @return AtsDao
     */
    protected static function getDao($table, $connectionName = null)
    {
        return new AtsDao($table, $connectionName);
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
            $message =  NULL === $message ? 'Interface 层出错，请检查' : $message;
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
            $message =  NULL === $message ? 'Interface 层出错，请检查' : $message;
            throw new \AtsException($message);
        }
    }
}