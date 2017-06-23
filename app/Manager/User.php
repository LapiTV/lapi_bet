<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/23/17
 * Time: 11:45 AM
 */

namespace Bet\App\Manager;


class User extends BaseManager
{
    public static function isLogin()
    {
        return !empty($_SESSION) && !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }
}