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

    public static function getLastBet()
    {
        $lastBet = Database::getInstance()->select()
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

        $dateCreated = new \DateTime($lastBet['dateCreated']);
        $interval = new \DateInterval('PT' . $lastBet['pariDurationMinute'] . 'M');

        $dateEnd = $dateCreated->add($interval);

        if($dateEnd < new \DateTime()) {
            throw new CustomException('Il n\'y a pas de pari en cours.');
        }

        return $dateEnd->setTimezone(new \DateTimeZone('UTC'));
    }
}