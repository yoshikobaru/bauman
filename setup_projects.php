<?php
/**
 * One-time setup: create "Проекты" InfoBlock + 4 demo projects.
 * Run once, then DELETE this file.
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('iblock');

$ibType = 'po_catalog';

$existing = CIBlock::GetList([], ['TYPE' => $ibType, 'CODE' => 'po_projects'])->Fetch();

if ($existing) {
    $projId = (int)$existing['ID'];
    echo "• Инфоблок 'Проекты' уже существует (ID=$projId).\n";
} else {
    $ib = new CIBlock();
    $projId = $ib->Add([
        'LID'             => 's1',
        'IBLOCK_TYPE_ID'  => $ibType,
        'CODE'            => 'po_projects',
        'NAME'            => 'Проекты',
        'SORT'            => 300,
        'ACTIVE'          => 'Y',
        'LIST_PAGE_URL'   => '/projects/',
        'DETAIL_PAGE_URL' => '/projects/detail/?id=#ELEMENT_ID#',
        'INDEX_ELEMENT'   => 'N',
        'INDEX_SECTION'   => 'N',
    ]);
    if ($projId) {
        echo "✓ Инфоблок 'Проекты' создан (ID=$projId).\n";
    } else {
        echo "✗ Ошибка создания инфоблока: " . $ib->LAST_ERROR . "\n";
        die();
    }
}

// Свойство PROJECT_STATUS
$dbProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $projId, 'CODE' => 'PROJECT_STATUS']);
if (!$dbProp->Fetch()) {
    $ibProp = new CIBlockProperty();
    $ibProp->Add([
        'IBLOCK_ID'     => $projId,
        'CODE'          => 'PROJECT_STATUS',
        'NAME'          => 'Статус проекта',
        'PROPERTY_TYPE' => 'L',
        'SORT'          => 500,
        'ACTIVE'        => 'Y',
        'VALUES'        => [
            ['VALUE' => 'active',    'DEF' => 'Y', 'SORT' => 100, 'XML_ID' => 'active'],
            ['VALUE' => 'completed', 'DEF' => 'N', 'SORT' => 200, 'XML_ID' => 'completed'],
        ],
    ]);
    echo "✓ Свойство PROJECT_STATUS создано.\n";
} else {
    echo "• Свойство PROJECT_STATUS уже существует.\n";
}

// Свойство DETAIL_URL (кастомная ссылка на статичную страницу)
$dbPropUrl = CIBlockProperty::GetList([], ['IBLOCK_ID' => $projId, 'CODE' => 'DETAIL_URL']);
if (!$dbPropUrl->Fetch()) {
    $ibProp = new CIBlockProperty();
    $ibProp->Add([
        'IBLOCK_ID'     => $projId,
        'CODE'          => 'DETAIL_URL',
        'NAME'          => 'URL детальной страницы',
        'PROPERTY_TYPE' => 'S',
        'SORT'          => 600,
        'ACTIVE'        => 'Y',
    ]);
    echo "✓ Свойство DETAIL_URL создано.\n";
} else {
    echo "• Свойство DETAIL_URL уже существует.\n";
}

// Получить enum ID для 'active'
$statusEnumId = null;
$dbPs = CIBlockProperty::GetList([], ['IBLOCK_ID' => $projId, 'CODE' => 'PROJECT_STATUS']);
if ($prop = $dbPs->Fetch()) {
    $dbVals = CIBlockPropertyEnum::GetList(['SORT' => 'ASC'], ['PROPERTY_ID' => $prop['ID']]);
    while ($val = $dbVals->Fetch()) {
        if ($val['XML_ID'] === 'active') $statusEnumId = $val['ID'];
    }
}

// Добавить 4 demo проекта
$dbCheck = CIBlockElement::GetList([], ['IBLOCK_ID' => $projId], false, ['nTopCount' => 1], ['ID']);
if (!$dbCheck->Fetch()) {
    $projects = [
        ['name' => 'PolytechExpo',            'preview' => 'Ежегодная конференция выпускников и партнёров МГТУ им. Н.Э. Баумана', 'url' => '/projects/politech-expo/'],
        ['name' => 'Встреча выпускников',     'preview' => 'Традиционная встреча выпускников всех поколений Бауманки',           'url' => '/projects/conference/'],
        ['name' => 'Попечительский совет МТ4','preview' => 'Поддержка развития кафедры МТ4 МГТУ им. Н.Э. Баумана',             'url' => '/projects/trustees/'],
        ['name' => 'Реставрация ротонды',     'preview' => 'Проект по восстановлению исторической ротонды МГТУ',                 'url' => '/projects/restoration/'],
    ];
    $el = new CIBlockElement();
    foreach ($projects as $i => $proj) {
        $props = ['DETAIL_URL' => $proj['url']];
        if ($statusEnumId) $props['PROJECT_STATUS'] = $statusEnumId;
        $res = $el->Add([
            'IBLOCK_ID'       => $projId,
            'NAME'            => $proj['name'],
            'PREVIEW_TEXT'    => $proj['preview'],
            'ACTIVE'          => 'Y',
            'SORT'            => ($i + 1) * 100,
            'PROPERTY_VALUES' => $props,
        ]);
        echo $res ? "✓ Проект '{$proj['name']}' создан (ID=$res).\n" : "✗ Ошибка '{$proj['name']}': " . $el->LAST_ERROR . "\n";
    }
} else {
    echo "• Проекты уже есть в инфоблоке.\n";
}

// Обновить IBLOCK_PROJECTS_ID в init.php
$initFile    = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
$initContent = file_get_contents($initFile);
$newContent  = preg_replace(
    "/define\('IBLOCK_PROJECTS_ID',\s*\d+\);/",
    "define('IBLOCK_PROJECTS_ID', {$projId}); // Проекты",
    $initContent
);
if ($newContent !== $initContent) {
    file_put_contents($initFile, $newContent);
    echo "✓ IBLOCK_PROJECTS_ID={$projId} обновлена в init.php.\n";
} else {
    echo "• IBLOCK_PROJECTS_ID уже актуальна.\n";
}

echo "\nГотово. Удалите этот файл после выполнения.\n";
