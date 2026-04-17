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
define('IBLOCK_PROJECTS_ID', 8); // Проекты // Проекты // Проекты // Проекты
define('IBLOCK_BOARD_ID', 7); // Правление // Правление // Правление // Правление

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
define('IBLOCK_REFERENCE_ID',   9); 
define('IBLOCK_FUNDS_ID',        6); 

/**
 * Email администратора для уведомлений о новых заявках.
 */
define('PO_ADMIN_EMAIL', 'info@bauman-polytech.ru');

/**
 * Пути к уставным документам (публичный URL).
 */
define('DOC_USTAV_URL',    '/local/templates/my_template/assets/USTAV.pdf');
define('DOC_POLITIKA_URL', '/local/templates/my_template/assets/POLITIKA.docx');

/**
 * Отправить уведомление администратору о новой заявке.
 *
 * @param string $type  Тип заявки (project_support, event_reg, reference_visit, …)
 * @param array  $data  Данные формы
 */
function po_mailEncodeSubject(string $subject): string
{
    return '=?UTF-8?B?' . base64_encode($subject) . '?=';
}

function po_mailLabelForField(string $key): string
{
    static $fieldLabels = [
        'type' => 'Тип',
        'тип_заявки' => 'Тип заявки',
        'тип_членства' => 'Тип членства',
        'company' => 'Компания',
        'contact_name' => 'Контактное лицо',
        'site' => 'Сайт',
        'email' => 'Email',
        'phone' => 'Телефон',
        'msg' => 'Комментарий',
        'fio' => 'ФИО',
        'name' => 'ФИО',
        'first_name' => 'Имя',
        'last_name' => 'Фамилия',
        'second_name' => 'Отчество',
        'full_name' => 'ФИО',
        'membership_type' => 'Тип членства',
        'agree_pd' => 'Согласие с политикой ПДн',
        'agree_charter' => 'Согласие с Уставом',
        'contact' => 'Контакт',
        'position' => 'Должность',
        'dob' => 'Дата рождения',
        'dept' => 'Кафедра',
        'year' => 'Год выпуска',
        'sphere' => 'Сфера деятельности',
        'exp' => 'Стаж (лет)',
        'is_graduate' => 'Выпускник',
        'выпускник_бауманки' => 'Выпускник Бауманки',
        'год_окончания' => 'Год окончания',
        'выпускающая_кафедра' => 'Выпускающая кафедра',
        'telegram' => 'Telegram',
        'вступал_ранее' => 'Ранее состоял в обществе',
        'серия_диплома' => 'Серия диплома',
        'номер_диплома' => 'Номер диплома',
        'дата_выдачи_диплома' => 'Дата выдачи диплома',
        'достижения' => 'Достижения',
        'согласие_с_уставом_и_пдн' => 'Согласие с Уставом и ПДн',
        'id_пользователя' => 'ID пользователя',
        'загруженные_файлы' => 'Загруженные файлы',
    ];

    if (isset($fieldLabels[$key])) {
        return $fieldLabels[$key];
    }

    $label = str_replace('_', ' ', $key);
    return mb_convert_case($label, MB_CASE_TITLE, 'UTF-8');
}

function po_mailNormalizeValue(string $key, $value): string
{
    if ($key === 'тип_членства') {
        $map = [
            'basic' => 'Базовое',
            'premium' => 'Профессиональное',
            'partner' => 'Партнёрское',
            'honorary' => 'Почётное',
            'non_graduate' => 'Невыпускник',
        ];
        return $map[(string)$value] ?? (string)$value;
    }
    if ($key === 'membership_type') {
        $map = [
            'basic' => 'Базовое',
            'premium' => 'Профессиональное',
            'partner' => 'Партнёрское',
            'honorary' => 'Почётное',
        ];
        return $map[(string)$value] ?? (string)$value;
    }
    if ($key === 'agree_pd') {
        return in_array((string)$value, ['1', 'yes', 'true'], true) ? 'Да' : 'Нет';
    }
    if ($key === 'agree_charter') {
        return in_array((string)$value, ['1', 'yes', 'true'], true) ? 'Да' : 'Нет';
    }

    return (string)$value;
}

/**
 * @param array $options ['attachments' => [['path' => '/tmp/php123', 'name' => 'file.pdf']]]
 */
function po_sendAdminEmail(string $type, array $data, array $options = []): void
{
    $typeLabels = [
        'membership'         => 'Вступление в общество (D1)',
        'project_support'    => 'Поддержка проекта (D2)',
        'event_reg'          => 'Запись на событие (D3)',
        'reference_visit'    => 'Участие в референс-визите (D4)',
        'reference_org'      => 'Организация референс-визита (D5)',
        'competency_request' => 'Компетенция/Витрина (D6)',
        'partnership'        => 'Промышленное партнёрство (D7)',
        'vacancy'            => 'Вакансия (карьерная платформа)',
        'resume'             => 'Резюме выпускника (карьерная платформа)',
        'honorary'           => 'Почётное членство',
        'contact'            => 'Связаться с организаторами',
        'access_recovery'    => 'Экстренное восстановление доступа',
    ];
    $label = $typeLabels[$type] ?? $type;

    $fileLinks = [];
    if (isset($data['file_links']) && is_array($data['file_links'])) {
        $fileLinks = $data['file_links'];
    }
    unset($data['file_links']);

    $lines = [
        "Поступила новая заявка с сайта.",
        "",
        "Направление: {$label}",
        "",
        "Данные заявки:",
    ];
    foreach ($data as $k => $v) {
        if ($v === '' || $v === null) {
            continue;
        }
        $lines[] = po_mailLabelForField((string)$k) . ': ' . po_mailNormalizeValue((string)$k, $v);
    }

    if (!empty($fileLinks)) {
        $lines[] = '';
        $lines[] = 'Ссылки на загруженные файлы:';
        foreach ($fileLinks as $name => $url) {
            if (!$url) {
                continue;
            }
            $lines[] = '- ' . po_mailLabelForField((string)$name) . ': ' . $url;
        }
    }

    $body = implode("\n", $lines);

    $to      = PO_ADMIN_EMAIL;
    $subject = "[ПОЛИТЕХ] Новая заявка: {$label}";
    $from    = PO_ADMIN_EMAIL;
    $replyTo = isset($data['email']) && $data['email'] ? (string)$data['email'] : $from;

    $attachments = [];
    if (isset($options['attachments']) && is_array($options['attachments'])) {
        foreach ($options['attachments'] as $file) {
            $path = $file['path'] ?? '';
            if (!$path || !is_file($path)) {
                continue;
            }
            $attachments[] = [
                'path' => $path,
                'name' => (string)($file['name'] ?? basename($path)),
            ];
        }
    }

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "From: {$from}\r\n";
    $headers .= "Reply-To: {$replyTo}\r\n";

    if (empty($attachments)) {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        @mail($to, po_mailEncodeSubject($subject), $body, $headers);
        return;
    }

    $boundary = '==Multipart_Boundary_x' . md5((string)microtime(true)) . 'x';
    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

    $message  = "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= $body . "\r\n";

    foreach ($attachments as $file) {
        $content = @file_get_contents($file['path']);
        if ($content === false) {
            continue;
        }
        $encodedName = rawurlencode($file['name']);
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: application/octet-stream; name=\"{$encodedName}\"\r\n";
        $message .= "Content-Disposition: attachment; filename=\"{$encodedName}\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($content)) . "\r\n";
    }
    $message .= "--{$boundary}--";

    @mail($to, po_mailEncodeSubject($subject), $message, $headers);
}

function po_sendMembershipConfirmationEmail(string $email): bool
{
    $email = trim($email);
    if ($email === '') {
        return false;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('[po_sendMembershipConfirmationEmail] invalid email: ' . $email);
        return false;
    }

    $subject = 'Заявление на вступление Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана';
    $body = "Уважаемый Бауманец!\n\n"
        . "Благодарим вас за подачу заявления на вступление в Политехническое общество выпускников. "
        . "В течении 5 рабочих дней ваша заявка будет обработана, и мы свяжемся с вами.\n\n"
        . "С уважением,\n"
        . "Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "From: " . PO_ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . PO_ADMIN_EMAIL . "\r\n";

    $sent = mail($email, po_mailEncodeSubject($subject), $body, $headers);
    if (!$sent) {
        $lastError = error_get_last();
        error_log('[po_sendMembershipConfirmationEmail] mail() failed for ' . $email . '; error=' . ($lastError['message'] ?? 'unknown'));
    }
    return $sent;
}

function po_is_valid_phone_chars(string $phone): bool
{
    $phone = trim($phone);
    if ($phone === '') {
        return true;
    }
    if (!preg_match('/^[\d\s\+\-]+$/u', $phone)) {
        return false;
    }
    return (bool)preg_match('/\d/u', $phone);
}

function po_flash_set(string $key, array $payload): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    if (!isset($_SESSION['PO_FLASH']) || !is_array($_SESSION['PO_FLASH'])) {
        $_SESSION['PO_FLASH'] = [];
    }
    $_SESSION['PO_FLASH'][$key] = $payload;
}

function po_flash_get(string $key): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    if (!isset($_SESSION['PO_FLASH'][$key]) || !is_array($_SESSION['PO_FLASH'][$key])) {
        return null;
    }
    $payload = $_SESSION['PO_FLASH'][$key];
    unset($_SESSION['PO_FLASH'][$key]);
    return $payload;
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

    $badge = $newCount > 0 ? ' (' . $newCount . ')' : '';

    $globalMenu['global_menu_politeh'] = [
        'menu_id'   => 'politeh',
        'text'      => 'Политех' . $badge,
        'title'     => 'Управление Политехническим обществом',
        'icon'      => 'main_menu_group',
        'page_icon' => 'main_menu_group',
        'sort'      => 50,
        'items_id'  => 'menu_politeh_items',
        'items'     => [
            [
                'text'      => 'Заявки' . ($newCount > 0 ? ' (' . $newCount . ')' : ''),
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
                'url'   => '/bitrix/admin/iblock_admin.php?type=po_catalog&lang=ru',
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
        'contact'            => 'Связаться с организаторами',
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

/**
 * Телефон в заявке «Индустриальное партнёрство»: только цифры, пробел, + и -, минимум одна цифра.
 */
function po_is_valid_partnership_phone(string $phone): bool
{
    $phone = trim($phone);
    if ($phone === '') {
        return false;
    }
    if (!preg_match('/^[\d\s\+\-]+$/u', $phone)) {
        return false;
    }
    return (bool)preg_match('/\d/u', $phone);
}

require_once __DIR__ . '/partnership_form_markup.php';

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
