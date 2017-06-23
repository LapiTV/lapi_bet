<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/23/17
 * Time: 11:39 AM
 */

namespace Bet\App\Manager;


use Bet\App\Service\Database;

class Bet extends BaseManager
{
    protected static $table = 'bet';

    public static function getLastBet()
    {
        $lastBet = Database::getInstance()->select()
            ->from(self::$table)
            ->orderBy('dateCreated', 'DESC')
            ->limit(1, 0);

        return $lastBet->execute()->fetch();
    }
}