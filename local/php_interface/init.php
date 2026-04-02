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

/**
 * Константы инфоблоков.
 * Заполнить реальными ID после запуска /setup_iblocks.php
 */
define('IBLOCK_NEWS_ID',     1); // Новости
define('IBLOCK_EVENTS_ID',   2); // События
define('IBLOCK_PROJECTS_ID', 3); // Проекты
define('IBLOCK_BOARD_ID',    4); // Правление

/**
 * HL-блок заявок.
 */
define('HL_APPLICATIONS_ID', 2);

/**
 * Email администратора для уведомлений о новых заявках.
 */
define('PO_ADMIN_EMAIL', 'admin@bauman-polytech.ru');

/**
 * Отправить уведомление администратору о новой заявке.
 *
 * @param string $type  Тип заявки (project_support, event_reg, reference_visit, …)
 * @param array  $data  Данные формы
 */
function po_sendAdminEmail(string $type, array $data): void
{
    $typeLabels = [
        'project_support'  => 'Поддержка проекта (D2)',
        'event_reg'        => 'Запись на событие (D3)',
        'reference_visit'  => 'Участие в референс-визите (D4)',
        'reference_org'    => 'Организация референс-визита (D5)',
        'competency_request' => 'Компетенция/Витрина (D6)',
        'partnership'      => 'Промышленное партнёрство (D7)',
    ];
    $label = $typeLabels[$type] ?? $type;

    $body = "Новая заявка: {$label}\n\n";
    foreach ($data as $k => $v) {
        if ($v !== '' && $v !== null) {
            $body .= mb_strtoupper($k) . ": {$v}\n";
        }
    }

    $from = 'noreply@bauman-polytech.ru';
    \CMain::Mail([
        'TO'      => PO_ADMIN_EMAIL,
        'FROM'    => $from,
        'SUBJECT' => "[ПОЛИТЕХ] Новая заявка: {$label}",
        'BODY'    => $body,
    ]);
}

/**
 * CRM: создать Лид по данным заявки.
 * Вызывается напрямую из каждого обработчика формы после успешного сохранения в HL-блок.
 *
 * @param string $type  Тип заявки (project_support, event_reg, …)
 * @param array  $data  Данные формы (first_name, last_name, email, phone, …)
 */
function po_createCrmLead(string $type, array $data): void
{
    if (!\Bitrix\Main\Loader::includeModule('crm')) return;

    $typeLabels = [
        'project_support'    => 'Поддержка проекта (D2)',
        'event_reg'          => 'Запись на событие (D3)',
        'reference_visit'    => 'Участие в референс-визите (D4)',
        'reference_org'      => 'Организация референс-визита (D5)',
        'competency_request' => 'Компетенция/Витрина (D6)',
        'partnership'        => 'Промышленное партнёрство (D7)',
    ];
    $title = 'Заявка: ' . ($typeLabels[$type] ?? $type);

    $emailValue = $data['email'] ?? '';
    $emailField = $emailValue ? [['VALUE' => $emailValue, 'VALUE_TYPE' => 'WORK']] : [];

    $phoneValue = $data['phone'] ?? '';
    $phoneField = $phoneValue ? [['VALUE' => $phoneValue, 'VALUE_TYPE' => 'WORK']] : [];

    $comments = '';
    foreach ($data as $k => $v) {
        if ($v !== '' && $v !== null) {
            $comments .= mb_strtoupper($k) . ": {$v}\n";
        }
    }

    \Bitrix\Crm\LeadTable::add([
        'TITLE'     => $title,
        'NAME'      => $data['first_name']  ?? ($data['contact_name'] ?? ''),
        'LAST_NAME' => $data['last_name']   ?? '',
        'EMAIL'     => $emailField,
        'PHONE'     => $phoneField,
        'COMMENTS'  => $comments,
        'SOURCE_ID' => 'WEB',
        'STATUS_ID' => 'NEW',
    ]);
}
