<?php
/**
 * Одноразовый скрипт: создаёт инфоблок «Компетенции» и добавляет константу в init.php
 * Запустить один раз: https://your-domain.ru/setup_competencies.php
 * После выполнения УДАЛИТЕ этот файл с сервера.
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (!$USER->IsAdmin()) {
    die('Access denied');
}

\Bitrix\Main\Loader::includeModule('iblock');

$siteId = SITE_ID ?: 's1';
echo '<pre>';

// ——— 1. Тип инфоблока ———
$ibTypeId = 'po_catalog';
$dbType = CIBlockType::GetList([], ['=ID' => $ibTypeId]);
if (!$dbType->Fetch()) {
    $oIBType = new CIBlockType();
    $res = $oIBType->Add([
        'ID'       => $ibTypeId,
        'LANG'     => ['ru' => ['NAME' => 'ПО Каталоги', 'SECTION_NAME' => 'Раздел', 'ELEMENT_NAME' => 'Элемент']],
        'SECTIONS' => 'N',
    ]);
    if ($res) {
        echo "✓ Тип инфоблока '{$ibTypeId}' создан.\n";
    } else {
        echo "✗ Ошибка создания типа инфоблока.\n";
    }
} else {
    echo "• Тип '{$ibTypeId}' уже существует.\n";
}

// ——— 2. Инфоблок «Компетенции» ———
$existComp = null;
$dbIb = CIBlock::GetList([], ['TYPE' => $ibTypeId, 'CODE' => 'competencies']);
if ($arRow = $dbIb->Fetch()) {
    $existComp = (int)$arRow['ID'];
    echo "• Инфоблок 'Компетенции' уже существует (ID={$existComp}).\n";
}

if (!$existComp) {
    $oIBlock = new CIBlock();
    $ibId = $oIBlock->Add([
        'IBLOCK_TYPE_ID' => $ibTypeId,
        'LID'            => $siteId,
        'CODE'           => 'competencies',
        'NAME'           => 'Компетенции',
        'ACTIVE'         => 'Y',
        'SORT'           => 500,
        'LIST_PAGE_URL'  => '/competencies/',
        'DETAIL_PAGE_URL'=> '/competencies/detail/#ELEMENT_ID#/',
    ]);
    if ($ibId) {
        $existComp = $ibId;
        echo "✓ Инфоблок 'Компетенции' создан (ID={$ibId}).\n";
    } else {
        echo "✗ Ошибка создания инфоблока: " . $oIBlock->LAST_ERROR . "\n";
    }
}

// ——— 3. Свойства инфоблока ———
if ($existComp) {
    $properties = [
        [
            'IBLOCK_ID'    => $existComp,
            'NAME'         => 'Категория',
            'CODE'         => 'CATEGORY',
            'PROPERTY_TYPE'=> 'L',
            'SORT'         => 100,
            'VALUES'       => [
                ['VALUE' => 'university', 'SORT' => 100],
                ['VALUE' => 'skb',        'SORT' => 200],
                ['VALUE' => 'partner',    'SORT' => 300],
            ],
        ],
        [
            'IBLOCK_ID'    => $existComp,
            'NAME'         => 'Теги',
            'CODE'         => 'TAGS',
            'PROPERTY_TYPE'=> 'S',
            'SORT'         => 200,
        ],
        [
            'IBLOCK_ID'    => $existComp,
            'NAME'         => 'Ссылка на PDF',
            'CODE'         => 'PDF_LINK',
            'PROPERTY_TYPE'=> 'S',
            'SORT'         => 300,
        ],
    ];

    $oProp = new CIBlockProperty();
    foreach ($properties as $prop) {
        $dbCheck = CIBlockProperty::GetList([], ['IBLOCK_ID' => $existComp, 'CODE' => $prop['CODE']]);
        if ($dbCheck->Fetch()) {
            echo "• Свойство '{$prop['CODE']}' уже существует.\n";
        } else {
            $vals = $prop['VALUES'] ?? null;
            unset($prop['VALUES']);
            $propId = $oProp->Add($prop);
            if ($propId) {
                echo "✓ Свойство '{$prop['CODE']}' создано (ID={$propId}).\n";
                // Добавляем значения для списка
                if ($vals) {
                    foreach ($vals as $v) {
                        CIBlockPropertyEnum::Add(['PROPERTY_ID' => $propId] + $v);
                    }
                    echo "  ✓ Значения перечисления добавлены.\n";
                }
            } else {
                echo "✗ Ошибка создания свойства '{$prop['CODE']}'.\n";
            }
        }
    }

    // ——— 4. Демо-элемент для проверки ———
    $dbCheck = CIBlockElement::GetList([], ['IBLOCK_ID' => $existComp], false, ['nTopCount' => 1]);
    if (!$dbCheck->Fetch()) {
        $oEl = new CIBlockElement();
        $categoryPropId = null;
        $dbProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $existComp, 'CODE' => 'CATEGORY']);
        if ($arP = $dbProp->Fetch()) {
            $categoryPropId = $arP['ID'];
        }
        $enumId = null;
        if ($categoryPropId) {
            $dbEnum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $categoryPropId, 'VALUE' => 'university']);
            if ($arE = $dbEnum->Fetch()) $enumId = $arE['ID'];
        }

        $elId = $oEl->Add([
            'IBLOCK_ID'    => $existComp,
            'NAME'         => 'НОЦ «Перспективные исследования в ракетно-космической технике» (ПИРТ)',
            'ACTIVE'       => 'Y',
            'SORT'         => 500,
            'PREVIEW_TEXT' => 'Комплексные исследования в области проектирования ракетно-космической техники.',
            'DETAIL_TEXT'  => '<p>Центр занимается комплексными научными исследованиями и разработками в области проектирования перспективных образцов ракетно-космической техники.</p>',
            'PROPERTY_VALUES' => [
                'TAGS'     => '#ракетостроение #космические_аппараты #системный_анализ',
                'CATEGORY' => ($enumId ? $enumId : 'university'),
                'PDF_LINK' => '',
            ],
        ]);
        echo $elId ? "✓ Демо-элемент создан (ID={$elId}).\n" : "✗ Ошибка создания демо-элемента.\n";
    } else {
        echo "• Демо-элемент уже существует.\n";
    }

    // ——— 5. Обновляем init.php ———
    $initFile = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
    $initContent = file_get_contents($initFile);
    $defineKey = "define('IBLOCK_COMPETENCIES_ID'";
    if (strpos($initContent, $defineKey) === false) {
        $insertAfter = "define('HL_APPLICATIONS_ID', 2);";
        $addLine     = "\n/**\n * Инфоблок Компетенции.\n */\ndefine('IBLOCK_COMPETENCIES_ID', {$existComp});";
        if (strpos($initContent, $insertAfter) !== false) {
            $initContent = str_replace($insertAfter, $insertAfter . $addLine, $initContent);
            file_put_contents($initFile, $initContent);
            echo "✓ Константа IBLOCK_COMPETENCIES_ID={$existComp} добавлена в init.php.\n";
        } else {
            echo "⚠ Не удалось найти место вставки в init.php. Добавьте вручную:\n  define('IBLOCK_COMPETENCIES_ID', {$existComp});\n";
        }
    } else {
        echo "• IBLOCK_COMPETENCIES_ID уже определена в init.php.\n";
    }
}

echo "\n<strong>Готово.</strong> Удалите этот файл после выполнения.\n";
echo '</pre>';
