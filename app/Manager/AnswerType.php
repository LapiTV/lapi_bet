<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/23/17
 * Time: 11:46 AM
 */

namespace Bet\App\Manager;


class AnswerType extends BaseManager
{
    protected static $table = 'answerType';

    public static function parseMessage($type, $message)
    {
        switch ($type) {
            case 'date':
                $msg = substr($message, 0, 10);
                $dateInfo = explode('/', $msg);
                $day = $dateInfo[0] ?? 0;
                $month = $dateInfo[1] ?? 0;
                $year = $dateInfo[2] ?? 0;

                if (!checkdate($month, $day, $year)) {
                    return false;
                }
                return $year . '/' . $month . '/' . $day;
            case 'int':
                $res = null;
                $get = preg_match('/^[0-9]+/', $message, $res);

                if (empty($get)) {
                    return false;
                }

                return (int)$res[0];
            case 'string':
                $key = ucfirst(strtolower(trim($message)));
                if (empty($key)) {
                    return false;
                }
                return $key;
        }

        return false;
    }
}