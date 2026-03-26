<?php

$MESS = [
    'GET:docs' => 'Документация в формате JSON. Может предоставляться сторонним клиентам.',
    'GET:user' => 'Получение данных пользователя. Можно передать любой из параметров.',
    'POST:user' => 'Создание нового пользователя. Доступны все параметры согласно <a href="https://dev.1c-bitrix.ru/api_help/main/reference/cuser/add.php" target="_blank">документации</a>. При обработке все параметры приводятся к верхнему регистру, то есть confirm_password равнозначно CONFIRM_PASSWORD.',
    'PUT:user' => 'Обновление данных пользователя. Для идентификации пользователя можно передать любой из параметров: id, login, token. Доступны все параметры согласно <a href="https://dev.1c-bitrix.ru/api_help/main/reference/cuser/update.php" target="_blank">документации</a>. При обработке все параметры приводятся к верхнему регистру, то есть confirm_password равнозначно CONFIRM_PASSWORD.',
    'DELETE:user' => 'Удаление пользователя. Можно передать любой из параметров.',
    'GET:user/token' => 'Получение токена пользователя',
    'POST:user/token' => 'Генерация токена для пользователя',
    'DELETE:user/token' => 'Удаление токена пользователя',

    'GET:provider' => 'Получение информации о внутреннем апи-методе платформы.<br><a href="https://dev.1c-bitrix.ru/api_help/" target="_blank">Документация по старому ядру</a><br><a href="https://dev.1c-bitrix.ru/api_d7/" target="_blank">Документация по новому ядру (D7)</a>',
    'POST:provider' => 'Исполнение внутреннего апи-метода платформы.<br><a href="https://dev.1c-bitrix.ru/api_help/" target="_blank">Документация по старому ядру</a><br><a href="https://dev.1c-bitrix.ru/api_d7/" target="_blank">Документация по новому ядру (D7)</a>',

    'parameter:id' => 'ID пользователя',
    'parameter:login' => 'Логин пользователя',
    'parameter:email' => 'E-Mail адрес пользователя',
    'parameter:password' => 'Пароль пользователя',
    'parameter:token' => 'Токен пользователя',

    'parameter:class' => 'Название класса либо его неймспейс',
    'parameter:method' => 'Метод класса',
    'parameter:methodParameters' => 'Параметры метода класса',
    'parameter:methodCallbackFunction' => 'Функция, которая будет применена к результату работы метода. По умолчанию, будет применена функция fetch для старого ядра, и fetchRaw для ядра D7.',
];
