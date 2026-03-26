<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$settings = require __DIR__.'/../settings.php';

if (!$settings['module']['id'] || Loader::includeSharewareModule($settings['module']['id']) === Loader::MODULE_DEMO_EXPIRED) {
    return [];
}

try {
    Loc::loadLanguageFile(__FILE__);
    Loader::includeModule($settings['module']['id']);

    if (page()->checkAccess('accessMenuItems') === false) {
        return [];
    }

    return [
        [
            'parent_menu' => 'global_menu_services',
            'text' => settings()->get('module')['name'],
            'section' => settings()->get('module')['id'],
            'module_id' => settings()->get('module')['id'],
            'items_id' => 'menu_' . settings()->get('module')['id'],
            'icon' => 'clouds_menu_icon',
            'page_icon' => 'clouds_menu_icon',
            'sort' => 1,
            'items' => [

                [
                    'items_id' => 'menu_' . settings()->get('module')['id'] . '_documentation',
                    'icon' => 'learning_menu_icon',
                    'page_icon' => 'learning_menu_icon',
                    'text' => loc('ArtamonovRestMenuItemDocumentation'),
                    'items' => [
                        [
                            'items_id' => 'menu_' . settings()->get('module')['id'] . '_documentation_general',
                            'text' => loc('ArtamonovRestMenuItemDocumentationGeneral'),
                            'url' => 'rest-api-documentation-general.php?lang=' . LANG
                        ],
                        [
                            'items_id' => 'menu_' . settings()->get('module')['id'] . '_documentation_routes',
                            'text' => loc('ArtamonovRestMenuItemDocumentationRoutes'),
                            'url' => 'rest-api-documentation-routes.php?lang=' . LANG
                        ]
                    ]
                ],

                [
                    'items_id' => 'menu_' . settings()->get('module')['id'] . '_security',
                    'icon' => 'security_menu_ddos_icon',
                    'page_icon' => 'security_menu_ddos_icon',
                    'text' => loc('ArtamonovRestMenuItemSecurity'),
                    'url' => 'rest-api-security.php?lang=' . LANG
                ],

                [
                    'items_id' => 'menu_' . settings()->get('module')['id'] . '_journal',
                    'icon' => 'update_marketplace',
                    'page_icon' => 'update_marketplace',
                    'text' => loc('ArtamonovRestMenuItemJournal'),
                    'items' => [
                        [
                            'items_id' => 'menu_' . settings()->get('module')['id'] . '_journal_request_response',
                            'text' => loc('ArtamonovRestMenuItemJournalRequestResponse'),
                            'url' => 'rest-api-journal-request-response.php?lang=' . LANG,
                            'more_url' => [
                                'rest-api-journal-request-response-record.php?lang=' . LANG
                            ]
                        ],
                        [
                            'items_id' => 'menu_' . settings()->get('module')['id'] . '_journal_request_limit',
                            'text' => loc('ArtamonovRestMenuItemJournalRequestLimit'),
                            'url' => 'rest-api-journal-request-limit.php?lang=' . LANG
                        ]
                    ]
                ],

                [
                    'items_id' => 'menu_' . settings()->get('module')['id'] . '_support',
                    'icon' => 'support_menu_icon',
                    'page_icon' => 'support_menu_icon',
                    'text' => loc('ArtamonovRestMenuItemSupport'),
                    'url' => 'rest-api-support.php?lang=' . LANG
                ],

                [
                    'items_id' => 'menu_' . settings()->get('module')['id'] . '_settings',
                    'icon' => 'sys_menu_icon',
                    'page_icon' => 'sys_menu_icon',
                    'text' => loc('ArtamonovRestMenuItemSettings'),
                    'url' => 'rest-api-config.php?lang=' . LANG
                ]
            ]
        ]
    ];

} catch (\Bitrix\Main\LoaderException $e) {
    return [];
}
