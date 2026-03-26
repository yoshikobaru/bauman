<?php

namespace Artamonov\Rest\Foundation;


class Helper
{
    private static $_instance;

    public function _print($data)
    {
        if (is_array($data) || is_object($data)) {
            echo '<pre>' . print_r($data, true) . '</pre>';
        } elseif ($data) {
            echo $data . '<br>';
        }
    }

    public function sortByMultipleKey($array, $args)
    {
        usort($array, function ($a, $b) use ($args) {
            $res = 0;
            $a = (object)$a;
            $b = (object)$b;
            foreach ($args as $k => $v) {
                if ($a->$k === $b->$k) continue;

                $res = ($a->$k < $b->$k) ? -1 : 1;
                if ($v === 'desc') $res = -$res;
                break;
            }
            return $res;
        });
        return $array;
    }

    public function note($message)
    {
        echo BeginNote() . $message . EndNote();
    }

    public function login()
    {
        return 'login';
    }

    public function token()
    {
        return 'token';
    }

    public function ROOT()
    {
        return 'ROOT';
    }

    public function GET()
    {
        return 'GET';
    }

    public function POST()
    {
        return 'POST';
    }

    public function PUT()
    {
        return 'PUT';
    }

    public function PATCH()
    {
        return 'PATCH';
    }

    public function DELETE()
    {
        return 'DELETE';
    }

    public function HEAD()
    {
        return 'HEAD';
    }

    public function contentTypeJson()
    {
        return 'application/json';
    }

    public function cgi()
    {
        return mb_strpos(php_sapi_name(), 'cgi') !== false;
    }

    public function isBool($value)
    {
        return is_bool($value);
    }

    public function isArray($value)
    {
        return is_array($value);
    }

    public function isObject($value)
    {
        return is_object($value);
    }

    public function isString($value)
    {
        return is_string($value);
    }

    public function isInteger($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => PHP_INT_MAX]]);
    }

    public function isFloat($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    public function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public function isIp($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP);
    }

    public function isDomain($value)
    {
        return filter_var($value, FILTER_VALIDATE_DOMAIN);
    }

    public function isUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

    /**
     * @param string $data Значение, которое нужно проверить, является ли оно сериализованными данными
     * @param bool $strict Точная проверка для конца строки. При true строка всегда должна заканчиваться на символ ; или }. По умолчанию: true
     * @return bool
     */
    public function isSerialized(string $data, $strict = true)
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' === $data) {
            return true;
        }
        if (mb_strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = mb_substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = mb_strpos($data, ';');
            $brace = mb_strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== mb_substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === mb_strpos($data, '"')) {
                    return false;
                }
                break;
            // Or else fall through.
            case 'a':
            case 'O':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }
        return false;
    }

    public function adminGroupId()
    {
        return 1;
    }

    public function ratingVoteGroupId()
    {
        return 3;
    }

    public function ratingVoteAuthorityGroupId()
    {
        return 4;
    }

    public function generateUniqueCode($length = 10, $level = 'simple')
    {
        $characters = '';
        if ($level === 'simple') {
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else if ($level === 'medium') {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else if ($level === 'hard') {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ&%$#@+?^*';
        }
        $charactersLength = mb_strlen($characters);
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, $charactersLength - 1)];
        }
        return $code;
    }

    public function generateToken($userId = '', $userLogin = '')
    {
        $token = md5($userId . '-' . $userLogin . '=' . date('Y-m-dH:i:s') . $this->generateUniqueCode());
        $token = str_split($token, 8);
        $token = implode('-', $token);
        return $token;
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
