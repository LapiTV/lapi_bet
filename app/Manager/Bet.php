<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/23/17
 * Time: 11:39 AM
 */

namespace Bet\App\Manager;


use Bet\App\Exception\CustomException;
use Bet\App\Service\Database;

class Bet extends BaseManager
{
    protected static $table = 'bet';

    private static $fieldSup = [
        'TIMESTAMPDIFF(SECOND, dateCreated, now()) as timeCreated',
        'DATE_ADD(dateCreated, INTERVAL pariDurationMinute MINUTE) as dateEnd',
        'now() as dateNow'
    ];

    public static function get(int $id)
    {
            $selectStatement = Database::getInstance()->select(array_merge(['*'], self::$fieldSup))
            ->from(static::$table)
            ->where('id', '=', $id);

        return $selectStatement->execute()->fetch();
    }

    public static function getLastBet()
    {
        $lastBet = Database::getInstance()->select(array_merge(['*'], self::$fieldSup))
            ->from(self::$table)
            ->orderBy('dateCreated', 'DESC')
            ->limit(1, 0);

        return $lastBet->execute()->fetch();
    }

    /**
     * @return \DateTime
     * @throws CustomException
     */
    public static function getDateEnd(int $id)
    {
        $lastBet = static::get($id);

        if (empty($lastBet)) {
            throw new CustomException('Il n\'y a pas de pari en cours.');
        }

        $dateEnd = new \DateTime($lastBet['dateEnd']);

        if($dateEnd < new \DateTime()) {
            throw new CustomException('Il n\'y a pas de pari en cours.');
        }

        return $dateEnd->setTimezone(new \DateTimeZone('UTC'));
    }
}