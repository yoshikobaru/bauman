<?php
/*
|--------------------------------------------------------------------------
| Пример карты роутов
|--------------------------------------------------------------------------
|
| Внимание!
|
| - Данный файл может быть перезаписан при обновлении модуля.
| - Данный файл исключен из публичной документации.
|
| Для добавления своих роутов, создайте собственную карту.
| Например:
| /routes/sale.php
| /routes/iblock.php
| /local/api/routes/iblock.php
| И так далее, количество карт неограниченно.
|
| Карты могут располагаться вне папки модуля. Для этого необходимо указать путь к собственной папке в настройках.
| Карты будут подгружены автоматически.
|
| Совет: не желательно разбивать карту на множество файлов,
|        так как, чем больше файлов, тем больше будет происходить их подключений,
|        соответственно, затрачивается дополнительное время при запуске интерфейса.
|
| А также, не забудьте указать контроллеры для обработки роутов.
| Контроллеры могут располагаться где угодно, главное чтобы они были доступны через пространство имён.
| Вместо класса можно указать любой php-файл, который будет подключен и отработан при запросе.
|
| Для получения карты из контроллера можно воспользоваться методом request()->map()
|
| Поддерживаемые типы параметров: string, integer, float, array, bool, email, ip, domain, url
|
*/

return [
    // Тип запроса
    'GET' => [
        // Роут
        // Для получения всех параметров request()->get()
        // Для получения конкретного параметра request()->get({parameter_name})
        'example/check' => [
            // Описание роута
            // 'description' => 'GET',

            // Активность роутра
            // Необязательный параметр (по умолчанию роут активен)
            'active' => true,

            // Контроллер обрабатывающий роут
            // Данный ключ исключен из публичной документации
            // Контроллер может располагаться за пределами модуля, главное чтобы он был доступен через пространство имён
            // Где Example - класс, _get - метод класса
            'controller' => '\Artamonov\Rest\Controllers\Native\Example@_get',
            // Вместо класса можно указать любой php-файл, который будет подключен и отработан при запросе
            // 'controller' => $_SERVER['DOCUMENT_ROOT'] .  '/local/rest/controllers/file.php',

            // Безопасность участвует при запуске интерфейса
            'security' => [

                // Настройки авторизации при запросе
                'auth' => [
                    // Обязательная авторизация для роута
                    // Происходит проверка на наличие заголовков
                    // Внимание! Если глобально Авторизация по токену, тогда данный параметр будет проигнорирован
                    // Авторизация по токену: /bitrix/admin/rest-api-security.php?lang=ru
                    'required' => false, // true || false

                    // Тип авторизации используемый для роута
                    // Если login, тогда должны быть переданы заголовки Authorization-Login и Authorization-Password
                    // Если token, тогда должен быть передан заголовок Authorization-Token
                    // 'type' => 'login', // login || token
                    'type' => 'token',
                ],

                // Настройки безопасности для логинов
                // Данный ключ исключен из публичной документации
                'login' => [
                    // Доступ к методу будет доступен только для логинов из списка
                    'whitelist' => [
                        // 'admin',
                    ]
                ],

                // Настройки безопасности для токенов
                // Данный ключ исключен из публичной документации
                'token' => [
                    // Проверять срок годности токена
                    // Если checkExpire не указан в роуте, тогда для роута будет происходить проверка срока годности
                    // согласно параметра из настроек модуля, раздел Безопасность
                    // Если токен находится в белом списке, тогда параметр игнорируется
                    'checkExpire' => true, // true || false

                    // Доступ к методу будет доступен только для токенов из списка
                    'whitelist' => [
                        // 'b3bfb6b8-82dca90f-6049641b-76d957d6',
                        // 'bc95d11b-f2fdf7f4-15e869d3-882e72b5',
                        // '408f4f2e-d5a6e4a7-06930a16-8301b343',
                    ]
                ],

                // Настройки безопасности для групп
                // Данный ключ исключен из публичной документации
                'group' => [
                    // Доступ к методу будет доступен только для групп из списка
                    // Указывается ID группы
                    // Если имеется белый список логинов или токенов, тогда параметр игнорируется
                    'whitelist' => [
                        // 1,
                        // 6,
                        // 7,
                    ]
                ]
            ],
            // Параметры запроса
            // Напоминание: для получения текущих параметров в контроллере, можно воспользоваться методом request()->map()
            'parameters' => [
                'iblock_id' => [
                    // Обязательный параметр
                    // 'required' => true, // true || false
                    'type' => 'integer',
                    // Возможные значения параметра
                    'possibleValue' => [
                        '1',
                        '...',
                        '50'
                    ],
                    'description' => 'ID инфоблока'
                ],
                'active' => [
                    'type' => 'string',
                    'possibleValue' => [
                        'Y',
                        'N'
                    ],
                    'description' => 'Активность'
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Имя элемента'
                ],
                'color' => [
                    'type' => 'string',
                    'possibleValue' => [
                        'Белый',
                        'Красный'
                    ],
                    'description' => 'Свойство: Цвет'
                ],
                'form' => [
                    'type' => 'string',
                    'possibleValue' => [
                        'Круглый',
                        'Квадрат'
                    ],
                    'description' => 'Свойство: Форма'
                ],
                'fields' => [
                    'type' => 'string',
                    'possibleValue' => [
                        'preview_text',
                        'detail_text',
                        'preview_picture',
                        'form'
                    ],
                    'description' => 'Дополнительные поля'
                ],
                // Разделитель параметров
                // Обязательная часть в ключе 'separator:', после двоеточия указывается уникальный код разделителя
                // Служит только для визуального разделения параметров в документации, в админ. разделе
                'separator:example' => [
                    'required' => true,
                    'title' => 'Пример разделителя параметров'
                ],
                'sort' => [
                    'type' => 'string',
                    'description' => 'Сортировка (пример: sort=id:asc,name:asc)'
                ],
                'limit' => [
                    'type' => 'integer',
                    'possibleValue' => [
                        '1',
                        '...',
                        '150'
                    ],
                    'description' => 'Количество (по умолчанию: 10)'
                ],
                'page' => [
                    'type' => 'integer',
                    'description' => 'Страница'
                ],

            ],
            // Пример ответа
            // Необходим лишь для документации
            // #DOMAIN# и #API# будут автоматически заменены на реальные данные при выводе на экран
            // При возврате ответа клиенту response[json] автоматически декодируется
            // 'example' => [
            //     'request' => [
            //         'url' => 'http://#DOMAIN#/#API#/example/check/?iblock_id=1&sort=id:asc',
            //         'response' => [
            //             'json' => '{"page":1,"total":3,"items":[{"ID":1,"NAME":"item1"},{"ID":2,"NAME":"item2"},{"ID":3,"NAME":"item3"}]}'
            //         ]
            //     ]
            // ],
            // Настройки для поведения роута в документации
            // Данный ключ исключен из публичной документации
            'documentation' => [
                // Исключить роут
                'exclude' => [
                    // Из документации в административной части сайта
                    'admin' => false, // true || false
                    // Из документации в публичной части сайта
                    'public' => false, // true || false
                ]
            ]
        ],

        // Пример роута с использованием переменных в строке
        '/example/check/iblock/{{iblockId}}/' => [
            'controller' => '\Artamonov\Rest\Controllers\Native\Example@_get',
            'parameters' => [
                'iblockId' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID инфоблока',
                ],
            ]
        ],
        'example/check/section/{{sectionId}}' => [
            'controller' => '\Artamonov\Rest\Controllers\Native\Example@_get',
            'parameters' => [
                'sectionId' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID раздела',
                ]
            ]
        ],
        'example/check/section/{{sectionId}}/{{productId}}/view' => [
            'controller' => '\Artamonov\Rest\Controllers\Native\Example@_get',
            'parameters' => [
                'sectionId' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID раздела',
                ],
                'productId' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID товара',
                ],
            ]
        ],
    ],
    // Запросы поддерживают все возможные параметры: из строки запроса (GET) и из тела запроса
    'POST' => [
        'example/check' => [
            // 'description' => 'POST',
            // Роут отключен
            // Клиент получит ответ со статусом: 434 Requested host unavailable
            //'active' => false,
            'controller' => '\Artamonov\Rest\Controllers\Native\Example@_post',
            // Сервер ожидает запрос с типом контента application/json
            // Если тип будет отличаться, запрос будет отклонен
            'contentType' => 'application/json',
            'security' => [
                'auth' => [
                    // 'required' => true,
                    // 'type' => 'login',
                ],
                'token' => [
                    'whitelist' => [
                        // '408f4f2e-d5a6e4a7-06930a16-8301b343'
                    ]
                ]
            ],

            // Внимание: пример запроса будет отображен ниже, после описания правил для параметров

            'parameters' => [
                // Параметры: уровень 1
                'iblock_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID инфоблока'
                ],
                // Разделитель параметров
                // Обязательная часть в ключе 'separator:', после двоеточия указывается уникальный код разделителя
                // Служит только для визуального разделения параметров в документации, в админ. разделе
                'separator:user' => [
                    'title' => 'Автор для новых элементов'
                ],
                'user' => [
                    'required' => true,
                    'type' => 'array',
                    'description' => 'Автор',
                    // Параметры: уровень 2
                    'parameters' => [
                        'id' => [
                            'required' => true,
                            'type' => 'integer',
                            'description' => 'ID'
                        ],
                        'name' => [
                            'required' => true,
                            'type' => 'string',
                            'description' => 'Имя'
                        ]
                    ]
                ],
                // Разделитель параметров
                // Обязательная часть в ключе 'separator:', после двоеточия указывается уникальный код разделителя
                // Служит только для визуального разделения параметров в документации, в админ. разделе
                'separator:item' => [
                    'title' => 'Элементы для добавления в инфоблок'
                ],
                // Параметры: уровень 1
                'items' => [
                    'required' => true,
                    'type' => 'array',
                    'description' => 'Массив элементов',
                    // Параметры: уровень 2
                    'parameters' => [

                        // Параметры для элемента типа массив
                        // Правила прописываются только для одного item, но проверка происходит для каждого объекта/массива
                        // Пример применения смотрите ниже

                        [ // <---------------------------- Обращаем внимание на скобку - начало правил для одного item
                            'name' => [
                                'required' => true,
                                'type' => 'string',
                                'description' => 'Имя элемента'
                            ],
                            'sectionId' => [
                                'required' => true,
                                'type' => 'integer',
                                'description' => 'ID раздела элемента'
                            ],

                            // Разделитель параметров
                            // Обязательная часть в ключе 'separator:', после двоеточия указывается уникальный код разделителя
                            // Служит только для визуального разделения параметров в документации, в админ. разделе
                            'separator:description' => [
                                'title' => 'Описание'
                            ],
                            'description' => [
                                'required' => true,
                                'type' => 'array',
                                'description' => 'Описание элемента',
                                // Параметры: уровень 3
                                'parameters' => [
                                    'preview' => [
                                        'required' => true,
                                        'type' => 'array',
                                        'description' => 'Краткое описание',
                                        // Параметры: уровень 4
                                        'parameters' => [
                                            'ru' => [
                                                'required' => true,
                                                'type' => 'string',
                                                'description' => 'Краткое описание на русском языке',
                                            ],
                                            'en' => [
                                                'required' => true,
                                                'type' => 'string',
                                                'description' => 'Краткое описание на английском языке',
                                            ],
                                        ]
                                    ],
                                    'detail' => [
                                        'required' => true,
                                        'type' => 'array',
                                        'description' => 'Полное описание',
                                        'parameters' => [
                                            'ru' => [
                                                'required' => true,
                                                'type' => 'string',
                                                'description' => 'Полное описание на русском языке',
                                            ],
                                            'en' => [
                                                'required' => true,
                                                'type' => 'string',
                                                'description' => 'Полное описание на английском языке',
                                            ],
                                        ]
                                    ]
                                ]
                            ],
                            // Разделитель параметров
                            // Обязательная часть в ключе 'separator:', после двоеточия указывается уникальный код разделителя
                            // Служит только для визуального разделения параметров в документации, в админ. разделе
                            'separator:properties' => [
                                'title' => 'Свойства'
                            ],
                            'type' => [
                                'required' => true,
                                'type' => 'string',
                                'description' => 'Тип элемента'
                            ],
                            'color' => [
                                'required' => false,
                                'type' => 'string',
                                'description' => 'Свойство: Цвет элемента'
                            ],

                            // Разделитель параметров
                            // Обязательная часть в ключе 'separator:', после двоеточия указывается уникальный код разделителя
                            // Служит только для визуального разделения параметров в документации, в админ. разделе
                            'separator:item:files' => [
                                'title' => 'Файлы'
                            ],
                            'files' => [
                                'required' => true,
                                'type' => 'array',
                                'description' => 'Файлы элемента',
                                'parameters' => [
                                    // Описываем правила для одного файла
                                    // Но при проверке правила будут применяться ко всем файлам
                                    [ // <--------------------------------- Обращаем внимание на скобку
                                        'name' => [
                                            'required' => true,
                                            'type' => 'array',
                                            'description' => 'Название файла',
                                            'parameters' => [
                                                'ru' => [
                                                    'required' => true,
                                                    'type' => 'string',
                                                    'description' => 'Название файла на русском языке',
                                                ],
                                                'en' => [
                                                    'required' => true,
                                                    'type' => 'string',
                                                    'description' => 'Название файла на английском языке',
                                                ],
                                            ]
                                        ],
                                        'type' => [
                                            'required' => true,
                                            'type' => 'string',
                                            'description' => 'Тип файла'
                                        ],
                                        'base64' => [
                                            'required' => true,
                                            'type' => 'string',
                                            'description' => 'Base64-строка файла'
                                        ],
                                    ] // <--------------------------------- Обращаем внимание на скобку
                                ]
                            ],
                        ] // <---------------------------- Обращаем внимание на скобку - конец правил для одного item
                    ]
                ],
                'tags' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Тэги'
                ]
            ]

            /* Пример запроса для example/check
             {
                "iblock_id": 25,
                "user": {
                    "id": 4,
                    "name": "Артамонов Денис"
                },
                "items": [
                    {
                        "name": "Элемент 1",
                        "sectionId": 23,
                        "description": {
                            "preview": {
                                "ru": "Краткое описание на русском языке",
                                "en": "Краткое описание на английском языке"
                            },
                            "detail":{
                                "ru": "Полное описание на русском языке",
                                "en": "Полное описание на английском языке"
                            }
                        },
                        "type": "Тип элемента",
                        "files": [
                            {
                                "name": {
                                    "ru": "Название файла 1 на русском языке",
                                    "en": "Название файла 1 на английском языке"
                                },
                                "type": "png",
                                "base64": "......"
                            },
                            {
                                "name": {
                                    "ru": "Название файла 2 на русском языке",
                                    "en": "Название файла 2 на английском языке"
                                },
                                "type": "png",
                                "base64": "......"
                            },
                            {
                                "name": {
                                    "ru": "Название файла 3 на русском языке",
                                    "en": "Название файла 3 на английском языке"
                                },
                                "type": "png",
                                "base64": "......"
                            }
                        ]
                    },
                    {
                        "name": "Элемент 2",
                        "sectionId": 32,
                        "description": {
                            "preview": {
                                "ru": "Краткое описание на русском языке",
                                "en": "Краткое описание на английском языке"
                            },
                            "detail":{
                                "ru": "Полное описание на русском языке",
                                "en": "Полное описание на английском языке"
                            }
                        },
                        "type": "Тип элемента",
                        "files": [
                            {
                                "name": {
                                    "ru": "Название файла 1 на русском языке",
                                    "en": "Название файла 1 на английском языке"
                                },
                                "type": "png",
                                "base64": "......"
                            }
                        ]
                    }
                ],
                "tags": "Теги элементов"
            }
             */
        ]
    ],
    'PUT' => [
        'example/check' => [
            // 'description' => 'PUT',
            'controller' => '\Artamonov\Rest\Controllers\Native\Example@_put',
            'contentType' => 'application/json',
            'security' => [
                'auth' => [
                    // 'required' => true,
                    // 'type' => 'login',
                ],
            ],
            'parameters' => [
                'element_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID элемента'
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Имя элемента'
                ],
                'preview_text' => [
                    'type' => 'string',
                    'description' => 'Описание анонса'
                ],
                'detail_text' => [
                    'type' => 'string',
                    'description' => 'Детальное описание'
                ],
            ]
        ]
    ],
    'PATCH' => [
        'example/check' => [
            // 'description' => 'PATCH',
            'controller' => '\Artamonov\Rest\Controllers\Native\Example@_put',
            'contentType' => 'application/json',
            'security' => [
                'auth' => [
                    // 'required' => true,
                    // 'type' => 'login',
                ],
            ],
            'parameters' => [
                'element_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID элемента'
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Имя элемента'
                ],
                'preview_text' => [
                    'type' => 'string',
                    'description' => 'Описание анонса'
                ],
                'detail_text' => [
                    'type' => 'string',
                    'description' => 'Детальное описание'
                ],
            ]
        ]
    ],
    'DELETE' => [
        'example/check' => [
            // 'description' => 'DELETE',
            'controller' => '\Artamonov\Rest\Controllers\Native\Example@_delete',
            'security' => [
                'auth' => [
                    // 'required' => true,
                    // 'type' => 'login',
                ]
            ],
            'parameters' => [
                'element_ids' => [
                    'required' => true,
                    'type' => 'array',
                    'description' => 'ID элементов'
                ]
            ]
        ]
    ],
    'HEAD' => [
        'example/check' => [
            // 'description' => 'HEAD',
            'controller' => '\Artamonov\Rest\Controllers\Native\Example@_head'
        ]
    ]
];
