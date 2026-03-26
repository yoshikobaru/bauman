<?php

namespace Artamonov\Rest\Foundation\Security;


class Password
{
    public static function equals($login, string $originalPassword, string $hashPassword = ''): bool
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lib/security/password.php')) {
            return \Bitrix\Main\Security\Password::equals($hashPassword, $originalPassword);
        }
        global $USER;
        $USER = is_object($USER) ? $USER : new \CUser();
        $isAuthorized = $USER->Login($login, $originalPassword);
        return $isAuthorized === true;
    }
}