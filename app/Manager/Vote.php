<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/23/17
 * Time: 11:48 AM
 */

namespace Bet\App\Manager;


use Bet\App\Service\Database;

class Vote extends BaseManager
{
    protected static $table = 'vote';

    const THRESHOLD_DISPLAY = 5;

    public static function getVoteOf(int $betId)
    {
        $selectStatement = Database::getInstance()->select()
            ->from('vote')
            ->where('betId', '=', $betId);

        return $selectStatement->execute()->fetchAll();
    }
}