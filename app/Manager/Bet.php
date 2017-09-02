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
        '(EXTRACT(EPOCH FROM current_timestamp - datecreated))::Integer AS "timecreated"',
        'datecreated + paridurationminute * INTERVAL \'1 minute\' as dateend',
        'now() as datenow'
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
            ->orderBy('datecreated', 'DESC')
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

        $dateCreated = new \DateTime($lastBet['datecreated']);
        $interval = new \DateInterval('PT' . $lastBet['paridurationminute'] . 'M');

        $dateEnd = $dateCreated->add($interval);

        if ($dateEnd < new \DateTime()) {
            throw new CustomException('Il n\'y a pas de pari en cours.');
        }

        return $dateEnd->setTimezone(new \DateTimeZone('UTC'));
    }
}