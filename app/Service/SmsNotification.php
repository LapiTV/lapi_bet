<?php
/**
 * Created by PhpStorm.
 * User: Francois
 * Date: 28/06/2017
 * Time: 21:56
 */

namespace Bet\App\Service;


use Symfony\Component\Yaml\Yaml;

class SmsNotification
{
    public static function sendSms($message)
    {
        try {
            $ch = curl_init();

            $config = Yaml::parse(file_get_contents(__DIR__ . '/../../src/db.yaml'));

            $postFields = [
                'token=' . curl_escape($ch, $config['twilio']['token']),
                'message=' . curl_escape($ch, $message),
            ];

            curl_setopt($ch, CURLOPT_URL, 'https://sms.wisak.eu');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $postFields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $data = curl_exec($ch);
            var_dump($data);

            curl_close($ch);
        } catch(\Exception $e) {}
    }
}