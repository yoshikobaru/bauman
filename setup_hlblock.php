<?php
/**
 * Временный setup-скрипт для создания HL-блока Applications.
 * Запустить один раз из браузера: http://your-site/setup_hlblock.php
 * После использования УДАЛИТЬ файл с сервера.
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

if (!$USER->IsAdmin()) {
    die('Доступ только для администратора');
}

use Bitrix\Main\Loader;
if (!Loader::includeModule('highloadblock')) {
    die('Модуль highloadblock не установлен');
}

use Bitrix\Highloadblock\HighloadBlockTable;

$results = [];
$hlId = 0;

// --- Проверяем, существует ли уже HL-блок ---
$existing = HighloadBlockTable::getList(['filter' => ['=NAME' => 'Applications']])->fetch();
if ($existing) {
    $hlId = (int)$existing['ID'];
    $results[] = ['item' => 'HL-блок Applications', 'status' => 'exists', 'id' => $hlId];
} else {
    $result = HighloadBlockTable::add([
        'NAME'       => 'Applications',
        'TABLE_NAME' => 'po_applications',
    ]);
    if ($result->isSuccess()) {
        $hlId = (int)$result->getId();
        $results[] = ['item' => 'HL-блок Applications', 'status' => 'created', 'id' => $hlId];
    } else {
        $results[] = ['item' => 'HL-блок Applications', 'status' => 'error: ' . implode(', ', $result->getErrorMessages()), 'id' => 0];
    }
}

// --- Пользовательские поля HL-блока ---
$entityId = 'HLBLOCK_' . $hlId;

$ufDefs = [
    ['FIELD_NAME' => 'UF_USER_ID',    'USER_TYPE_ID' => 'integer', 'MANDATORY' => 'N', 'LABEL' => 'ID пользователя'],
    ['FIELD_NAME' => 'UF_TYPE',       'USER_TYPE_ID' => 'string',  'MANDATORY' => 'Y', 'LABEL' => 'Тип заявки'],
    ['FIELD_NAME' => 'UF_STATUS',     'USER_TYPE_ID' => 'string',  'MANDATORY' => 'N', 'LABEL' => 'Статус'],
    ['FIELD_NAME' => 'UF_DATE_CREATE','USER_TYPE_ID' => 'datetime', 'MANDATORY' => 'N', 'LABEL' => 'Дата создания'],
    ['FIELD_NAME' => 'UF_DATA',       'USER_TYPE_ID' => 'string',  'MANDATORY' => 'N', 'LABEL' => 'Данные (JSON)', 'SETTINGS' => ['SIZE' => 60, 'ROWS' => 5, 'MIN_LENGTH' => 0, 'MAX_LENGTH' => 0, 'DEFAULT_VALUE' => '']],
    ['FIELD_NAME' => 'UF_ELEMENT_ID', 'USER_TYPE_ID' => 'integer', 'MANDATORY' => 'N', 'LABEL' => 'ID элемента (события/проекта)'],
];

if ($hlId > 0) {
    $oUF = new CUserTypeEntity;
    foreach ($ufDefs as $def) {
        $existing = CUserTypeEntity::GetList(
            [],
            ['ENTITY_ID' => $entityId, 'FIELD_NAME' => $def['FIELD_NAME']]
        )->Fetch();

        if ($existing) {
            $results[] = ['item' => $def['FIELD_NAME'], 'status' => 'exists', 'id' => $existing['ID']];
            continue;
        }

        $arField = [
            'ENTITY_ID'         => $entityId,
            'FIELD_NAME'        => $def['FIELD_NAME'],
            'USER_TYPE_ID'      => $def['USER_TYPE_ID'],
            'MULTIPLE'          => 'N',
            'MANDATORY'         => $def['MANDATORY'],
            'EDIT_FORM_LABEL'   => ['ru' => $def['LABEL']],
            'LIST_COLUMN_LABEL' => ['ru' => $def['LABEL']],
            'LIST_FILTER_LABEL' => ['ru' => $def['LABEL']],
        ];
        if (!empty($def['SETTINGS'])) {
            $arField['SETTINGS'] = $def['SETTINGS'];
        }
        $id = $oUF->Add($arField);
        $results[] = ['item' => $def['FIELD_NAME'], 'status' => ($id ? 'created' : 'error: ' . $oUF->LAST_ERROR), 'id' => $id];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Setup HL-block</title>
<style>
body{font-family:monospace;padding:20px}
table{border-collapse:collapse;width:100%}
th,td{border:1px solid #ccc;padding:8px 12px;text-align:left}
th{background:#333;color:#fff}
.created{color:green} .exists{color:#888} .error{color:red}
</style>
</head>
<body>
<h2>Setup HL-block Applications — результат</h2>
<table>
<tr><th>Элемент</th><th>Статус</th><th>ID</th></tr>
<?php foreach ($results as $r):
    $cls = strpos($r['status'], 'error') !== false ? 'error'
         : (strpos($r['status'], 'exists') !== false ? 'exists' : 'created');
?>
<tr>
    <td><?= htmlspecialchars($r['item']) ?></td>
    <td class="<?= $cls ?>"><?= htmlspecialchars($r['status']) ?></td>
    <td><?= isset($r['id']) ? (int)$r['id'] : '' ?></td>
</tr>
<?php endforeach; ?>
</table>

<h3 style="margin-top:30px">Добавьте в <code>local/php_interface/init.php</code>:</h3>
<pre style="background:#f5f5f5;padding:16px">define('HL_APPLICATIONS_ID', <?= (int)$hlId ?>);</pre>
<p style="color:red"><strong>⚠ Удалите этот файл с сервера после использования!</strong></p>
</body>
</html>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
