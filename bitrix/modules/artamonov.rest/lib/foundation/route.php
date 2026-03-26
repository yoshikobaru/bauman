<?php

namespace Artamonov\Rest\Foundation;


class Route
{
    private static $_instance;
    private static $_route;
    private static $_path;

    public function get()
    {
        return self::$_route;
    }

    public function path()
    {
        return self::$_path;
    }

    public function isActive()
    {
        return self::$_route['active'] === false ? false : true;
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
            self::$_path = $_SERVER['REQUEST_URI'];
            if (SITE_DIR !== '/' && defined('SITE_DIR') === true) {
                self::$_path = str_replace(SITE_DIR, '', self::$_path);
            }
            self::$_path = trim(explode('?', self::$_path)[0], '/');
            if (config()->get('pathRestApi') !== helper()->ROOT()) {
                self::$_path = str_replace(config()->get('pathRestApi') . '/', '', self::$_path);
            }
            $dir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'routes';
            $files[$dir] = array_diff(scandir($dir), ['..', '.']);
            if (config()->get('localRouteMap')) {
                $dir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . config()->get('localRouteMap');
                $files[$dir] = array_diff(scandir($dir), ['..', '.']);
            }
            foreach ($files as $dir => $items) {
                foreach ($items as $file) {
                    $file = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_array($ar = require $file)) {
                        foreach ($ar as $type => $routes) {
                            if ($_SERVER['REQUEST_METHOD'] === $type) {
                                foreach ($routes as $route => $config) {
                                    if ($params = restApiCheckRoute(self::$_path, $route)) {
                                        self::$_route = $config;
                                        if (is_array($params)) {
                                            self::$_route['paramsUri'] = $params;
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
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
