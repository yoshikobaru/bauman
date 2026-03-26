<?php

namespace Artamonov\Rest\Foundation;


class Request
{
    private static $_instance;
    private static $_parameters;
    private $headers = null;
    private $ip = null;

    /**
     * Получение данных текущего запроса
     *
     * @param string $code
     *
     * @return array | string
     */
    public function get($code = '')
    {
        return $code ? self::$_parameters[$code] : self::$_parameters;
    }

    /**
     * Сохранение информации в текущий запрос
     *
     * @param $code
     * @param $value
     */
    public function set($code, $value)
    {
        self::$_parameters[$code] = $value;
    }

    /**
     * Метод для обновления данных запроса
     * Например, после преобразования данных
     *
     * @param $request
     */
    public function update($request)
    {
        self::$_parameters = $request;
    }

    /**
     * Получение заголовков текущего запроса
     *
     * @param string $code
     *
     * @return array | string
     */
    public function header($code = '')
    {
        if ($this->headers === null) {
            if (!function_exists('apache_request_headers')) {
                foreach ($_SERVER as $name => $value) {
                    if (mb_substr($name, 0, 5) === 'HTTP_') {
                        $key = str_replace(' ', '-', mb_strtolower(str_replace('_', ' ', mb_substr($name, 5))));
                        $this->headers[$key] = $value;
                    }
                }
            } else {
                $this->headers = array_change_key_case(apache_request_headers(), CASE_LOWER);
            }
        }
        return !empty($code) ? $this->headers[$code] : $this->headers;
    }

    /**
     * Метод текущего запроса
     *
     * @return mixed
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * IP-адрес клиента текущего запроса
     *
     * @return mixed
     */
    public function ip()
    {
        if ($this->ip === null) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $this->ip = $_SERVER['HTTP_CLIENT_IP'];
            } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $this->ip = $_SERVER['REMOTE_ADDR'];
            }
        }
        return $this->ip;
    }

    /**
     * Путь по которому был совершен текущий запрос
     *
     * @return mixed
     */
    public function path()
    {
        return route()->path();
    }

    /**
     * Карта роута предназначенная для текущего запроса
     *
     * @return mixed
     */
    public function map()
    {
        return route()->get();
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
            self::$_parameters = $_GET;
            if (
                $_SERVER['CONTENT_TYPE'] === 'application/json'
                ||
                strpos($_SERVER['CONTENT_TYPE'], '/json') !== false
                ||
                strpos($_SERVER['CONTENT_TYPE'], '+json') !== false
            ) {
                $ar = json_decode(file_get_contents('php://input'), true);
                foreach ($ar as $k => $v) {
                    self::$_parameters[$k] = $v;
                }
            } else {
                if ($_SERVER['REQUEST_METHOD'] === helper()->POST()) {
                    self::$_parameters = array_merge(self::$_parameters, $_POST);
                } else {
                    parse_str(file_get_contents('php://input'), $data);
                    self::$_parameters = array_merge(self::$_parameters, $data);
                }
            }
            if (route()->get()['paramsUri']) {
                self::$_parameters = array_merge(self::$_parameters, route()->get()['paramsUri']);
            }
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
