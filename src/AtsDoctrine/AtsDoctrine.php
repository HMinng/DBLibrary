<?php
namespace HMinng\DBLibrary\AtsDoctrine;

use Doctrine\Common\ClassLoader;
use HMinng\DBLibrary\AtsConfig\AtsConfig;

class AtsDoctrine
{
    protected static $instance   = NULL;
    protected static $DBALLoaded = FALSE;
    const DEFAULT_CONNECTION_NAME = 'master';
    /**
     * 当前默认数据库连接名
     * @var string
     */
    protected $connectionName = NULL;
    protected $databaseConfigures = NULL;
    /**
     * 数据库连接
     * @var array array('name1' => Doctrine\DBAL\Connection, 'name2' => Doctrine\DBAL\Connection)
     */
    protected $conn = array();

    public $lastHeartbeatTime = array();
    public $heartbeatTimeout = 120;

    protected function __construct()
    {
        $this->connectionName = self::DEFAULT_CONNECTION_NAME;
        $this->databaseConfigures = AtsConfig::configures();
    }

    /**
     * @return AtsDoctrine
     */
    public static function getInstance()
    {
        if (self::$instance == NULL) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * 返回数据库连接句柄
     * @param string|null $connectionName 数据库连接名。如为null，返回当前默认数据库连接
     * @param boolean $newConnection 是否重新建立连接
     * @param boolean $autoReconnect 当现有连接go away时，是否尝试新建连接
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection($connectionName = NULL, $newConnection = false, $autoReconnect = false)
    {
        if (is_null($connectionName)) {
            $connectionName = $this->connectionName;
        }

        if (! isset($this->lastHeartbeatTime[$connectionName]) || ! $this->lastHeartbeatTime[$connectionName]) {
            $this->lastHeartbeatTime[$connectionName] = time();
        }

        if (in_array($connectionName, array_keys($this->conn)) && $this->conn[$connectionName] instanceof \Doctrine\DBAL\Connection && (time() - $this->lastHeartbeatTime[$connectionName] > $this->heartbeatTimeout)) {
            $this->lastHeartbeatTime[$connectionName] = time();
            $this->conn[$connectionName] = NULL;
        }

        if (!$newConnection && in_array($connectionName, array_keys($this->conn)) && $this->conn[$connectionName] instanceof \Doctrine\DBAL\Connection) {
            //do nothing
        } else {
            $configureation   = new \Doctrine\DBAL\Configuration();
            $connectionParams = $this->getValidConnectionParams($connectionName);

            $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $configureation);
            $this->conn[$connectionName] = $conn;
            $this->conn[$connectionName]->executeQuery('SET NAMES UTF8');
            //$this->conn[$connectionName]->executeQuery('SET SESSION wait_timeout=900');
        }

        return $this->conn[$connectionName];
    }

    /**
     * 设置当前默认的连接
     * @param string $connectionName
     * @return AtsDoctrine
     */
    public function setCurrentConnection($connectionName)
    {
        if (!$this->isValidConnectionName($connectionName)) {
            throw new \AtsException('找不到数据库连接的配置参数，请检查');
        }
        $this->connectionName = $connectionName;
        return $this;
    }

    /**
     * 返回当前默认数据库连接名称
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * 重置，设为默认当前数据库连接
     * @return AtsDoctrine
     */
    public function resetCurrentConnection()
    {
        $this->setCurrentConnection(self::DEFAULT_CONNECTION_NAME);
        return $this;
    }

    /**
     * 验证默认数据库连接名是否正确
     * @param string $connectionName
     * @return boolean
     */
    protected function isValidConnectionName($connectionName)
    {
        $flag = FALSE;

        foreach ($this->databaseConfigures as $key => $databaseConfigure) {
            $key == $connectionName && $flag = TRUE;
        }

        if (! $flag) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * 获取正确的数据库连接参数
     * @param string $connectionName
     * @return array
     */
    protected function getValidConnectionParams($connectionName)
    {
        if (count($this->databaseConfigures) < 1) {
            throw new \AtsException('找不到数据库配置参数，请检查');
        }

        if (!$this->isValidConnectionName($connectionName)) {
           throw new \AtsException('找不到数据库配置参数，请检查');
        }

        return $this->databaseConfigures[$connectionName];
    }

    /**
     * 获取所有连接名
     * @return array
     */
    public function getConnectionNames()
    {
         return array_keys($this->databaseConfigures);
    }
}