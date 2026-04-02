<?php

/**
 * REST API
 *
 * @install  25.03.2026 13:22:24
 * @package  artamonov.rest
 * @website  https://marketplace.1c-bitrix.ru/solutions/artamonov.rest
 */
if (Bitrix\Main\Loader::includeModule('artamonov.rest')) {
    \Artamonov\Rest\Foundation\Core::getInstance()->run();
}

/**
 * Константы групп пользователей.
 * ID заполняются после выполнения /api/setup/install.
 * Значения можно проверить через /api/setup/status.
 */
define('PO_REGISTERED_ID',     5); // Зарегистрированный (без членства)
define('PO_MEMBER_BASIC_ID',   6); // Член общества — Базовое
define('PO_MEMBER_PREMIUM_ID', 7); // Член общества — Привилегированное
define('PO_PARTNER_ID',        8); // Партнёр (юр. лицо)
define('PO_MODERATOR_ID',      9); // Модератор / Сотрудник
