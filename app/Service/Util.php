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

    public static function getUserOnline(): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://tmi.twitch.tv/group/user/w_lapin/chatters");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        if(empty($output)) {
            return [];
        }

        $users = json_decode($output, true);

        return $users['chatters']['moderators'] + $users['chatters']['staff'] + $users['chatters']['admins'] + $users['chatters']['global_mods'] + $users['chatters']['viewers'];
    }
}