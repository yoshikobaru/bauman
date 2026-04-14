<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;

global $USER;
header('Content-Type: text/html; charset=utf-8');

if (!$USER || !$USER->IsAdmin()) {
    http_response_code(403);
    echo 'Доступ запрещён. Скрипт доступен только администратору.';
    return;
}

if (!Loader::includeModule('iblock')) {
    echo 'Модуль iblock не подключён.';
    return;
}

if (!defined('IBLOCK_PROJECTS_ID') || IBLOCK_PROJECTS_ID <= 0) {
    echo 'IBLOCK_PROJECTS_ID не задан или <= 0 в local/php_interface/init.php';
    return;
}

$iblockId = (int)IBLOCK_PROJECTS_ID;
$code = 'HOME_IMAGE';

$existing = CIBlockProperty::GetList(
    ['SORT' => 'ASC', 'ID' => 'ASC'],
    ['IBLOCK_ID' => $iblockId, 'CODE' => $code]
)->Fetch();

if ($existing) {
    echo 'Свойство уже существует: ID=' . (int)$existing['ID'] . ', CODE=' . htmlspecialchars($existing['CODE']) . '.';
    return;
}

$property = new CIBlockProperty();
$newId = $property->Add([
    'IBLOCK_ID'          => $iblockId,
    'NAME'               => 'Картинка для главной',
    'ACTIVE'             => 'Y',
    'SORT'               => 180,
    'CODE'               => $code,
    'PROPERTY_TYPE'      => 'F',
    'MULTIPLE'           => 'N',
    'IS_REQUIRED'        => 'N',
    'FILTRABLE'          => 'N',
    'SEARCHABLE'         => 'N',
    'WITH_DESCRIPTION'   => 'N',
    'FILE_TYPE'          => 'jpg, jpeg, png, webp, gif',
    'HINT'               => 'Изображение карточки проекта на главной странице. Если пусто, используется "Картинка для анонса".',
]);

if (!$newId) {
    echo 'Ошибка создания свойства: ' . htmlspecialchars((string)$property->LAST_ERROR);
    return;
}

echo 'Готово. Свойство создано: ID=' . (int)$newId . ', CODE=' . $code . '.';
