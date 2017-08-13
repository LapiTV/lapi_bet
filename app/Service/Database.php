<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/23/17
 * Time: 11:36 AM
 */

namespace Bet\App\Service;


class Database
{
    /** @var \Slim\PDO\Database */
    protected static $database;

    public static function setInstance(\Slim\PDO\Database $database)
    {
        self::$database = $database;
    }

    /**
     * @return \Slim\PDO\Database
     */
    public static function getInstance()
    {
        return self::$database;
    }

    public static function getTimeDatabase()
    {
        $stmt = self::$database->query('SELECT now() as now;');
        $stmt->execute();

        return $stmt->fetch()['now'];
    }
}