<?php
/**
 * ВРЕМЕННЫЙ скрипт — удалить после выполнения Блока 1.
 * Создаёт группы пользователей и UF_* поля.
 * Запускать однократно: https://bauman-polytech.ru/local/setup_block1.php
 */

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!$USER->IsAdmin()) {
    die('Доступ запрещён. Войдите как администратор.');
}

$groups = [
    'PO_REGISTERED'     => 'Зарегистрированный (без членства)',
    'PO_MEMBER_BASIC'   => 'Член общества — Базовое',
    'PO_MEMBER_PREMIUM' => 'Член общества — Привилегированное',
    'PO_PARTNER'        => 'Партнёр (юр. лицо)',
    'PO_MODERATOR'      => 'Модератор / Сотрудник',
];

$userFields = [
    ['FIELD_NAME' => 'UF_MEMBERSHIP_TYPE',    'USER_TYPE_ID' => 'string',   'SORT' => 100, 'LABEL' => 'Тип членства',           'HINT' => 'basic / premium / partner / honorary'],
    ['FIELD_NAME' => 'UF_MEMBERSHIP_STATUS',  'USER_TYPE_ID' => 'string',   'SORT' => 110, 'LABEL' => 'Статус заявки',          'HINT' => 'pending / active / rejected'],
    ['FIELD_NAME' => 'UF_MEMBERSHIP_EXPIRES', 'USER_TYPE_ID' => 'datetime', 'SORT' => 120, 'LABEL' => 'Членство до',            'HINT' => ''],
    ['FIELD_NAME' => 'UF_GRADUATE_YEAR',      'USER_TYPE_ID' => 'integer',  'SORT' => 200, 'LABEL' => 'Год окончания МГТУ',     'HINT' => ''],
    ['FIELD_NAME' => 'UF_GRADUATE_DEPT',      'USER_TYPE_ID' => 'string',   'SORT' => 210, 'LABEL' => 'Кафедра / факультет',   'HINT' => ''],
    ['FIELD_NAME' => 'UF_TELEGRAM',           'USER_TYPE_ID' => 'string',   'SORT' => 220, 'LABEL' => 'Telegram',               'HINT' => ''],
    ['FIELD_NAME' => 'UF_DIPLOMA_SERIES',     'USER_TYPE_ID' => 'string',   'SORT' => 300, 'LABEL' => 'Серия диплома',          'HINT' => ''],
    ['FIELD_NAME' => 'UF_DIPLOMA_NUMBER',     'USER_TYPE_ID' => 'string',   'SORT' => 310, 'LABEL' => 'Номер диплома',          'HINT' => ''],
    ['FIELD_NAME' => 'UF_DIPLOMA_DATE',       'USER_TYPE_ID' => 'string',   'SORT' => 320, 'LABEL' => 'Дата выдачи диплома',   'HINT' => ''],
    ['FIELD_NAME' => 'UF_COMPANY_ID',         'USER_TYPE_ID' => 'integer',  'SORT' => 400, 'LABEL' => 'ID компании (юр. лицо)', 'HINT' => ''],
];

// — Получаем существующие группы —
$existingGroups = [];
$dbGroups = CGroup::GetList([], []);
while ($g = $dbGroups->Fetch()) {
    $existingGroups[$g['STRING_ID']] = (int)$g['ID'];
}

// — Получаем существующие UF поля —
$existingFields = [];
$dbFields = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER']);
while ($f = $dbFields->Fetch()) {
    $existingFields[$f['FIELD_NAME']] = (int)$f['ID'];
}

$groupResults = [];
$fieldResults = [];

// — Создаём группы —
foreach ($groups as $code => $name) {
    if (isset($existingGroups[$code])) {
        $groupResults[] = ['code' => $code, 'status' => 'exists', 'id' => $existingGroups[$code]];
        continue;
    }
    $oGroup = new CGroup();
    $id = $oGroup->Add(['ACTIVE' => 'Y', 'NAME' => $name, 'STRING_ID' => $code]);
    if ($id) {
        $groupResults[] = ['code' => $code, 'status' => 'created', 'id' => (int)$id];
    } else {
        $groupResults[] = ['code' => $code, 'status' => 'error', 'error' => $oGroup->LAST_ERROR];
    }
}

// — Создаём UF поля —
$oUF = new CUserTypeEntity();
foreach ($userFields as $field) {
    $name = $field['FIELD_NAME'];
    if (isset($existingFields[$name])) {
        $fieldResults[] = ['name' => $name, 'status' => 'exists', 'id' => $existingFields[$name]];
        continue;
    }
    $id = $oUF->Add([
        'ENTITY_ID'         => 'USER',
        'FIELD_NAME'        => $name,
        'USER_TYPE_ID'      => $field['USER_TYPE_ID'],
        'XML_ID'            => $name,
        'SORT'              => $field['SORT'],
        'MULTIPLE'          => 'N',
        'MANDATORY'         => 'N',
        'EDIT_FORM_LABEL'   => ['ru' => $field['LABEL']],
        'LIST_COLUMN_LABEL' => ['ru' => $field['LABEL']],
        'LIST_FILTER_LABEL' => ['ru' => $field['LABEL']],
        'HELP_MESSAGE'      => ['ru' => $field['HINT']],
    ]);
    if ($id) {
        $fieldResults[] = ['name' => $name, 'status' => 'created', 'id' => (int)$id];
    } else {
        $fieldResults[] = ['name' => $name, 'status' => 'error', 'error' => $oUF->LAST_ERROR];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Setup Блок 1 — Группы и поля</title>
<style>
body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
h2 { color: #569cd6; }
.ok      { color: #4ec9b0; }
.exists  { color: #dcdcaa; }
.error   { color: #f44747; }
table { border-collapse: collapse; margin-bottom: 30px; }
td, th { padding: 6px 14px; border: 1px solid #444; text-align: left; }
th { background: #333; color: #9cdcfe; }
</style>
</head>
<body>
<h2>Блок 1: Группы пользователей</h2>
<table>
<tr><th>Код</th><th>Статус</th><th>ID</th></tr>
<?php foreach ($groupResults as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['code']) ?></td>
    <td class="<?= $r['status'] ?>"><?= $r['status'] ?><?= isset($r['error']) ? ': ' . htmlspecialchars($r['error']) : '' ?></td>
    <td><?= isset($r['id']) ? $r['id'] : '—' ?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>UF_* поля пользователей</h2>
<table>
<tr><th>Поле</th><th>Статус</th><th>ID</th></tr>
<?php foreach ($fieldResults as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td class="<?= $r['status'] ?>"><?= $r['status'] ?><?= isset($r['error']) ? ': ' . htmlspecialchars($r['error']) : '' ?></td>
    <td><?= isset($r['id']) ? $r['id'] : '—' ?></td>
</tr>
<?php endforeach; ?>
</table>

<p style="color:#888">Скопируй ID групп и сообщи — я обновлю init.php с реальными значениями.</p>
<p style="color:#f44747">❗ Удали этот файл после использования: <code>/local/setup_block1.php</code></p>
</body>
</html>
