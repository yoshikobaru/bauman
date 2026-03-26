<?php

namespace Artamonov\Rest\Foundation;


use Bitrix\Main\Type\DateTime;

class Settings
{
    private static $_instance = null;
    private static $_settings;

    public function get($code = '')
    {
        return $code ? self::$_settings[$code] : self::$_settings;
    }

    public function getTokenFieldCode()
    {
        return config()->get('tokenFieldCode') ? config()->get('tokenFieldCode') : self::$_settings['config']['token']['code'];
    }

    public function getTokenExpireFieldCode()
    {
        return self::$_settings['config']['token']['expire']['code'];
    }

    public function getTokenExpire(): string
    {
        $lifetime = config()->get('tokenLifetime') ? config()->get('tokenLifetime') . ' seconds' : self::$_settings['config']['token']['expire']['lifetime'];
        $date = new DateTime();
        $date->add('+' . $lifetime);
        return $date->toString();
    }

    public static function getInstance(): Settings
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
            self::$_settings = require __DIR__ . '/../../settings.php';
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    public function __call($name, $arguments)
    {
        response()->internalServerError('Method \'' . $name . '\' is not defined');
    }

    private function __clone()
    {
    }
}
