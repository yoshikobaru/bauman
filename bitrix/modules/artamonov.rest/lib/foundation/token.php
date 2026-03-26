<?php

namespace Artamonov\Rest\Foundation;


use Bitrix\Main\UserTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class Token
{
    const SIZE_STEP = 20;

    private static $_instance;

    /**
     * Возвращает информацию по токену
     * @param string $token
     * @return array|false
     */
    public function getData(string $token)
    {
        try {
            return UserTable::getList([
                'select' => [
                    '*',
                    'UF_*',
                ],
                'filter' => [
                    '=' . Settings::getInstance()->getTokenFieldCode() => $token
                ],
                'limit' => 1,
            ])->fetch();
        } catch (ObjectPropertyException | SystemException $e) {
        }
        return false;
    }

    /**
     * Возвращает данные токена по ID пользователя
     * @param int $userId
     * @return array|false
     */
    public function getDataByUserId(int $userId)
    {
        return User::getInstance()->getToken($userId);
    }

    /**
     * Возвращает срок годности токена
     * @param string $token
     * @return array|false
     */
    public function getExpirationDate(string $token)
    {
        try {
            return UserTable::getList([
                'select' => [
                    Settings::getInstance()->getTokenExpireFieldCode(),
                ],
                'filter' => [
                    '=' . Settings::getInstance()->getTokenFieldCode() => $token
                ],
                'limit' => 1,
            ])->fetch();
        } catch (ObjectPropertyException | SystemException $e) {
        }
        return false;
    }

    /**
     * Обновляет срок годности токена, согласно настройкам Безопасности модуля
     * @param string $token
     * @return bool|string
     */
    public function updateExpirationDate(string $token)
    {
        try {
            $data = UserTable::getList([
                'select' => ['ID'],
                'filter' => [
                    '=' . Settings::getInstance()->getTokenFieldCode() => $token
                ],
                'limit' => 1,
            ])->fetchRaw();
            if ($data) {
                $user = new \CUser();
                $updated = $user->Update($data['ID'], [
                    Settings::getInstance()->getTokenExpireFieldCode() => Settings::getInstance()->getTokenExpire()
                ]);
                if ($updated) {
                    return true;
                } else {
                    return $user->LAST_ERROR;
                }
            }
        } catch (ObjectPropertyException | SystemException $e) {
        }
        return true;
    }

    /**
     * Удаляет токен
     * @param string $token
     * @return bool|string
     */
    public function delete(string $token)
    {
        try {
            $data = UserTable::getList([
                'select' => ['ID'],
                'filter' => [
                    '=' . Settings::getInstance()->getTokenFieldCode() => $token
                ],
                'limit' => 1,
            ])->fetchRaw();
            if ($data) {
                $user = new \CUser();
                $updated = $user->Update($data['ID'], [
                    Settings::getInstance()->getTokenFieldCode() => false,
                    Settings::getInstance()->getTokenExpireFieldCode() => false,
                ]);
                if ($updated) {
                    return true;
                } else {
                    return $user->LAST_ERROR;
                }
            }
        } catch (ObjectPropertyException | SystemException $e) {
        }
        return true;
    }

    /**
     * Генерация токенов для пользователей
     * @param array $parameters
     * @param bool $update
     * @return int
     */
    public function generate(array $parameters = [], bool $update = false)
    {
        $counter = 0;
        // select
        if (!isset($parameters['select'])) {
            $parameters['select'] = [];
        }
        if (!isset($parameters['select']['ID'])) {
            $parameters['select'][] = 'ID';
        }
        if (!isset($parameters['select']['LOGIN'])) {
            $parameters['select'][] = 'LOGIN';
        }
        if (!isset($parameters['select'][settings()->getTokenFieldCode()])) {
            $parameters['select'][] = settings()->getTokenFieldCode();
        }
        // filter
        if (!isset($parameters['filter'])) {
            $parameters['filter'] = [];
        }
        if (!isset($parameters['filter']['ACTIVE'])) {
            $parameters['filter']['ACTIVE'] = 'Y';
        }
        if ($update === false) {
            $parameters['filter'][settings()->getTokenFieldCode()] = false;
        } else {
            $parameters['filter']['!' . settings()->getTokenFieldCode()] = false;
        }
        if (
            isset($parameters['filter'][settings()->getTokenFieldCode()]) &&
            (
                $parameters['filter'][settings()->getTokenFieldCode()] === 'false' ||
                $parameters['filter'][settings()->getTokenFieldCode()] === false
            )
        ) {
            $parameters['filter'][settings()->getTokenFieldCode()] = false;
            unset($parameters['offset']);
        } else {
            // offset
            if (!isset($parameters['offset'])) {
                $parameters['offset'] = 0;
            }
        }
        // order
        if (!isset($parameters['order'])) {
            $parameters['order'] = [];
        }
        if (!isset($parameters['order']['ID'])) {
            $parameters['order']['ID'] = 'ASC';
        }
        try {
            if ($users = UserTable::getList($parameters)) {
                $user = new \CUser();
                while ($data = $users->fetchRaw()) {
                    if (!array_key_exists(settings()->getTokenFieldCode(), $data)) continue;
                    $token = helper()->generateToken($data['ID'], $data['LOGIN']);
                    if ($user->update($data['ID'], [
                        settings()->getTokenFieldCode() => $token,
                        settings()->getTokenExpireFieldCode() => settings()->getTokenExpire()
                    ])) {
                        $counter++;
                    }
                }
            }
        } catch (ObjectPropertyException | SystemException $e) {
        }
        return $counter;
    }

    public static function getInstance(): Token
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
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
