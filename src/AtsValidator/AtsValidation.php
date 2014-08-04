<?php
namespace HMinng\DBLibrary\AtsValidator;

use HMinng\DBLibrary\AtsQuery\AtsQuery;
class AtsValidation
{
    /**
     * 验证表是否存在
     * @param string $table
     * @return boolen
     */
    public static function hasTable($table)
    {
        $q = AtsQuery::getInstance()->prepare("SHOW TABLES LIKE :table");
		$q->bindValue('table', $table);
        $q->execute();

        return (bool) $q->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * 验证表字段是否存在
     * @param string $table
     * @param string $column
     * @return boolen
     */
    public static function hasColumn($table, $column)
    {
        if (! self::hasTable($table)) {
            throw new \AtsException("The table '$table' doesn't exitst.", \AtsMessages::DBLIBRARY_ERROR);
        }

        $q = AtsQuery::getInstance()->prepare("SHOW COLUMNS FROM $table LIKE :column");
		$q->bindValue('column', $column);
        $q->execute();

        return (bool) $q->fetch(PDO::FETCH_COLUMN);
    }
}