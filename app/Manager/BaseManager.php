<?php
/**
 * Created by PhpStorm.
 * User: Francois
 * Date: 23/06/2017
 * Time: 17:46
 */

namespace Bet\App\Manager;


use Bet\App\Service\Database;

class BaseManager
{
    protected static $table = '';

    public static function getAll($orderBy = null, $limit = null)
    {
        $selectStatement = Database::getInstance()->select()
            ->from(static::$table);

        if(!empty($orderBy)) {
            foreach($orderBy as $col => $dir) {
                $selectStatement->orderBy($col, $dir);
            }
        }

        if(!empty($limit)) {
            $selectStatement->limit($limit);
        }

        return $selectStatement->execute()->fetchAll();
    }

    public static function get(int $id)
    {
        $selectStatement = Database::getInstance()->select()
            ->from(static::$table)
            ->where('id', '=', $id);

        return $selectStatement->execute()->fetch();
    }
}