<?php

namespace Artamonov\Rest\Foundation;


class Controller
{
    private static $_instance;

    public function run()
    {
        if (mb_strpos(route()->get()['controller'], '.php') !== false) {
            require route()->get()['controller'];
            die;
        }
        $controller = explode('@', route()->get()['controller']);
        $action = $controller[1];
        $controller = $controller[0];
        if (!class_exists($controller)) {
            spl_autoload_register(function ($file) {
                $file = mb_strtolower($file);
                $file = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $file) . '.php';
                if (is_file($file)) {
                    require $file;
                }
            });
        }
        $controller = new $controller();
        $controller->$action();
    }

    public static function getInstance(): Controller
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
