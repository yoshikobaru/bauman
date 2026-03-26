<?php

/**
 * @var $APPLICATION
 */


use Artamonov\Rest\Foundation\RequestResponseTable;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\UI\AdminPageNavigation;
use Bitrix\Main\Web\HttpClient;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$settings = require __DIR__ . '/../../../settings.php';

if (Loader::includeSharewareModule($settings['module']['id']) === Loader::MODULE_DEMO_EXPIRED) {
    LocalRedirect('/bitrix/admin/partner_modules.php?lang=' . LANG);
}

try {
    Loader::includeModule($settings['module']['id']);
} catch (LoaderException $e) {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
    CAdminMessage::ShowMessage(['MESSAGE' => $e->getMessage(), 'TYPE' => 'ERROR']);
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
    die;
}

page()->checkAccess('accessJournal');
page()->setTitle(loc('ArtamonovRestMenuItemJournalRequestResponse'));

$pageId = $settings['module']['id'] . '_' . basename(__FILE__, '.php');
$request = Application::getInstance()->getContext()->getRequest();

if ($request->get('clear_cache') && $request->get('clear_cache') === 'Y') {
    RequestResponseTable::clearCache();
    $APPLICATION->GetCurPageParam('', ['clear_cache']);
}

// Table
$sorting = new CAdminSorting(RequestResponseTable::getTableName(), 'ID', 'DESC');
$adminList = new CAdminList(RequestResponseTable::getTableName(), $sorting);

// Group action handlers
if ($request->get('action_button') === 'delete') {
    if ($request->get('action_target') === 'selected') {
        Application::getConnection()->truncateTable(RequestResponseTable::getTableName());
        RequestResponseTable::clearCache();
    } else {
        $ids = $adminList->groupAction();
        $ids = array_map('intval', $ids);
        $r = RequestResponseTable::getList([
            'select' => [
                'ID',
            ],
            'filter' => [
                '=ID' => $ids,
            ],
        ]);
        while ($a = $r->fetchRaw()) {
            try {
                $d = RequestResponseTable::delete($a['ID']);
                if (!$d->isSuccess()) {
                    $adminList->AddGroupError(implode('<br>', $d->getErrorMessages()), $a['ID']);
                }
            } catch (Exception $e) {
                $adminList->AddGroupError($e->getMessage(), $a['ID']);
            }
        }
    }
}

// Headers
$tableMap = RequestResponseTable::getMap();
$headers = [];
foreach ($tableMap as $item) {
    if (
        $item->getName() === 'REQUEST'
        || $item->getName() === 'RESPONSE'
    ) {
        continue;
    }
    $headers[] = [
        'id' => $item->getName(),
        'content' => $item->getTitle(),
        'sort' => $item->getName(),
        'default' => true,
    ];
}
$adminList->AddHeaders($headers);

// Filter
foreach ($headers as $item) {
    $filter[] = 'find_' . strtolower($item['id']);
    if ($item['id'] === 'DATETIME') {
        $filter[] = 'find_datetime_from';
        $filter[] = 'find_datetime_to';
    }
}
$adminList->InitFilter($filter);
$filter = [];
foreach ($headers as $item) {
    if ($item['id'] === 'DATETIME') {
        if (!empty($find_datetime_from)) {
            $filter['>=DATETIME'] = $find_datetime_from;
        }
        if (!empty($find_datetime_to)) {
            $filter['<=DATETIME'] = $find_datetime_to;
        }
        continue;
    }
    $var = 'find_' . strtolower($item['id']);
    $key = $item['id'];
    $key = '=' . $key;
    if (trim($$var) !== '') {
        $filter[$key] = $$var;
    }
}

//Sorting
$sortBy = mb_strtoupper($sorting->getField());
$sortBy = !empty($sortBy) ? $sortBy : 'ID';
$sortOrder = mb_strtoupper($sorting->getOrder());
$sortOrder = $sortOrder !== 'DESC' ? 'ASC' : 'DESC';

//Navigation
$nav = new AdminPageNavigation('nav');
$cookie = md5('navPageSize:' . RequestResponseTable::getTableName());
if ($_GET['nav']) {
    $r = explode('size-', $_GET['nav'])[1];
    if ($r > 0) {
        setcookie($cookie, $r, time() + 8460000, '/');
        $_COOKIE[$cookie] = $r;
    }
}
if ($_COOKIE[$cookie] < 10) {
    $_COOKIE[$cookie] = 10;
}
$nav->setPageSize($_COOKIE[$cookie]);

// Get list
$r = RequestResponseTable::getList([
    'filter' => $filter,
    'order' => [$sortBy => $sortOrder],
    'count_total' => true,
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
    'cache' => [
        'ttl' => 3600,
    ],
]);
$nav->setRecordCount($r->getCount());
$adminList->setNavigation($nav, loc('ArtamonovRestRequests'));

// Content
while ($item = $r->fetchRaw()) {
    $row =& $adminList->AddRow($item['ID'], $item);
    if ($item['ID']) {
        $row->AddViewField('ID', '<a href="/bitrix/admin/rest-api-journal-request-response-record.php?lang=' . LANGUAGE_ID . '&id=' . $item['ID'] . '">' . $item['ID'] . '</a>');
    } else if ($item['DATETIME']) {
        $d = new \Bitrix\Main\Type\DateTime($item['DATETIME'], 'Y-m-d H:i:s');
        $row->AddViewField('DATETIME', $d->toString());
    }
    $row->AddActions([
        [
            'ICON' => 'view',
            'DEFAULT' => true,
            'TEXT' => loc('ArtamonovRestButtonView'),
            'ACTION' => $adminList->ActionRedirect('rest-api-journal-request-response-record.php?lang=' . LANGUAGE_ID . '&id=' . $item['ID']),
        ],
        [
            'ICON' => 'delete',
            'TEXT' => loc('ArtamonovRestButtonDelete'),
            'ACTION' => "if(confirm('" . GetMessageJS('ArtamonovRestConfirmDelete') . "')) " . $adminList->ActionDoGroup($item['ID'], 'delete'),
        ],
    ]);
}

// Group actions
$adminList->AddGroupActionTable(
    ['delete' => true]
);

// Toolbar
$toolbar = [
    [
        'TEXT' => loc('ArtamonovRestClearCache'),
        'TITLE' => loc('ArtamonovRestClearCache'),
        'LINK' => $APPLICATION->GetCurPageParam('clear_cache=Y', ['clear_cache', 'mode']),
    ],
];
$adminList->AddAdminContextMenu($toolbar);

// Prepare list
$adminList->CheckListMode();

// Run filter
$r = [];
foreach ($headers as $item) {
    $r[] = $item['content'];
}
$oFilter = new CAdminFilter(RequestResponseTable::getTableName() . '_filter', $r);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

if ($request->get('clear_cache') && $request->get('clear_cache') === 'Y') {
    CAdminMessage::ShowMessage(['MESSAGE' => loc('ArtamonovRestSuccessCacheClear'), 'TYPE' => 'OK']);
}
?>
    <form name="<?= $pageId ?>" method="GET" action="<?= $APPLICATION->GetCurPage() ?>">
        <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
        <?php $oFilter->Begin() ?>
        <?php foreach ($headers as $item): ?>
            <?php $var = 'find_' . strtolower($item['id']) ?>
            <tr>
                <td>
                    <?= $item['content'] ?>
                </td>
                <td>
                    <?php if ($item['id'] === 'DATETIME'): ?>
                        <?= CalendarPeriod('find_datetime_from', $find_datetime_from, 'find_datetime_to', $find_datetime_to, $pageId, 'Y') ?>
                    <?php elseif ($item['id'] === 'METHOD'): ?>
                        <select name="<?= $var ?>">
                            <option value=""><?= loc('ArtamonovRestListAll') ?></option>
                            <option value="<?= HttpClient::HTTP_HEAD ?>"<?php if ($$var === HttpClient::HTTP_HEAD) echo " selected" ?>><?= HttpClient::HTTP_HEAD ?></option>
                            <option value="<?= HttpClient::HTTP_GET ?>"<?php if ($$var === HttpClient::HTTP_GET) echo " selected" ?>><?= HttpClient::HTTP_GET ?></option>
                            <option value="<?= HttpClient::HTTP_POST ?>"<?php if ($$var === HttpClient::HTTP_POST) echo " selected" ?>><?= HttpClient::HTTP_POST ?></option>
                            <option value="<?= HttpClient::HTTP_PUT ?>"<?php if ($$var === HttpClient::HTTP_PUT) echo " selected" ?>><?= HttpClient::HTTP_PUT ?></option>
                            <option value="<?= HttpClient::HTTP_PATCH ?>"<?php if ($$var === HttpClient::HTTP_PATCH) echo " selected" ?>><?= HttpClient::HTTP_PATCH ?></option>
                            <option value="<?= HttpClient::HTTP_DELETE ?>"<?php if ($$var === HttpClient::HTTP_DELETE) echo " selected" ?>><?= HttpClient::HTTP_DELETE ?></option>
                        </select>
                    <?php else: ?>
                        <input type="text" name="<?= $var ?>" size="40" value="<?= htmlspecialcharsbx($$var) ?>">
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
        <?php
        $oFilter->Buttons([
            'table_id' => RequestResponseTable::getTableName(),
            'url' => $APPLICATION->GetCurPage(),
            'form' => $pageId,
        ]);
        $oFilter->End();
        ?>
    </form>
<?php
// Run list
$adminList->DisplayList();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
