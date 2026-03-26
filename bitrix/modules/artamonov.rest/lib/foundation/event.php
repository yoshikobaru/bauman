<?php

namespace Artamonov\Rest\Foundation;


class Event
{
    public static function OnProlog()
    {
        // Late Start
        Core::getInstance()->start();
    }

    public static function OnBeforeUserAdd(&$arFields)
    {
        // Generate token for new user
        if (
            Config::getInstance()->get('useGenerateTokenRegisterUser') &&
            empty($arFields[Settings::getInstance()->getTokenFieldCode()])
        ) {
            $token = Helper::getInstance()->generateToken($arFields['ID'], $arFields['LOGIN']);
            $arFields[Settings::getInstance()->getTokenFieldCode()] = $token;
            $arFields[Settings::getInstance()->getTokenExpireFieldCode()] = Settings::getInstance()->getTokenExpire();
        }
    }

    public static function OnBeforeUserRegister(&$arFields)
    {
        // Generate token for new user
        if (
            Config::getInstance()->get('useGenerateTokenRegisterUser') &&
            empty($arFields[Settings::getInstance()->getTokenFieldCode()])
        ) {
            $token = Helper::getInstance()->generateToken($arFields['ID'], $arFields['LOGIN']);
            $arFields[Settings::getInstance()->getTokenFieldCode()] = $token;
            $arFields[Settings::getInstance()->getTokenExpireFieldCode()] = Settings::getInstance()->getTokenExpire();
        }
    }

    public static function OnBeforeUserSimpleRegister(&$arFields)
    {
        // Generate token for new user
        if (
            Config::getInstance()->get('useGenerateTokenRegisterUser') &&
            empty($arFields[Settings::getInstance()->getTokenFieldCode()])
        ) {
            $token = Helper::getInstance()->generateToken($arFields['ID'], $arFields['LOGIN']);
            $arFields[Settings::getInstance()->getTokenFieldCode()] = $token;
            $arFields[Settings::getInstance()->getTokenExpireFieldCode()] = Settings::getInstance()->getTokenExpire();
        }
    }
}
