<?php
/**
 * Одноразовый скрипт: создаёт HL-блок «Logs» для логирования действий.
 * Запустить один раз: https://your-domain.ru/setup_logs.php
 * После выполнения УДАЛИТЕ этот файл с сервера.
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (!$USER->IsAdmin()) die('Access denied');

\Bitrix\Main\Loader::includeModule('highloadblock');
use Bitrix\Highloadblock\HighloadBlockTable;

echo '<pre>';

$hlName  = 'Logs';
$hlTable = 'po_logs';

// 1. Создаём HL-блок
$existing = HighloadBlockTable::getList(['filter' => ['=NAME' => $hlName]])->fetch();
$hlId = null;

if ($existing) {
    $hlId = (int)$existing['ID'];
    echo "• HL-блок '{$hlName}' уже существует (ID={$hlId}).\n";
} else {
    $res = HighloadBlockTable::add(['NAME' => $hlName, 'TABLE_NAME' => $hlTable]);
    if ($res->isSuccess()) {
        $hlId = $res->getId();
        echo "✓ HL-блок '{$hlName}' создан (ID={$hlId}).\n";
    } else {
        echo "✗ Ошибка: " . implode(', ', $res->getErrorMessages()) . "\n";
        die();
    }
}

// 2. Поля
$fields = [
    ['FIELD_NAME' => 'UF_USER_ID',     'USER_TYPE_ID' => 'integer',  'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'ID пользователя']],
    ['FIELD_NAME' => 'UF_ACTION',      'USER_TYPE_ID' => 'string',   'MANDATORY' => 'Y', 'EDIT_FORM_LABEL' => ['ru' => 'Действие']],
    ['FIELD_NAME' => 'UF_ENTITY_TYPE', 'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Тип сущности']],
    ['FIELD_NAME' => 'UF_ENTITY_ID',   'USER_TYPE_ID' => 'integer',  'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'ID сущности']],
    ['FIELD_NAME' => 'UF_IP',          'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'IP-адрес']],
    ['FIELD_NAME' => 'UF_USER_AGENT',  'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'User-Agent']],
    ['FIELD_NAME' => 'UF_DESCRIPTION', 'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Описание']],
    ['FIELD_NAME' => 'UF_DATE_CREATE', 'USER_TYPE_ID' => 'datetime', 'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Дата и время']],
];

$oUF = new CUserTypeEntity();
foreach ($fields as $field) {
    $check = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'HLBLOCK_' . $hlId, 'FIELD_NAME' => $field['FIELD_NAME']]);
    if ($check->Fetch()) {
        echo "  • Поле '{$field['FIELD_NAME']}' уже существует.\n";
        continue;
    }
    $field['ENTITY_ID'] = 'HLBLOCK_' . $hlId;
    $fid = $oUF->Add($field);
    echo $fid ? "  ✓ Поле '{$field['FIELD_NAME']}' создано.\n" : "  ✗ Ошибка создания '{$field['FIELD_NAME']}'.\n";
}

// 3. Константа в init.php
$initFile    = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
$initContent = file_get_contents($initFile);
$defineKey   = "define('HL_LOGS_ID'";

if (strpos($initContent, $defineKey) === false) {
    $anchor = "define('HL_APPLICATIONS_ID'";
    $addLine = "\ndefine('HL_LOGS_ID', {$hlId}); // Логи действий";
    if (strpos($initContent, $anchor) !== false) {
        $initContent = str_replace($anchor, $addLine . "\n" . $anchor, $initContent);
    } else {
        $initContent .= "\ndefine('HL_LOGS_ID', {$hlId});\n";
    }
    file_put_contents($initFile, $initContent);
    echo "✓ HL_LOGS_ID={$hlId} добавлена в init.php.\n";
} else {
    echo "• HL_LOGS_ID уже определена в init.php.\n";
}

echo "\n<strong>Готово.</strong> Удалите этот файл после выполнения.\n";
echo '</pre>';
