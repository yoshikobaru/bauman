<?php

Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);

/*
|--------------------------------------------------------------------------
| Встроенные роуты
|--------------------------------------------------------------------------
|
| Внимание!
|
| - Файл может быть перезаписан при обновлении модуля.
| - Роуты модулем нигде не используются.
| - Роуты нужны лишь для разработчиков, чтобы сократить время разработки.
| - Роуты могут быть перекрыты собственной картой.
| - Активностью карты можно управлять из интерфейса модуля.
|
|--------------------------------------------------------------------------
*/

return [
    'GET' => [
        'docs' => [
            'description' => loc('GET:docs'),
            'controller' => '\Artamonov\Rest\Controllers\Native\Documentation@get',
            'documentation' => [
                'exclude' => [
                    'public' => true
                ]
            ]
        ],
        'user' => [
            'description' => loc('GET:user'),
            'controller' => '\Artamonov\Rest\Controllers\Native\User@get',
            'security' => [
                'auth' => [
                    'required' => true,
                    'type' => 'token'
                ],
                'token' => [
                    'checkExpire' => true
                ]
            ],
            'parameters' => [
                'id' => [
                    'type' => 'integer',
                    'description' => loc('parameter:id')
                ],
                'login' => [
                    'type' => 'string',
                    'description' => loc('parameter:login')
                ],
                'token' => [
                    'type' => 'string',
                    'description' => loc('parameter:token')
                ],
            ],
        ],
        'user/token' => [
            'description' => loc('GET:user/token'),
            'controller' => '\Artamonov\Rest\Controllers\Native\Token@get',
            'parameters' => [
                'login' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:login')
                ],
                'password' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:password')
                ]
            ]
        ],
        'provider' => [
            'description' => loc('GET:provider'),
            'controller' => '\Artamonov\Rest\Controllers\Native\Provider@get',
            'security' => [
                'auth' => [
                    'required' => true,
                    'type' => 'token'
                ],
                'token' => [
                    'checkExpire' => true
                ]
            ],
            'parameters' => [
                'class' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:class')
                ],
                'method' => [
                    'required' => false,
                    'type' => 'string',
                    'description' => loc('parameter:method')
                ],
            ]
        ],
    ],
    'POST' => [
        'user' => [
            'description' => loc('POST:user'),
            'controller' => '\Artamonov\Rest\Controllers\Native\User@create',
            'contentType' => 'application/json',
            'security' => [
                'auth' => [
                    'required' => true,
                    'type' => 'token'
                ],
                'token' => [
                    'checkExpire' => true
                ]
            ],
            'parameters' => [
                'login' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:login')
                ],
                'email' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:email')
                ],
                'password' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:password')
                ]
            ],
        ],
        'user/token' => [
            'description' => loc('POST:user/token'),
            'controller' => '\Artamonov\Rest\Controllers\Native\Token@create',
            'contentType' => 'application/json',
            'parameters' => [
                'login' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:login')
                ],
                'password' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:password')
                ]
            ]
        ],
        'provider' => [
            'description' => loc('POST:provider'),
            'controller' => '\Artamonov\Rest\Controllers\Native\Provider@exec',
            'contentType' => 'application/json',
            'security' => [
                'auth' => [
                    'required' => true,
                    'type' => 'token'
                ],
                'token' => [
                    'checkExpire' => true
                ]
            ],
            'parameters' => [
                'class' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:class')
                ],
                'method' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:method')
                ],
                'parameters' => [
                    'required' => false,
                    'type' => 'array',
                    'description' => loc('parameter:methodParameters')
                ],
                'callback' => [
                    'required' => false,
                    'type' => 'string',
                    'description' => loc('parameter:methodCallbackFunction')
                ]
            ]
        ],
    ],
    'PUT' => [
        'user' => [
            'description' => loc('PUT:user'),
            'controller' => '\Artamonov\Rest\Controllers\Native\User@update',
            'contentType' => 'application/json',
            'security' => [
                'auth' => [
                    'required' => true,
                    'type' => 'token'
                ],
                'token' => [
                    'checkExpire' => true
                ]
            ],
            'parameters' => [
                'id' => [
                    'required' => false,
                    'type' => 'integer',
                    'description' => loc('parameter:id')
                ],
                'login' => [
                    'required' => false,
                    'type' => 'string',
                    'description' => loc('parameter:login')
                ],
                'token' => [
                    'required' => false,
                    'type' => 'string',
                    'description' => loc('parameter:token')
                ],
            ],
        ]
    ],
    'DELETE' => [
        'user' => [
            'description' => loc('DELETE:user'),
            'controller' => '\Artamonov\Rest\Controllers\Native\User@delete',
            'contentType' => 'application/json',
            'security' => [
                'auth' => [
                    'required' => true,
                    'type' => 'token'
                ],
                'token' => [
                    'checkExpire' => true
                ]
            ],
            'parameters' => [
                'id' => [
                    'required' => false,
                    'type' => 'integer',
                    'description' => loc('parameter:id')
                ],
                'login' => [
                    'required' => false,
                    'type' => 'string',
                    'description' => loc('parameter:login')
                ],
                'token' => [
                    'required' => false,
                    'type' => 'string',
                    'description' => loc('parameter:token')
                ],
            ],
        ],
        'user/token' => [
            'description' => loc('DELETE:user/token'),
            'controller' => '\Artamonov\Rest\Controllers\Native\Token@delete',
            'contentType' => 'application/json',
            'parameters' => [
                'login' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:login')
                ],
                'password' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => loc('parameter:password')
                ]
            ]
        ]
    ]
];
