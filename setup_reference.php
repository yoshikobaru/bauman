<?php
/**
 * One-time setup: create "Референс-визиты" InfoBlock + properties.
 * Run once, then DELETE this file.
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('iblock');

$ibType = 'po_catalog';

// --- IBlock ---
$existing = CIBlock::GetList([], ['TYPE' => $ibType, 'CODE' => 'po_reference'])->Fetch();

if ($existing) {
    $refId = (int)$existing['ID'];
    echo "• Инфоблок 'Референс-визиты' уже существует (ID=$refId).\n";
} else {
    $ib = new CIBlock();
    $refId = $ib->Add([
        'LID'             => 's1',
        'IBLOCK_TYPE_ID'  => $ibType,
        'CODE'            => 'po_reference',
        'NAME'            => 'Референс-визиты',
        'SORT'            => 400,
        'ACTIVE'          => 'Y',
        'LIST_PAGE_URL'   => '/reference/',
        'DETAIL_PAGE_URL' => '/reference/#ELEMENT_CODE#/',
        'INDEX_ELEMENT'   => 'N',
        'INDEX_SECTION'   => 'N',
    ]);
    if ($refId) {
        echo "✓ Инфоблок 'Референс-визиты' создан (ID=$refId).\n";
    } else {
        echo "✗ Ошибка создания инфоблока: " . $ib->LAST_ERROR . "\n";
        die();
    }
}

// --- Свойства ---
$properties = [
    [
        'CODE'          => 'REF_STATUS',
        'NAME'          => 'Статус',
        'PROPERTY_TYPE' => 'L',
        'SORT'          => 100,
        'VALUES'        => [
            ['VALUE' => 'Активный',   'DEF' => 'Y', 'SORT' => 100, 'XML_ID' => 'active'],
            ['VALUE' => 'Завершён',   'DEF' => 'N', 'SORT' => 200, 'XML_ID' => 'completed'],
        ],
    ],
    [
        'CODE'          => 'REF_DATE',
        'NAME'          => 'Дата (текст)',
        'PROPERTY_TYPE' => 'S',
        'SORT'          => 200,
        'ROW_COUNT'     => 1,
        'COL_COUNT'     => 60,
    ],
    [
        'CODE'          => 'REF_LOCATION',
        'NAME'          => 'Локация',
        'PROPERTY_TYPE' => 'S',
        'SORT'          => 300,
        'ROW_COUNT'     => 1,
        'COL_COUNT'     => 80,
    ],
    [
        'CODE'          => 'REF_DURATION',
        'NAME'          => 'Продолжительность',
        'PROPERTY_TYPE' => 'S',
        'SORT'          => 400,
        'ROW_COUNT'     => 1,
        'COL_COUNT'     => 60,
    ],
    [
        'CODE'          => 'REF_REGISTER_URL',
        'NAME'          => 'Ссылка на регистрацию',
        'PROPERTY_TYPE' => 'S',
        'SORT'          => 500,
        'ROW_COUNT'     => 1,
        'COL_COUNT'     => 80,
    ],
];

foreach ($properties as $propData) {
    $code = $propData['CODE'];
    $db   = CIBlockProperty::GetList([], ['IBLOCK_ID' => $refId, 'CODE' => $code]);
    if ($existing = $db->Fetch()) {
        $ibProp = new CIBlockProperty();
        $upd = $propData;
        unset($upd['VALUES'], $upd['CODE']);
        $upd['IBLOCK_ID'] = $refId;
        $upd['ACTIVE']    = 'Y';
        $ibProp->Update((int)$existing['ID'], $upd);
        echo "• Свойство $code уже существует (обновлено).\n";
    } else {
        $ibProp = new CIBlockProperty();
        $add = $propData;
        $add['IBLOCK_ID'] = $refId;
        $add['ACTIVE']    = 'Y';
        $ibProp->Add($add);
        echo "✓ Свойство $code создано.\n";
    }
}

echo "\nID инфоблока: $refId\n";
echo "Добавьте в init.php:\n";
echo "  define('IBLOCK_REFERENCE_ID', $refId);\n\n";
echo "Готово. Удалите этот файл после выполнения.\n";
