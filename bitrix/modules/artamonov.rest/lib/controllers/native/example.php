<?php

namespace Artamonov\Rest\Controllers\Native;


class Example
{
    public function __construct()
    {
        if (!config()->get('useExampleRoute')) {
            response()->json('Showing examples is disabled in the settings');
        }
    }

    public function _get()
    {
        $this->response(__FUNCTION__);
    }

    public function _post()
    {
        $this->response(__FUNCTION__);
    }

    public function _put()
    {
        $this->response(__FUNCTION__);
    }

    public function _delete()
    {
        $this->response(__FUNCTION__);
    }

    public function _head()
    {
        $this->response(__FUNCTION__);
    }

    private function response($action)
    {
        $response = [
            'message' => 'Запрос выполнен успешно',
            'date' => date('Y-m-d H:i:s'),
            'controller' => __CLASS__,
            'action' => $action,
            'method' => request()->method(),
            'header' => request()->header(),
            'request' => request()->get(),
            'server' => $_SERVER,
        ];
        journal()->add('request-response', ['request' => request()->get(), 'response' => $response]);

        response()->json($response);

        // Заголовок - простой строкой
        //response()->json($response, 200, [], ['test-header' => 'test:test']);

        // Заголовок с поддержкой параметров - доступно с версии модуля 4.4.0
        /*response()->json($response, 200, [],
            // Дополнительные заголовки для ответа клиенту
            [
                // Описание из документации: https://www.php.net/manual/ru/function.header.php
                'test-header' => [ // название заголовка
                    'value' => 'value-header', // значение заголовка
                    'replace' => true, // true | false, - параметр заголовка
                    'http_response_code' => 302 // - параметр заголовка
                ]
            ]
        );*/
    }
}
