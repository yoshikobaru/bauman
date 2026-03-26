<?php

use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$settings = require __DIR__ . '/../../../settings.php';

if (Loader::includeSharewareModule($settings['module']['id']) === Loader::MODULE_DEMO_EXPIRED) {
    LocalRedirect('/bitrix/admin/partner_modules.php?lang=' . LANG);
}

LocalRedirect($settings['path']['documentationUrl'], true);
die;
