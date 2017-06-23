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
            ->from(self::$table);

        return $selectStatement->execute()->fetchAll();
    }

    public static function parseMessage($type, $message)
    {
        $key = false;
        switch($type) {
            case 'date':
                $msg = substr($message, 0, 10);
                $dateInfo = explode('/', $msg);
                $day = $dateInfo[0] ?? 0;
                $month = $dateInfo[1] ?? 0;
                $year = $dateInfo[2] ?? 0;

                if(!checkdate($month, $day, $year)) {
                    return false;
                }
                $key = $year . '/' . $month . '/' . $day;
                break;
            case 'int':
                $res = null;
                $get = preg_match('/^[0-9]+/', $message, $res);

                if(empty($get)) {
                    return false;
                }

                $key = (int) $res[0];

                break;
            case 'string':
                $key = ucfirst(strtolower(trim($message)));
                if(empty($key)) {
                    return false;
                }
                break;
        }

        return $key;
    }
}