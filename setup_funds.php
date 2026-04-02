<?php
/**
 * Одноразовый скрипт: создаёт инфоблок «Фонды».
 * Запустить один раз: https://your-domain.ru/setup_funds.php
 * После выполнения УДАЛИТЕ этот файл с сервера.
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (!$USER->IsAdmin()) die('Access denied');

\Bitrix\Main\Loader::includeModule('iblock');
$siteId = SITE_ID ?: 's1';
echo '<pre>';

// 1. Тип po_catalog должен уже существовать (создан setup_competencies)
$ibTypeId = 'po_catalog';

// 2. Инфоблок Фонды
$existFunds = null;
$dbIb = CIBlock::GetList([], ['TYPE' => $ibTypeId, 'CODE' => 'funds']);
if ($arRow = $dbIb->Fetch()) {
    $existFunds = (int)$arRow['ID'];
    echo "• Инфоблок 'Фонды' уже существует (ID={$existFunds}).\n";
}

if (!$existFunds) {
    $oIBlock = new CIBlock();
    $ibId = $oIBlock->Add([
        'IBLOCK_TYPE_ID' => $ibTypeId,
        'LID'            => $siteId,
        'CODE'           => 'funds',
        'NAME'           => 'Фонды',
        'ACTIVE'         => 'Y',
        'SORT'           => 600,
        'LIST_PAGE_URL'  => '/support/',
        'DETAIL_PAGE_URL'=> '/support/#fund-#ELEMENT_ID#',
    ]);
    if ($ibId) {
        $existFunds = $ibId;
        echo "✓ Инфоблок 'Фонды' создан (ID={$ibId}).\n";
    } else {
        echo "✗ Ошибка: " . $oIBlock->LAST_ERROR . "\n";
    }
}

// 3. Демо-элементы
if ($existFunds) {
    $dbCheck = CIBlockElement::GetList([], ['IBLOCK_ID' => $existFunds], false, ['nTopCount' => 1]);
    if (!$dbCheck->Fetch()) {
        $oEl = new CIBlockElement();
        $demoFunds = [
            [
                'NAME'         => 'Фонд развития науки МГТУ',
                'PREVIEW_TEXT' => 'Поддержка фундаментальных и прикладных исследований, финансирование лабораторного оборудования и стипендий для студентов.',
            ],
            [
                'NAME'         => 'Фонд помощи выпускникам',
                'PREVIEW_TEXT' => 'Помощь выпускникам, оказавшимся в трудной жизненной ситуации: адресная поддержка, профессиональная переориентация.',
            ],
        ];
        foreach ($demoFunds as $fund) {
            $elId = $oEl->Add(array_merge($fund, [
                'IBLOCK_ID' => $existFunds,
                'ACTIVE'    => 'Y',
                'SORT'      => 500,
            ]));
            echo $elId ? "✓ Фонд '{$fund['NAME']}' создан (ID={$elId}).\n" : "✗ Ошибка создания фонда.\n";
        }
    } else {
        echo "• Демо-фонды уже существуют.\n";
    }

    // 4. Прописываем константу в init.php
    $initFile    = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
    $initContent = file_get_contents($initFile);
    $defineKey   = "define('IBLOCK_FUNDS_ID'";
    if (strpos($initContent, $defineKey) === false) {
        $anchor  = "define('IBLOCK_COMPETENCIES_ID'";
        $addLine = "\ndefine('IBLOCK_FUNDS_ID', {$existFunds});";
        if (strpos($initContent, $anchor) !== false) {
            $initContent = str_replace($anchor, $anchor . PHP_EOL . "define('IBLOCK_FUNDS_ID', {$existFunds});", $initContent);
            // Avoid double insertion
            $initContent = str_replace(
                "define('IBLOCK_COMPETENCIES_ID'" . PHP_EOL . "define('IBLOCK_FUNDS_ID', {$existFunds});" . PHP_EOL . "define('IBLOCK_FUNDS_ID', {$existFunds});",
                "define('IBLOCK_COMPETENCIES_ID'" . PHP_EOL . "define('IBLOCK_FUNDS_ID', {$existFunds});",
                $initContent
            );
        } else {
            // Fallback: append before closing
            $initContent .= "\ndefine('IBLOCK_FUNDS_ID', {$existFunds});\n";
        }
        file_put_contents($initFile, $initContent);
        echo "✓ IBLOCK_FUNDS_ID={$existFunds} добавлена в init.php.\n";
    } else {
        echo "• IBLOCK_FUNDS_ID уже определена в init.php.\n";
    }
}

echo "\n<strong>Готово.</strong> Удалите этот файл после выполнения.\n";
echo '</pre>';
