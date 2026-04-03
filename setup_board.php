<?php
/**
 * One-time setup: create "Правление" InfoBlock + demo members.
 * Run once, then DELETE this file.
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('iblock');

$ibType = 'po_catalog';

$existing = CIBlock::GetList([], ['TYPE' => $ibType, 'CODE' => 'po_board'])->Fetch();

if ($existing) {
    $boardId = (int)$existing['ID'];
    echo "• Инфоблок 'Правление' уже существует (ID=$boardId).\n";
} else {
    $ib = new CIBlock();
    $boardId = $ib->Add([
        'LID'             => 's1',
        'IBLOCK_TYPE_ID'  => $ibType,
        'CODE'            => 'po_board',
        'NAME'            => 'Правление',
        'SORT'            => 400,
        'ACTIVE'          => 'Y',
        'LIST_PAGE_URL'   => '',
        'DETAIL_PAGE_URL' => '',
        'INDEX_ELEMENT'   => 'N',
        'INDEX_SECTION'   => 'N',
    ]);
    if ($boardId) {
        echo "✓ Инфоблок 'Правление' создан (ID=$boardId).\n";
    } else {
        echo "✗ Ошибка создания инфоблока: " . $ib->LAST_ERROR . "\n";
        die();
    }
}

// Добавить demo-членов если пусто
$dbCheck = CIBlockElement::GetList([], ['IBLOCK_ID' => $boardId], false, ['nTopCount' => 1], ['ID']);
if (!$dbCheck->Fetch()) {
    $members = [
        ['name' => 'Иван Петров',   'pos' => 'Президент общества'],
        ['name' => 'Анна Сидорова', 'pos' => 'Вице-президент'],
        ['name' => 'Михаил Козлов', 'pos' => 'Директор'],
        ['name' => 'Елена Новикова','pos' => 'Учёный секретарь'],
        ['name' => 'Сергей Волков', 'pos' => 'Председатель ревизионной комиссии'],
    ];
    $el = new CIBlockElement();
    foreach ($members as $i => $m) {
        $res = $el->Add([
            'IBLOCK_ID'    => $boardId,
            'NAME'         => $m['name'],
            'PREVIEW_TEXT' => $m['pos'],
            'ACTIVE'       => 'Y',
            'SORT'         => ($i + 1) * 100,
        ]);
        echo $res ? "✓ '{$m['name']}' создан (ID=$res).\n" : "✗ Ошибка '{$m['name']}': " . $el->LAST_ERROR . "\n";
    }
} else {
    echo "• Члены правления уже есть в инфоблоке.\n";
}

// Обновить IBLOCK_BOARD_ID в init.php
$initFile    = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
$initContent = file_get_contents($initFile);
$newContent  = preg_replace(
    "/define\('IBLOCK_BOARD_ID',\s*\d+\);/",
    "define('IBLOCK_BOARD_ID', {$boardId}); // Правление",
    $initContent
);
if ($newContent !== $initContent) {
    file_put_contents($initFile, $newContent);
    echo "✓ IBLOCK_BOARD_ID={$boardId} обновлена в init.php.\n";
} else {
    echo "• IBLOCK_BOARD_ID уже актуальна.\n";
}

echo "\nГотово. Удалите этот файл после выполнения.\n";
