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

// Удалить все старые элементы (демо-данные) перед добавлением реальных
$dbOld = CIBlockElement::GetList([], ['IBLOCK_ID' => $boardId], false, false, ['ID']);
$deleted = 0;
while ($row = $dbOld->Fetch()) {
    CIBlockElement::Delete($row['ID']);
    $deleted++;
}
if ($deleted > 0) {
    echo "• Удалено старых элементов: {$deleted}.\n";
}

// Добавить реальных членов правления
if (true) {
    $members = [
        ['name' => 'Абакумов Евгений',  'pos' => 'Директор по информационным технологиям госкорпорации «Росатом»'],
        ['name' => 'Нагайцев Максим',   'pos' => 'Доктор технических наук'],
        ['name' => 'Гордин Михаил',     'pos' => 'Ректор МГТУ им. Н.Э. Баумана, кандидат технических наук'],
        ['name' => 'Кондратьев Андрей', 'pos' => 'Генеральный директор АО «РТ-ФИНАНС», председатель совета директоров НОВИКОМа'],
        ['name' => 'Майоров Игорь',     'pos' => 'Генеральный директор METEOR Lift'],
        ['name' => 'Фетисов Алексей',   'pos' => 'Генеральный директор Холдинга Т1'],
        ['name' => 'Федоров Алексей',   'pos' => 'Вице-Президент «Газпромбанк»'],
        ['name' => 'Шелобков Алексей',  'pos' => 'Генеральный директор ООО «Бюро 1440»'],
        ['name' => 'Дабагов Анатолий',  'pos' => 'Кандидат технических наук, президент МТЛ'],
        ['name' => 'Краснов Дмитрий',   'pos' => 'Кандидат технических наук, председатель Правления Промышленной Группы «Приводная Техника»'],
        ['name' => 'Пивень Валерий',    'pos' => 'Директор департамента станкостроения и тяжелого машиностроения'],
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
}

echo "• IBLOCK_BOARD_ID={$boardId} (init.php не трогаем — ID уже актуален).\n";

echo "\nГотово. Удалите этот файл после выполнения.\n";
