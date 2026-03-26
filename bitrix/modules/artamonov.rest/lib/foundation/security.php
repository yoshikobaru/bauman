<?php

namespace Artamonov\Rest\Foundation;


use Artamonov\Rest\Foundation\Security\Password;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class Security
{
    private static $_instance;
    private $errors;

    /**
     * Получение настроек безопасности из настроек роута
     *
     * @return mixed
     */
    public function get()
    {
        return route()->get()['security'];
    }

    /**
     * Проверка активности роута
     *
     * @return array
     */
    public function isActive()
    {
        if (route()->isActive() === true) {
            return [
                'result' => true,
            ];
        } else {
            return [
                'result' => false,
                'message' => 'Route is disabled',
            ];
        }
    }

    /**
     * Проверка наличия контроллера для входящего запроса
     *
     * @return array
     */
    public function hasController()
    {
        if (route()->get()['controller']) {
            return [
                'result' => true,
            ];
        } else {
            return [
                'result' => false,
                'message' => 'Request cannot be processed. Controller is missing.',
            ];
        }
    }

    /**
     * Проверка соответствия типа контента входящего запроса настройкам роута
     *
     * @return array
     */
    public function isValidContentType()
    {
        if (route()->get()['contentType'] && mb_strpos($_SERVER['CONTENT_TYPE'], route()->get()['contentType']) !== 0) {
            return [
                'result' => false,
                'message' => 'Invalid content type. Must be: ' . route()->get()['contentType'],
            ];
        } else {
            return [
                'result' => true,
            ];
        }
    }

    /**
     * Проверка соответствия параметров входящего запроса настройкам роута
     *
     * @return bool
     */
    public function isValidParameters()
    {
        if (!request()->map()['parameters']) return true;

        $request = request()->get();
        $parameters = request()->map()['parameters'];
        $errors = [];

        $this->processRequestParameters($request, $parameters, $errors);

        if (count($errors) > 0) {
            $this->errors['badParameters'] = $errors;
            return false;
        }

        // Обновим данные входящего запроса, так как возможно, эти данные были преобразованы в ходе их проверки
        request()->update($request);

        return true;
    }

    private function processRequestParameters(&$request, &$parameters, &$errors)
    {
        foreach ($parameters as $code => &$config) {

            if (mb_strpos($code, 'separator:') !== false || !isset($config['required']) || $config['required'] !== true) continue;

            // Если указаны настройки для нескольких объектов/массивов
            // Тогда проверим каждый объект/массив
            if (isset($config['parameters'][0])) {
                foreach ($request[$code] as $index => $item) {
                    $this->processRequestParameters($request[$code][$index], $config['parameters'][0], $errors[$code][$index]);
                    // Если ошибок нет, тогда очистим ошибки
                    if (
                        (
                            is_array($errors[$code])
                            &&
                            array_key_exists($index, $errors[$code])
                            &&
                            $errors[$code][$index] === null
                        )
                        ||
                        (
                            is_array($errors[$code][$index])
                            &&
                            count($errors[$code][$index]) === 0
                        )
                    ) {
                        unset($errors[$code][$index]);
                    }
                }
            }

            // Проверим наличие параметра
            if (!array_key_exists($code, $request)) {
                $errors[$code]['required'] = $config['required'];
            }

            // Проверим характеристики
            if (isset($config['type'])) {
                $isType = 'is' . $config['type'];
                $value =& $request[$code];

                if (mb_strtolower($config['type']) === 'array' && is_array($value) && count($value) === 0) {
                    $value = null;
                }

                $result = helper()->$isType($value);

                if ($result === false) {
                    $errors[$code]['invalidValueType'] = $value;
                    $errors[$code]['valueTypeMustBe'] = $config['type'];
                } else {
                    if ($config['type'] === 'integer' || $config['type'] === 'float') {
                        $value = $result;
                    }
                }
            }

            if (isset($config['parameters'])) {
                //$this->processRequestParameters($request[$code], $config['parameters'], $errors[$code]);
                try {
                    $this->processRequestParameters($request[$code], $config['parameters'], $errors[$code]);
                } catch (\Error $error) { // PHP 7
                    //response()->json($error->getMessage());
                } catch (\Exception $error) { // PHP 5
                    //response()->json($error->getMessage());
                }
            }

            // Если ошибок нет, тогда очистим ошибки
            if (
                (
                    is_array($errors)
                    &&
                    array_key_exists($code, $errors)
                    &&
                    $errors[$code] === null
                )
                ||
                (
                    is_array($errors[$code])
                    &&
                    count($errors[$code]) === 0
                )
            ) {
                unset($errors[$code]);
            }
        }
    }

    /**
     * Используется для проверки параметров запроса
     *
     * @param      $params
     * @param      $code
     * @param      $required
     * @param      $type
     * @param bool $parent
     * @param bool $index
     *
     * @deprecated since 4.1.0
     */
    private function checkParameter($params, $code, $required, $type, $parent = false, $index = false)
    {
        if ($required && !array_key_exists($code, $params)) {
            if ($parent) {
                if ($index !== false) {
                    $this->errors['badParameters'][$parent][$index][$code]['required'] = $required;
                } else {
                    $this->errors['badParameters'][$parent][$code]['required'] = $required;
                }
            } else {
                $this->errors['badParameters'][$code]['required'] = $required;
            }
        } else if (array_key_exists($code, $params)) {
            $isType = 'is' . $type;
            $value = $params[$code];
            if (helper()->$isType($value) === false) {
                if ($parent) {
                    if ($index !== false) {
                        $this->errors['badParameters'][$parent][$index][$code]['invalidValueType'] = $value;
                        $this->errors['badParameters'][$parent][$index][$code]['valueTypeMustBe'] = $type;
                    } else {
                        $this->errors['badParameters'][$parent][$code]['invalidValueType'] = $value;
                        $this->errors['badParameters'][$parent][$code]['valueTypeMustBe'] = $type;
                    }
                } else {
                    $this->errors['badParameters'][$code]['invalidValueType'] = $value;
                    $this->errors['badParameters'][$code]['valueTypeMustBe'] = $type;
                }
            }
        }
    }

    /**
     * Проверка авторизации при доступе к интерфейсу
     *
     * @return array|bool|bool[]|false[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function isAuthorized()
    {
        if (!$this->get()['auth']['required']) return true;
        if (!$this->get()['auth']['type']) return false;
        if ($this->get()['auth']['type'] === helper()->token()) return $this->checkAccessByToken();
        if ($this->get()['auth']['type'] === helper()->login()) return $this->checkAccessByLoginPassword();
        return true;
    }

    /**
     * Проверка доступа к интерфейсу используя токен
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function checkAccessByToken()
    {
        if (!config()->get('useToken')) {
            return [
                'result' => false,
                'message' => 'You must include the Authorization token in the module settings',
            ];
        }
        if (!request()->header('authorization-token')) {
            return [
                'result' => false,
                'message' => 'Authorization-Token header is missing',
            ];
        }
        $token = request()->header('authorization-token');
        // Token key
        if (config()->get('tokenKey')) {
            $token = explode(':', $token);
            $keyword = $token[0];
            $token = $token[1];
            if (config()->get('tokenKey') !== $keyword) {
                return [
                    'result' => false,
                    'message' => 'Invalid token keyword',
                ];
            }
        }
        // Whitelist
        if ($this->get()['token']['whitelist'] && !in_array($token, $this->get()['token']['whitelist'])) {
            return [
                'result' => false,
                'message' => 'Token authorization failed',
            ];
        }
        // Filter
        $filter = ['=' . settings()->getTokenFieldCode() => $token];
        // Expire
        if (
            !$this->get()['token']['whitelist'] &&
            (
                (
                    isset($this->get()['token']['checkExpire']) &&
                    $this->get()['token']['checkExpire'] === true
                )
                ||
                (
                    !isset($this->get()['token']['checkExpire']) &&
                    config()->get('checkExpireToken')
                )
            )
        ) {
            $date = new DateTime();
            $filter['>' . settings()->getTokenExpireFieldCode()] = $date->toString();
        }
        // User
        $user = UserTable::getList(['select' => ['*', 'UF_*'], 'filter' => $filter, 'limit' => 1]);
        if ($user->getSelectedRowsCount() === 0) {
            return [
                'result' => false,
                'message' => 'Authorization for the token has failed or expired token',
            ];
        }
        $user = $user->fetchRaw();
        $userGroups = UserTable::getUserGroupIds($user['ID']);
        // Whitelist groups
        if (!$this->get()['token']['whitelist'] && $this->get()['group']['whitelist'] && !array_intersect($userGroups, $this->get()['group']['whitelist'])) {
            return [
                'result' => false,
                'message' => 'Token authorization failed',
            ];
        }
        // Requests
        $this->requestLimit($token, $userGroups);
        // Prepare user
        foreach ($user as &$value) {
            if (is_string($value) && helper()->isSerialized($value)) {
                $value = unserialize($value, ['allowed_classes' => false]);
            }
        }
        // Save user
        request()->set('_user', $user);
        // Return
        return [
            'result' => true,
        ];
    }

    /**
     * Проверка доступа к интерфейсу используя логин и пароль
     *
     * @return array|bool[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function checkAccessByLoginPassword()
    {
        if (!config()->get('useLoginPassword')) {
            return [
                'result' => false,
                'message' => 'You must activate authorization by login, password in module settings',
            ];
        }
        $login = false;
        $password = false;
        // Login, password
        if (request()->header('authorization-login') && request()->header('authorization-password')) {
            $login = request()->header('authorization-login');
            $password = request()->header('authorization-password');
        } else if ($_SERVER['PHP_AUTH_USER'] && $_SERVER['PHP_AUTH_PW']) {
            $login = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
        }
        if (empty($login) || empty($password)) {
            return [
                'result' => false,
                'message' => 'Login or password are missing',
            ];
        }
        // Whitelist
        if ($this->get()['login']['whitelist'] && !in_array($login, $this->get()['login']['whitelist'])) {
            return [
                'result' => false,
                'message' => 'Authorization by login, password failed',
            ];
        }
        // User
        if (isset(UserTable::getMap()['PASSWORD'])) {
            $user = UserTable::getList(['select' => ['*', 'UF_*'], 'filter' => ['=LOGIN' => $login], 'limit' => 1]);
        } else {
            $user = UserTable::getList(['select' => ['PASSWORD', '*', 'UF_*'], 'filter' => ['=LOGIN' => $login], 'private_fields' => ['PASSWORD'], 'limit' => 1]);
        }
        if ($user->getSelectedRowsCount() === 0) {
            return [
                'result' => false,
                'message' => 'Login not found',
            ];
        }
        $user = $user->fetchRaw();
        if (!Password::equals($user['LOGIN'], $password, $user['PASSWORD'])) {
            return [
                'result' => false,
                'message' => 'Access is denied. Invalid password.',
            ];
        }
        if (!isset(UserTable::getMap()['PASSWORD'])) {
            unset($user['PASSWORD']);
        }
        $userGroups = UserTable::getUserGroupIds($user['ID']);
        // Whitelist groups
        if (!$this->get()['login']['whitelist'] && $this->get()['group']['whitelist'] && !array_intersect($userGroups, $this->get()['group']['whitelist'])) {
            return [
                'result' => false,
                'message' => 'Authorization by login, password failed',
            ];
        }
        // Requests
        $this->requestLimit($login, $userGroups);
        // Prepare user
        foreach ($user as &$value) {
            if (is_string($value) && helper()->isSerialized($value)) {
                $value = unserialize($value, ['allowed_classes' => false]);
            }
        }
        // Save user
        request()->set('_user', $user);
        // Return
        return [
            'result' => true,
        ];
    }

    /**
     * Проверка ограничения количества запросов
     *
     * @param $clientId
     * @param $groups
     *
     * @return bool
     */
    private function requestLimit($clientId, $groups)
    {
        if (!config()->get('useRequestLimit')) return true;
        $requestLimitGroup = json_decode(config()->get('requestLimit'), true);
        if ($intersect = array_intersect($groups, array_keys($requestLimitGroup))) {
            $tmp = [];
            foreach ($intersect as $id) {
                $tmp[$id] = $requestLimitGroup[$id];
            }
            $requestLimitGroup = helper()->sortByMultipleKey($tmp, ['number' => 'desc', 'period' => 'desc'])[0];
            $dateFrom = date('Y-m-d H:i:s', strtotime('now-' . $requestLimitGroup['period'] . 'seconds'));
            $dateTo = date('Y-m-d H:i:s');
            $number = journal()->getRequestLimitNumber($clientId, $dateFrom, $dateTo);
            if ($number >= $requestLimitGroup['number']) {
                response()->tooManyRequests();
            }
            journal()->add('request-limit', ['CLIENT_ID' => $clientId]);
        }
        return true;
    }

    /**
     * Используется для определения доступа к странице модуля в админ. разделе
     *
     * @param $groups
     * @param $userGroups
     *
     * @return array
     */
    public function checkAccessByGroups($groups, $userGroups)
    {
        $groups[] = helper()->adminGroupId();
        return array_intersect($groups, $userGroups);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public static function getInstance()
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
