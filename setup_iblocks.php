<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

header('Content-Type: text/plain; charset=UTF-8');

if (!Loader::includeModule('iblock')) {
    echo "Ошибка: модуль iblock не подключен.\n";
    exit;
}

$projectsIblockId = defined('IBLOCK_PROJECTS_ID') ? (int)IBLOCK_PROJECTS_ID : 0;
if ($projectsIblockId <= 0) {
    echo "Ошибка: не задан IBLOCK_PROJECTS_ID.\n";
    exit;
}

function poEnsureProjectFileProperty(int $iblockId, string $code, string $name): void
{
    $propertyRes = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code]);
    if ($propertyRes && $propertyRes->Fetch()) {
        echo "Свойство {$code} уже существует.\n";
        return;
    }

    $property = new CIBlockProperty();
    $propertyId = $property->Add([
        'NAME'         => $name,
        'ACTIVE'       => 'Y',
        'SORT'         => 520,
        'CODE'         => $code,
        'PROPERTY_TYPE'=> 'F',
        'IBLOCK_ID'    => $iblockId,
        'IS_REQUIRED'  => 'N',
        'FILTRABLE'    => 'N',
        'MULTIPLE'     => 'N',
    ]);

    if (!$propertyId) {
        echo "Ошибка создания {$code}: " . $property->LAST_ERROR . "\n";
        return;
    }

    echo "Создано свойство {$code} (ID: {$propertyId}).\n";
}

poEnsureProjectFileProperty($projectsIblockId, 'HOME_IMAGE_MOB', 'Картинка для главной (моб)');

echo "Готово.\n";
