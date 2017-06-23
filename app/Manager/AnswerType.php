<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/23/17
 * Time: 11:46 AM
 */

namespace Bet\App\Manager;


use Bet\App\Service\Database;

class AnswerType
{
    protected static $table = 'answerType';

    public static function getAll()
    {
        $selectStatement = Database::getInstance()->select()
            ->from(self::$table)
            ->orderBy('dateCreated', 'DESC');

        return $selectStatement->execute()->fetchAll();
    }
}