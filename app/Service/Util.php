<?php
/**
 * Created by PhpStorm.
 * User: Francois
 * Date: 29/07/2017
 * Time: 22:33
 */

namespace Bet\App\Service;


class Util
{
    public static function roundDownToAny(int $number, int $any = 5): int
    {
        return $any * floor($number / $any);
    }
}