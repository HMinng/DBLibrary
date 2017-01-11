<?php

/**
 * Created by PhpStorm.
 * User: HMinng
 * Date: 09/01/2017
 * Time: 11:43
 */
class ModelInterfaceBase extends AtsInterfaceBase
{
    /**
     * @var $table database's table name
     */
    private static $table;
    
    /**
     * @var $connectName database connect name
     */
    private static $connectName;
    
    private static $instance = null;
    
    private final function __construct() { }
    
    private final function __clone() { }
    
    public static function getInstance($table, $connectName = 'master')
    {
        self::checkConditionIf(! $table || ! $connectName);
        
        self::$table       = $table;
        self::$connectName = $connectName;
        
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function create($params)
    {
        return self::getDao(self::$table, self::$connectName)->save($params);
    }
    
    public function update($fields, $where)
    {
        return self::getDao(self::$table, self::$connectName)->update($fields, $where);
    }
    
    public function get($where)
    {
        return self::getDao(self::$table, self::$connectName)->get($where);
    }
    
    public function gets($select = '*', $where = array(), $orderby = array(), $limit = null, $offset = null)
    {
        return self::getDao(self::$table, self::$connectName)->gets($select, $where, $orderby, $limit, $offset);
    }
    
    public function count($where)
    {
        return self::getDao(self::$table, self::$connectName)->count($where);
    }
}