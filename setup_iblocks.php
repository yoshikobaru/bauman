<?php
/**
 * Временный setup-скрипт для создания инфоблоков.
 * Запустить один раз из браузера: http://your-site/setup_iblocks.php
 * После использования УДАЛИТЬ файл с сервера.
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

if (!$USER->IsAdmin()) {
    die('Доступ только для администратора');
}

use Bitrix\Main\Loader;
if (!Loader::includeModule('iblock')) {
    die('Модуль iblock не установлен');
}

$results = [];

// --- 1. Тип инфоблока ---
$dbIBlockType = CIBlockType::GetList([], ['ID' => 'content']);
if (!$dbIBlockType->Fetch()) {
    $ibType = new CIBlockType;
    $res = $ibType->Add([
        'ID'       => 'content',
        'SECTIONS' => 'Y',
        'IN_RSS'   => 'N',
        'LANG'     => ['ru' => ['NAME' => 'Контент сайта', 'SECTION_NAME' => 'Раздел', 'ELEMENT_NAME' => 'Элемент']],
    ]);
    $results[] = ['item' => 'Тип "content"', 'status' => $res ? 'created' : 'error: ' . $ibType->LAST_ERROR, 'id' => 'content'];
} else {
    $results[] = ['item' => 'Тип "content"', 'status' => 'exists', 'id' => 'content'];
}

// --- 2. Вспомогательные функции ---
function po_createIBlock($code, $name, $detailUrl, $sort = 100)
{
    $dbIB = CIBlock::GetList([], ['CODE' => $code, 'TYPE' => 'content']);
    if ($row = $dbIB->Fetch()) {
        return ['item' => $name, 'status' => 'exists', 'id' => (int)$row['ID']];
    }
    $ib = new CIBlock;
    $id = $ib->Add([
        'NAME'          => $name,
        'CODE'          => $code,
        'IBLOCK_TYPE_ID'=> 'content',
        'SITE_ID'       => ['s1'],
        'SORT'          => $sort,
        'ACTIVE'        => 'Y',
        'DETAIL_PAGE_URL' => $detailUrl,
    ]);
    return ['item' => $name, 'status' => ($id ? 'created' : 'error: ' . $ib->LAST_ERROR), 'id' => (int)$id];
}

function po_addProp($iblockId, $code, $name, $type, $extra = [])
{
    if (!$iblockId) return ['item' => $code, 'status' => 'skip (no iblock)'];
    $dbProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code]);
    if ($dbProp->Fetch()) {
        return ['item' => $code, 'status' => 'exists'];
    }
    $prop = new CIBlockProperty;
    $id   = $prop->Add(array_merge([
        'IBLOCK_ID'     => $iblockId,
        'CODE'          => $code,
        'NAME'          => $name,
        'PROPERTY_TYPE' => $type,
        'ACTIVE'        => 'Y',
    ], $extra));
    return ['item' => $code, 'status' => ($id ? 'created' : 'error: ' . $prop->LAST_ERROR), 'id' => $id];
}

// --- 3. Инфоблоки ---
$newsRes     = po_createIBlock('news',     'Новости',   '#SITE_DIR#news/detail/?id=#ELEMENT_ID#', 10);
$eventsRes   = po_createIBlock('events',   'События',   '#SITE_DIR#news/detail/?id=#ELEMENT_ID#', 20);
$projectsRes = po_createIBlock('projects', 'Проекты',   '#SITE_DIR#projects/detail/?id=#ELEMENT_ID#', 30);
$boardRes    = po_createIBlock('board',    'Правление', '', 40);

$results[] = $newsRes;
$results[] = $eventsRes;
$results[] = $projectsRes;
$results[] = $boardRes;

$newsId     = $newsRes['id'];
$eventsId   = $eventsRes['id'];
$projectsId = $projectsRes['id'];
$boardId    = $boardRes['id'];

// --- 4. Свойства инфоблока «События» ---
if ($eventsId) {
    $results[] = po_addProp($eventsId, 'EVENT_DATE',     'Дата события',     'S', ['USER_TYPE' => 'Date']);
    $results[] = po_addProp($eventsId, 'EVENT_LOCATION', 'Место проведения', 'S');
    $results[] = po_addProp($eventsId, 'ACCESS_LEVEL',   'Уровень доступа',  'L', [
        'VALUES' => [
            ['VALUE' => 'all',     'DEF' => 'Y', 'SORT' => 100, 'XML_ID' => 'all'],
            ['VALUE' => 'members', 'DEF' => 'N', 'SORT' => 200, 'XML_ID' => 'members'],
        ],
    ]);
}

// --- 5. Свойства инфоблока «Проекты» ---
if ($projectsId) {
    $results[] = po_addProp($projectsId, 'PROJECT_STATUS', 'Статус проекта', 'L', [
        'VALUES' => [
            ['VALUE' => 'Активный',  'DEF' => 'Y', 'SORT' => 100, 'XML_ID' => 'active'],
            ['VALUE' => 'Завершён',  'DEF' => 'N', 'SORT' => 200, 'XML_ID' => 'completed'],
        ],
    ]);
    $results[] = po_addProp($projectsId, 'PROJECT_AMOUNT', 'Сумма цели', 'S');
    $results[] = po_addProp($projectsId, 'PROJECT_LINK',   'Ссылка',     'S');
}

// --- 6. Свойства инфоблока «Правление» ---
if ($boardId) {
    $results[] = po_addProp($boardId, 'POSITION',  'Должность',   'S');
    $results[] = po_addProp($boardId, 'GRAD_YEAR', 'Год выпуска', 'S');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Setup IBlocks</title>
<style>
body{font-family:monospace;padding:20px}
table{border-collapse:collapse;width:100%}
th,td{border:1px solid #ccc;padding:8px 12px;text-align:left}
th{background:#333;color:#fff}
.created{color:green} .exists{color:#888} .error{color:red} .skip{color:#aaa}
</style>
</head>
<body>
<h2>Setup InfoBlocks — результат</h2>
<table>
<tr><th>Элемент</th><th>Статус</th><th>ID</th></tr>
<?php foreach ($results as $r):
    $cls = strpos($r['status'], 'error') !== false ? 'error'
         : (strpos($r['status'], 'skip')  !== false ? 'skip'
         : (strpos($r['status'], 'exists')!== false ? 'exists' : 'created'));
?>
<tr>
    <td><?= htmlspecialchars($r['item']) ?></td>
    <td class="<?= $cls ?>"><?= htmlspecialchars($r['status']) ?></td>
    <td><?= isset($r['id']) ? htmlspecialchars((string)$r['id']) : '' ?></td>
</tr>
<?php endforeach; ?>
</table>

<h3 style="margin-top:30px">Добавьте в <code>local/php_interface/init.php</code>:</h3>
<pre style="background:#f5f5f5;padding:16px">
define('IBLOCK_NEWS_ID',     <?= (int)$newsId ?>);
define('IBLOCK_EVENTS_ID',   <?= (int)$eventsId ?>);
define('IBLOCK_PROJECTS_ID', <?= (int)$projectsId ?>);
define('IBLOCK_BOARD_ID',    <?= (int)$boardId ?>);
</pre>
<p style="color:red"><strong>⚠ Удалите этот файл с сервера после использования!</strong></p>
</body>
</html>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
