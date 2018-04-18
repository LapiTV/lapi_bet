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

                $day = self::parseNumber($day, 2);
                $month = self::parseNumber($month, 2);
                $year = self::parseNumber($year, 4);

                if (!checkdate($month, $day, $year)) {
                    return false;
                }
                return $day . '/' . $month . '/' . $year;
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
            case 'mdi':
                return $message;
        }

        return false;
    }

    public static function parseNumber($number, $lengthRequired)
    {
        $number = substr($number, 0, $lengthRequired);
        return str_pad($number, $lengthRequired, '0', STR_PAD_LEFT);
    }

    public static function calcDistance($type, $answer, $try)
    {
        switch ($type) {
            case 'string':
                return \levenshtein($answer, $try);
            case 'int':
                return \abs($answer - $try);
            case 'date':
                $answer = \DateTime::createFromFormat('d/m/Y', $answer);
                $try = \DateTime::createFromFormat('d/m/Y', $try);

                $interval = $answer->diff($try);

                return abs((int)$interval->format('%R%a'));
            case 'mdi':
                return (int) ($answer != $try);
            default:
                return 30;
        }
    }

    public static function order($type, $array)
    {
        switch ($type) {
            case 'date':
                uksort($array, function($a, $b) {
                    $a = \DateTime::createFromFormat('d/m/Y', $a);
                    $b = \DateTime::createFromFormat('d/m/Y', $b);

                    return $a <=> $b;
                });
                return $array;
            default:
                ksort($array);
                return $array;
        }
    }
}