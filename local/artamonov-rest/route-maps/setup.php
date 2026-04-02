<?php
/**
 * ВРЕМЕННЫЙ файл для разработки — удалить после завершения Блока 1.
 * Создаёт группы пользователей и UF_* поля через REST API.
 */

$controllersDir = $_SERVER['DOCUMENT_ROOT'] . '/local/artamonov-rest/controllers/';

return [
    [
        'route'      => '/setup/install',
        'method'     => 'GET',
        'controller' => $controllersDir . 'SetupController.php',
        'action'     => 'install',
    ],
    [
        'route'      => '/setup/status',
        'method'     => 'GET',
        'controller' => $controllersDir . 'SetupController.php',
        'action'     => 'status',
    ],
];
