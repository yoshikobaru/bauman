<?php

/**
 * @var $APPLICATION
 */

use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$settings = require __DIR__ . '/../../../settings.php';

if (Loader::includeSharewareModule($settings['module']['id']) === Loader::MODULE_DEMO_EXPIRED) {
    LocalRedirect('/bitrix/admin/partner_modules.php?lang=' . LANG);
}

Loader::includeModule($settings['module']['id']);
page()->checkAccess('accessJournal');
page()->setTitle(loc('ArtamonovRestMenuItemJournalRequestLimit'));
$sTableID = 'tbl_' . settings()->get('config')['table']['request-limit'];
$by = '';
$order = '';
$oSort = new CAdminSorting($sTableID, $by, $order);
$lAdmin = new CAdminList($sTableID, $oSort);
if ($ids = $lAdmin->GroupAction()) {
    if ($_REQUEST['action_target'] === 'selected') {
        $ids = '*';
    }
    if ($ids) {
        switch ($_REQUEST['action']) {
            case 'delete':
                journal()->delete('request-limit', $ids);
                break;
        }
    }
}
function CheckFilter()
{
    global $arFilterFields, $lAdmin;
    foreach ($arFilterFields as $f) global $$f;
    return count($lAdmin->arFilterErrors) == 0;
}

$arFilterFields = [
    'find_id',
    'find_date_from',
    'find_date_to',
    'find_client_id',
];
$find_id = '';
$find_date_from = '';
$find_date_to = '';
$find_client_id = '';
$arFilter = [];
$lAdmin->InitFilter($arFilterFields);
InitSorting();
if (CheckFilter()) {
    $arFilter = [
        'ID' => $find_id,
        'DATETIME_FROM' => $find_date_from,
        'DATETIME_TO' => $find_date_to,
        'CLIENT_ID' => $find_client_id,
    ];
}
$arSort = [
    'field' => $by ? $by : 'ID',
    'order' => $order ? $order : 'DESC',
];
$arNavParams = (isset($_REQUEST['mode']) && $_REQUEST['mode'] === 'excel') ? false : ['nPageSize' => CAdminResult::GetNavSize($sTableID)];
$rsData = journal()->get('request-limit', $arFilter, $arSort);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(loc('ArtamonovRestRequests')));
$arHeaders = [
    [
        'id' => 'ID',
        'content' => loc('ArtamonovRestId'),
        'sort' => 'ID',
        'default' => true,
        'align' => 'right',
    ],
    [
        'id' => 'DATETIME',
        'content' => loc('ArtamonovRestDateTime'),
        'sort' => 'DATETIME',
        'default' => true,
        'align' => 'right',
    ],
    [
        'id' => 'CLIENT_ID',
        'content' => loc('ArtamonovRestClientId'),
        'sort' => 'CLIENT_ID',
        'default' => true,
        'align' => 'right',
    ],
];
$lAdmin->AddHeaders($arHeaders);

while ($ar = $rsData->fetch()) {
    $row =& $lAdmin->AddRow($ar['ID'], $ar);
    $arActions = [];
    $arActions[] = [
        'ICON' => 'delete',
        'DEFAULT' => 'N',
        'TEXT' => loc('ArtamonovRestButtonDelete'),
        'ACTION' => "if(confirm('" . GetMessageJS('ArtamonovRestConfirmDelete') . "')) " . $lAdmin->ActionDoGroup($ar['ID'], 'delete'),
    ];
    $row->AddActions($arActions);
}
$lAdmin->AddGroupActionTable(['delete' => true]);
$lAdmin->CheckListMode();
$arFilterNames = [
    'find_id' => loc('ArtamonovRestId'),
    'find_date_from' => loc('ArtamonovRestDateTime'),
    'find_client_id' => loc('ArtamonovRestClientId'),
];
$oFilter = new CAdminFilter($sTableID . '_filter', $arFilterNames);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
    <form name="filter" method="GET" action="<?= $APPLICATION->GetCurPage() ?>?">
        <input type="hidden" name="lang" value="<?= LANG ?>">
        <?php $oFilter->Begin() ?>
        <tr>
            <td><?= loc('ArtamonovRestId') ?>:</td>
            <td><?= InputType('text', 'find_id', htmlspecialcharsbx($find_id), false) ?></td>
        </tr>
        <tr>
            <td><?= loc('ArtamonovRestDateTime') ?>:</td>
            <td><?= CalendarPeriod('find_date_from', $find_date_from, 'find_date_to', $find_date_to, 'filter', 'Y') ?></td>
        </tr>
        <tr>
            <td><?= loc('ArtamonovRestClientId') ?>:</td>
            <td><?= InputType('text', 'find_client_id', htmlspecialcharsbx($find_client_id), false) ?></td>
        </tr>
        <?php
        $oFilter->Buttons(['table_id' => $sTableID, 'url' => $APPLICATION->GetCurPage(), 'form' => 'filter']);
        $oFilter->End();
        ?>
    </form>
<?php
$lAdmin->DisplayList();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
