<?php

namespace Artamonov\Rest\Controllers\Native;


class Documentation
{
    public function __construct()
    {
        if (!config()->get('useNativeRoute')) {
            response()->json('The use of native routes is disabled in the settings');
        }
    }

    public function get()
    {
        $routes = [];
        $dir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'routes';
        $files[$dir] = array_diff(scandir($dir), ['..', '.']);
        if (config()->get('localRouteMap')) {
            $dir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . config()->get('localRouteMap');
            $files[$dir] = array_diff(scandir($dir), ['..', '.']);
        }
        foreach ($files as $dir => $items) {
            foreach ($items as $file) {
                if ($file === settings()->get('file')['example'] && !config()->get('useExampleRoute')) continue;
                $file = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_array($ar = require $file)) {
                    foreach ($ar as $type => $r) {
                        foreach ($r as $route => $config) {
                            if ($config['active'] === false || $config['documentation']['exclude']['admin'] || $config['documentation']['exclude']['public']) continue;
                            unset(
                                $config['controller'],
                                $config['security']['login'],
                                $config['security']['token'],
                                $config['security']['group'],
                                $config['documentation']
                            );
                            $this->checkSeparator($config['parameters']);
                            if ($config['example']) {
                                if ($config['example']['request']['url']) {
                                    $config['example']['request']['url'] = str_replace(['#DOMAIN#', '#API#'], [$_SERVER['HTTP_HOST'], config()->get('pathRestApi')], $config['example']['request']['url']);
                                }
                                if ($config['example']['request']['response']['json']) {
                                    $config['example']['request']['response'] = json_decode($config['example']['request']['response']['json'], true);
                                }
                            }
                            $routes[$type][$route] = $config;
                        }
                    }
                }
            }
        }
        response()->json($routes);
    }

    private function checkSeparator(&$parameters)
    {
        foreach ($parameters as $code => &$config) {
            if (mb_strpos($code, 'separator:') !== false) {
                unset($parameters[$code]);
            }
            if (isset($config['parameters'][0])) {
                $this->checkSeparator($config['parameters'][0]);
            } else if (isset($config['parameters'])) {
                $this->checkSeparator($config['parameters']);
            }
        }
    }
}
