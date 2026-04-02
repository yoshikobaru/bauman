<?php
/**
 * ВРЕМЕННЫЙ файл для разработки — удалить после завершения Блока 1.
 * Создаёт группы пользователей и UF_* поля через REST API.
 */
return [
    [
        'route'      => '/setup/install',
        'method'     => 'GET',
        'controller' => 'SetupController',
        'action'     => 'install',
        'security'   => [
            'auth' => [
                'required' => true,
                'type'     => 'token',
            ],
        ],
    ],
    [
        'route'      => '/setup/status',
        'method'     => 'GET',
        'controller' => 'SetupController',
        'action'     => 'status',
        'security'   => [
            'auth' => [
                'required' => true,
                'type'     => 'token',
            ],
        ],
    ],
];
