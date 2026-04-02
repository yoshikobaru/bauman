<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Константы групп пользователей.
 * ID заполняются после выполнения /api/setup/install.
 * Значения можно проверить через /api/setup/status.
 */
define('PO_REGISTERED_ID',     0); // Зарегистрированный (без членства)
define('PO_MEMBER_BASIC_ID',   0); // Член общества — Базовое
define('PO_MEMBER_PREMIUM_ID', 0); // Член общества — Привилегированное
define('PO_PARTNER_ID',        0); // Партнёр (юр. лицо)
define('PO_MODERATOR_ID',      0); // Модератор / Сотрудник
