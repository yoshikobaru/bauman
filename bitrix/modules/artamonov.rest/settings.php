<?php

/**
 * @var array $arModuleVersion
 */

use Bitrix\Main\Localization\Loc;

include __DIR__ . '/install/version.php';

return [
    'module' => [
        'id' => Loc::getMessage('ArtamonovRestModuleId'),
        'name' => Loc::getMessage('ArtamonovRestModuleName'),
        'description' => Loc::getMessage('ArtamonovRestModuleDescription'),
        'connectionString' => 'if (Bitrix\Main\Loader::includeModule(\'' . Loc::getMessage('ArtamonovRestModuleId') . '\')) \Artamonov\Rest\Foundation\Core::getInstance()->run();',
        'version' => [
            'value' => $arModuleVersion['VERSION'],
            'date' => $arModuleVersion['VERSION_DATE'],
        ],
    ],
    'mail@webco.one' => [
        'company' => Loc::getMessage('ArtamonovRestAuthorCompany'),
        'email' => Loc::getMessage('ArtamonovRestAuthorEmail'),
        'website' => Loc::getMessage('ArtamonovRestAuthorWebsite'),
        'copyright' => 'WEBCO company, ' . Loc::getMessage('ArtamonovRestAuthorWebsite'),
    ],
    'config' => [
        'prefix' => 'parameter:',
        'token' => [
            'code' => 'UF_REST_API_TOKEN',
            'expire' => [
                'code' => 'UF_API_TOKEN_EXPIRE',
                'lifetime' => '3 years'
            ]
        ],
        'table' => [
            'request-response' => Loc::getMessage('ArtamonovRestTablePrefix') . 'request_response',
            'request-limit' => Loc::getMessage('ArtamonovRestTablePrefix') . 'request_limit'
        ]
    ],
    'path' => [
        'reviews' => 'https://marketplace.1c-bitrix.ru/solutions/' . Loc::getMessage('ArtamonovRestModuleId') . '/#tab-rating-link',
        'marketplace' => 'https://marketplace.1c-bitrix.ru/solutions/' . Loc::getMessage('ArtamonovRestModuleId') . '/',
        'documentationUrl' => Loc::getMessage('ArtamonovRestAuthorWebsite') . '/docs/bitrix/modules/' . Loc::getMessage('ArtamonovRestModuleId') . '/',
    ],
    'file' => [
        'native' => '_native.php',
        'example' => '_example.php',
    ],
];
