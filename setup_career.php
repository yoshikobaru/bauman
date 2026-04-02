<?php
/**
 * Одноразовый скрипт: создаёт HL-блоки Vacancies (Вакансии) и Resumes (Резюме).
 * Запустить один раз: https://your-domain.ru/setup_career.php
 * После выполнения УДАЛИТЕ этот файл с сервера.
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (!$USER->IsAdmin()) {
    die('Access denied');
}

\Bitrix\Main\Loader::includeModule('highloadblock');
use Bitrix\Highloadblock\HighloadBlockTable;

echo '<pre>';

$hlBlocks = [
    'Vacancies' => [
        'name'   => 'Vacancies',
        'table'  => 'po_vacancies',
        'fields' => [
            ['FIELD_NAME' => 'UF_COMPANY',       'USER_TYPE_ID' => 'string',   'MANDATORY' => 'Y', 'EDIT_FORM_LABEL' => ['ru' => 'Компания']],
            ['FIELD_NAME' => 'UF_POSITION',      'USER_TYPE_ID' => 'string',   'MANDATORY' => 'Y', 'EDIT_FORM_LABEL' => ['ru' => 'Должность']],
            ['FIELD_NAME' => 'UF_DESCRIPTION',   'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Описание вакансии'], 'SETTINGS' => ['SIZE' => 5]],
            ['FIELD_NAME' => 'UF_REQUIREMENTS',  'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Требования']],
            ['FIELD_NAME' => 'UF_CONTACT_EMAIL', 'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Email для откликов']],
            ['FIELD_NAME' => 'UF_USER_ID',       'USER_TYPE_ID' => 'integer',  'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'ID пользователя']],
            ['FIELD_NAME' => 'UF_STATUS',        'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Статус'], 'SETTINGS' => ['DEFAULT_VALUE' => 'pending']],
            ['FIELD_NAME' => 'UF_DATE_CREATE',   'USER_TYPE_ID' => 'datetime', 'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Дата создания']],
        ],
    ],
    'Resumes' => [
        'name'   => 'Resumes',
        'table'  => 'po_resumes',
        'fields' => [
            ['FIELD_NAME' => 'UF_USER_ID',       'USER_TYPE_ID' => 'integer',  'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'ID пользователя']],
            ['FIELD_NAME' => 'UF_POSITION',      'USER_TYPE_ID' => 'string',   'MANDATORY' => 'Y', 'EDIT_FORM_LABEL' => ['ru' => 'Желаемая должность']],
            ['FIELD_NAME' => 'UF_SKILLS',        'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Навыки']],
            ['FIELD_NAME' => 'UF_EXPERIENCE',    'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Опыт работы']],
            ['FIELD_NAME' => 'UF_CONTACT_EMAIL', 'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Email']],
            ['FIELD_NAME' => 'UF_STATUS',        'USER_TYPE_ID' => 'string',   'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Статус'], 'SETTINGS' => ['DEFAULT_VALUE' => 'pending']],
            ['FIELD_NAME' => 'UF_DATE_CREATE',   'USER_TYPE_ID' => 'datetime', 'MANDATORY' => 'N', 'EDIT_FORM_LABEL' => ['ru' => 'Дата создания']],
        ],
    ],
];

$initFile    = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
$initContent = file_get_contents($initFile);
$createdIds  = [];

foreach ($hlBlocks as $key => $hlConfig) {
    // Проверяем существование
    $existing = HighloadBlockTable::getList(['filter' => ['=NAME' => $hlConfig['name']]])->fetch();
    if ($existing) {
        echo "• HL-блок '{$hlConfig['name']}' уже существует (ID={$existing['ID']}).\n";
        $createdIds[$key] = (int)$existing['ID'];
    } else {
        $res = HighloadBlockTable::add([
            'NAME'       => $hlConfig['name'],
            'TABLE_NAME' => $hlConfig['table'],
        ]);
        if ($res->isSuccess()) {
            $hlId = $res->getId();
            $createdIds[$key] = $hlId;
            echo "✓ HL-блок '{$hlConfig['name']}' создан (ID={$hlId}).\n";
        } else {
            echo "✗ Ошибка создания '{$hlConfig['name']}': " . implode(', ', $res->getErrorMessages()) . "\n";
            continue;
        }
    }

    // Добавляем поля
    $hlId = $createdIds[$key];
    $oUF = new CUserTypeEntity();
    foreach ($hlConfig['fields'] as $field) {
        $fieldName = $field['FIELD_NAME'];
        $existField = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'HLBLOCK_' . $hlId, 'FIELD_NAME' => $fieldName]);
        if ($existField->Fetch()) {
            echo "  • Поле '{$fieldName}' уже существует.\n";
            continue;
        }
        $field['ENTITY_ID'] = 'HLBLOCK_' . $hlId;
        if (isset($field['SETTINGS']) && is_array($field['SETTINGS'])) {
            // settings already set
        }
        $fieldId = $oUF->Add($field);
        echo $fieldId ? "  ✓ Поле '{$fieldName}' создано.\n" : "  ✗ Ошибка создания '{$fieldName}'.\n";
    }
}

// Обновляем init.php
$defineVacKey  = "define('HL_VACANCIES_ID'";
$defineResKey  = "define('HL_RESUMES_ID'";

$insertAfter = "define('HL_APPLICATIONS_ID', 2);";
if (!empty($createdIds['Vacancies']) && strpos($initContent, $defineVacKey) === false) {
    $addLine = "\ndefine('HL_VACANCIES_ID', {$createdIds['Vacancies']});";
    if (strpos($initContent, $insertAfter) !== false) {
        $initContent = str_replace($insertAfter, $insertAfter . $addLine, $initContent);
        echo "✓ Константа HL_VACANCIES_ID={$createdIds['Vacancies']} добавлена в init.php.\n";
    } else {
        echo "⚠ Не найдена точка вставки в init.php. Добавьте вручную: define('HL_VACANCIES_ID', {$createdIds['Vacancies']});\n";
    }
} else {
    echo "• HL_VACANCIES_ID уже определена в init.php.\n";
}

if (!empty($createdIds['Resumes']) && strpos($initContent, $defineResKey) === false) {
    $addLine = "\ndefine('HL_RESUMES_ID', {$createdIds['Resumes']});";
    $anchorKey = "define('HL_VACANCIES_ID'";
    if (strpos($initContent, $anchorKey) !== false) {
        // Insert after vacancies define
        $pos = strpos($initContent, $anchorKey);
        $eol = strpos($initContent, "\n", $pos);
        $initContent = substr($initContent, 0, $eol) . $addLine . substr($initContent, $eol);
    } elseif (strpos($initContent, $insertAfter) !== false) {
        $initContent = str_replace($insertAfter, $insertAfter . $addLine, $initContent);
    }
    echo "✓ Константа HL_RESUMES_ID={$createdIds['Resumes']} добавлена в init.php.\n";
} else {
    echo "• HL_RESUMES_ID уже определена в init.php.\n";
}

file_put_contents($initFile, $initContent);

echo "\n<strong>Готово.</strong> Удалите этот файл после выполнения.\n";
echo '</pre>';
