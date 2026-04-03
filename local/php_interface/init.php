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
define('IBLOCK_PROJECTS_ID', 8); // Проекты // Проекты // Проекты
define('IBLOCK_BOARD_ID', 7); // Правление // Правление // Правление

/**
 * HL-блок заявок.
 */
define('HL_APPLICATIONS_ID', 2);

/**
 * HL-блок логов действий пользователей и администраторов.
 * ID присваивается при запуске setup_logs.php
 */
define('HL_LOGS_ID', 5); // после setup_logs.php обновить реальным ID

/**
 * HL-блоки карьерной платформы.
 * ID присваиваются при запуске setup_career.php
 */
define('HL_VACANCIES_ID', 3);
define('HL_RESUMES_ID',   4);

/**
 * Инфоблоки каталогов.
 * ID присваиваются при запуске соответствующих setup-скриптов.
 */
define('IBLOCK_COMPETENCIES_ID', 5);
define('IBLOCK_FUNDS_ID',        6); // после setup_funds.php

/**
 * Email администратора для уведомлений о новых заявках.
 */
define('PO_ADMIN_EMAIL', 'info@bauman-polytech.ru');

/**
 * Пути к уставным документам (публичный URL).
 */
define('DOC_USTAV_URL',    '/local/templates/my_template/assets/' . rawurlencode('УСТАВ ПОЛИТЕХ.pdf'));
define('DOC_POLITIKA_URL', '/local/templates/my_template/assets/' . rawurlencode('Форма_Политика_в_отношении_обработки_персональных_данных_на.docx'));

/**
 * Отправить уведомление администратору о новой заявке.
 *
 * @param string $type  Тип заявки (project_support, event_reg, reference_visit, …)
 * @param array  $data  Данные формы
 */
function po_sendAdminEmail(string $type, array $data): void
{
    $typeLabels = [
        'project_support'    => 'Поддержка проекта (D2)',
        'event_reg'          => 'Запись на событие (D3)',
        'reference_visit'    => 'Участие в референс-визите (D4)',
        'reference_org'      => 'Организация референс-визита (D5)',
        'competency_request' => 'Компетенция/Витрина (D6)',
        'partnership'        => 'Промышленное партнёрство (D7)',
        'vacancy'            => 'Вакансия (карьерная платформа)',
        'resume'             => 'Резюме выпускника (карьерная платформа)',
        'access_recovery'   => 'Экстренное восстановление доступа',
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
 * Добавить раздел «Политех» в левое меню административной панели.
 */
AddEventHandler('main', 'OnBuildGlobalMenu', function (&$globalMenu, &$moduleMenu) {
    if (!defined('HL_APPLICATIONS_ID')) return;

    // Считаем новые заявки для бейджа
    $newCount = 0;
    try {
        if (\Bitrix\Main\Loader::includeModule('highloadblock')) {
            $hlData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
            if ($hlData) {
                $hlClass  = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlData)->getDataClass();
                $newCount = $hlClass::getList(['filter' => ['UF_STATUS' => 'new'], 'count_total' => true])->getCount();
            }
        }
    } catch (\Exception $e) {}

    $badge = $newCount > 0 ? ' <span style="background:#e74c3c;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;font-weight:700;">' . $newCount . '</span>' : '';

    $globalMenu['global_menu_politeh'] = [
        'menu_id'   => 'politeh',
        'text'      => 'Политех',
        'title'     => 'Управление Политехническим обществом',
        'icon'      => 'main_menu_group',
        'page_icon' => 'main_menu_group',
        'sort'      => 50,
        'items_id'  => 'menu_politeh_items',
        'items'     => [
            [
                'text'      => 'Заявки' . $badge,
                'title'     => 'Модерация заявок с форм сайта (D1–D7)',
                'url'       => '/local/admin/po_moderation.php',
                'icon'      => 'main_menu_comment',
                'more_url'  => ['/local/admin/po_moderation.php'],
            ],
            [
                'text'  => 'Только новые',
                'title' => 'Показать только новые заявки',
                'url'   => '/local/admin/po_moderation.php?filter_status=new',
                'icon'  => 'main_menu_search',
            ],
            [
                'text'  => 'На рассмотрении',
                'title' => 'Заявки в работе',
                'url'   => '/local/admin/po_moderation.php?filter_status=in_review',
                'icon'  => 'main_menu_search',
            ],
            [
                'text'  => '— Пользователи сайта',
                'title' => 'Список всех пользователей',
                'url'   => '/bitrix/admin/user_admin.php?lang=ru',
                'icon'  => 'main_menu_user',
            ],
            [
                'text'  => '— Инфоблоки (контент)',
                'title' => 'Новости, события, проекты, правление',
                'url'   => '/bitrix/admin/iblock_type_admin.php?lang=ru',
                'icon'  => 'main_menu_content',
            ],
        ],
    ];
});

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
        'vacancy'            => 'Вакансия (карьерная платформа)',
        'resume'             => 'Резюме выпускника (карьерная платформа)',
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

/**
 * Записать действие в лог (HL-блок Logs).
 * Тихо завершается при любой ошибке — не должна ломать основной поток.
 *
 * @param string $action      Тип действия: login, logout, form_submit, profile_update, admin_status_change
 * @param string $entityType  Тип сущности: application, user, page (необязательно)
 * @param int    $entityId    ID сущности (необязательно)
 * @param string $desc        Произвольное описание (необязательно)
 */
function po_logAction(string $action, string $entityType = '', int $entityId = 0, string $desc = ''): void
{
    try {
        if (!defined('HL_LOGS_ID') || HL_LOGS_ID <= 0) return;
        if (!\Bitrix\Main\Loader::includeModule('highloadblock')) return;

        $hlData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_LOGS_ID)->fetch();
        if (!$hlData) return;

        $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlData)->getDataClass();

        global $USER;
        $userId = ($USER instanceof CUser && $USER->IsAuthorized()) ? (int)$USER->GetID() : 0;
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua     = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $hlClass::add([
            'UF_USER_ID'     => $userId,
            'UF_ACTION'      => $action,
            'UF_ENTITY_TYPE' => $entityType,
            'UF_ENTITY_ID'   => $entityId,
            'UF_IP'          => $ip,
            'UF_USER_AGENT'  => $ua,
            'UF_DESCRIPTION' => substr($desc, 0, 500),
            'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
        ]);
    } catch (\Exception $e) {
        // Молча игнорируем — логирование не должно ломать сайт
    }
}

// Логирование входа пользователя
AddEventHandler('main', 'OnAfterUserLogin', function (&$arFields) {
    $login = $arFields['LOGIN'] ?? '';
    po_logAction('login', 'user', 0, 'Вход: ' . $login);
});

// Логирование выхода пользователя
AddEventHandler('main', 'OnUserLogout', function () {
    global $USER;
    $userId = ($USER instanceof CUser && $USER->IsAuthorized()) ? (int)$USER->GetID() : 0;
    po_logAction('logout', 'user', $userId, 'Выход из системы');
});
