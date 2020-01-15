<?php
/**
 * Created by PhpStorm.
 * User: minms
 * Date: 2020/1/15
 * Time: 09:16
 */

namespace Minms\RabbitMQ;

class Config
{
    private static $host;
    private static $port;
    private static $user;
    private static $password;
    private static $vhost;

    public static function setConfig($host, $port, $user, $password, $vhost = '/')
    {
        self::$host = $host;
        self::$port = $port;
        self::$user = $user;
        self::$password = $password;
        self::$vhost = $vhost;
    }

    public static function getConfig()
    {
        return [
            'host' => self::$host,
            'port' => self::$port,
            'user' => self::$user,
            'password' => self::$password,
            'vhost' => self::$vhost,
        ];
    }
}