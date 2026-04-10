<?php
/**
 * Adds REF_SUBTITLE property to the existing IBLOCK_REFERENCE_ID.
 * Run once, then DELETE this file.
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('iblock');

if (!defined('IBLOCK_REFERENCE_ID') || IBLOCK_REFERENCE_ID <= 0) {
    die("Ошибка: IBLOCK_REFERENCE_ID не задан в init.php.\n");
}

$refId = IBLOCK_REFERENCE_ID;

$db = CIBlockProperty::GetList([], ['IBLOCK_ID' => $refId, 'CODE' => 'REF_SUBTITLE']);
if ($existing = $db->Fetch()) {
    $ibProp = new CIBlockProperty();
    $ibProp->Update((int)$existing['ID'], [
        'NAME'      => 'Подзаголовок (детальная страница)',
        'ROW_COUNT' => 6,
        'COL_COUNT' => 80,
        'SORT'      => 250,
        'ACTIVE'    => 'Y',
    ]);
    echo "• Свойство REF_SUBTITLE уже существует — обновлено.\n";
} else {
    $ibProp = new CIBlockProperty();
    $res = $ibProp->Add([
        'IBLOCK_ID'     => $refId,
        'CODE'          => 'REF_SUBTITLE',
        'NAME'          => 'Подзаголовок (детальная страница)',
        'PROPERTY_TYPE' => 'S',
        'ROW_COUNT'     => 6,
        'COL_COUNT'     => 80,
        'SORT'          => 250,
        'ACTIVE'        => 'Y',
    ]);
    echo $res ? "✓ Свойство REF_SUBTITLE создано (ID=$res).\n" : "✗ Ошибка: " . $ibProp->LAST_ERROR . "\n";
}

echo "\nГотово. Удалите этот файл.\n";
