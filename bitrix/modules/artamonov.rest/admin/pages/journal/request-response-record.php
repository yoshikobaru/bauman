<?php

use Artamonov\Rest\Foundation\RequestResponseTable;
use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$settings = require __DIR__ . '/../../../settings.php';

if (Loader::includeSharewareModule($settings['module']['id']) === Loader::MODULE_DEMO_EXPIRED) {
    LocalRedirect('/bitrix/admin/partner_modules.php?lang=' . LANG);
}

Loader::includeModule($settings['module']['id']);
page()->checkAccess('accessJournal');
page()->setTitle(loc('ArtamonovRestRequest') . ' №' . (int)$_GET['id']);
$tabControl = new CAdminTabControl(
    'tabControl',
    [
        ['DIV' => 'tab-1', 'TAB' => loc('ArtamonovRestTabMainTitle'), 'TITLE' => loc('ArtamonovRestTabMainDescription')],
        ['DIV' => 'tab-2', 'TAB' => loc('ArtamonovRestTabRequestTitle'), 'TITLE' => loc('ArtamonovRestTabRequestDescription')],
        ['DIV' => 'tab-3', 'TAB' => loc('ArtamonovRestTabResponseTitle'), 'TITLE' => loc('ArtamonovRestTabResponseDescription')],
    ]
);

$arResult = RequestResponseTable::getById($_GET['id'])->fetch();
$arResult['REQUEST'] = json_decode($arResult['REQUEST'], true);
$arResult['RESPONSE'] = json_decode($arResult['RESPONSE'], true);
$context = new CAdminContextMenu([
    [
        'TEXT' => loc('ArtamonovRestButtonBackText'),
        'TITLE' => loc('ArtamonovRestButtonBackTitle'),
        'LINK' => 'rest-api-journal-request-response.php?lang=' . LANGUAGE_ID,
        'ICON' => 'btn_list',
    ],
]);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
$context->Show();
$tabControl->Begin();
?>
<?php $tabControl->BeginNextTab() ?>
    <tr>
        <td width="45%" valign="middle"><?= loc('ArtamonovRestId') ?>
        <td>
        <td width="55%" valign="middle"><?= $arResult['ID'] ?>
        <td>
    </tr>
    <tr>
        <td width="45%" valign="middle"><?= loc('ArtamonovRestMethod') ?>
        <td>
        <td width="55%" valign="middle"><?= $arResult['METHOD'] ?>
        <td>
    </tr>
    <tr>
        <td width="45%" valign="middle"><?= loc('ArtamonovRestDateTime') ?>
        <td>
        <td width="55%" valign="middle"><?= $arResult['DATETIME'] ?>
        <td>
    </tr>
    <tr>
        <td width="45%" valign="middle"><?= loc('ArtamonovRestIp') ?>
        <td>
        <td width="55%" valign="middle"><?= $arResult['IP'] ?>
        <td>
    </tr>
<?php if ($arResult['CLIENT_ID']): ?>
    <tr>
        <td width="45%" valign="middle"><?= loc('ArtamonovRestClientId') ?>
        <td>
        <td width="55%" valign="middle"><?= $arResult['CLIENT_ID'] ?>
        <td>
    </tr>
<?php endif ?>
<?php if ($arResult['REQUEST']): ?>
    <?php $tabControl->BeginNextTab() ?>
    <tr>
        <td colspan="4">
            <?php helper()->_print($arResult['REQUEST']) ?>
        </td>
    </tr>
<?php endif ?>
<?php if ($arResult['RESPONSE']): ?>
    <?php $tabControl->BeginNextTab() ?>
    <tr>
        <td colspan="4"><?php helper()->_print($arResult['RESPONSE']) ?></td>
    </tr>
<?php endif ?>
<?php
$tabControl->End();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
