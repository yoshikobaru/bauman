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
 * Рендереры секций для разделения PHP и HTML.
 * Подключаем все рендереры из папки renderers/
 * Это позволяет контент-менеджерам редактировать HTML в визуальном редакторе
 * без риска сломать PHP-логику.
 */
$renderersDir = __DIR__ . '/renderers/';
if (is_dir($renderersDir)) {
    $renderers = [
        'board_section.php',   // Члены Совета
        'projects_section.php', // Проекты общества
        'news_section.php',    // Новости и события
        'board_modals.php',    // Модальные окна членов Совета
    ];
    foreach ($renderers as $renderer) {
        $rendererPath = $renderersDir . $renderer;
        if (file_exists($rendererPath)) {
            require_once $rendererPath;
        }
    }
}

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
    ];
    if ($type === 'project_support') {
        po_project_support_append_payment_notice_lines($lines, $data);
    }
    $lines[] = '';
    $lines[] = 'Данные заявки:';

    foreach ($data as $k => $v) {
        if ($v === '' || $v === null) {
            continue;
        }
        if (is_array($v)) {
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

    $to = PO_ADMIN_EMAIL;
    if ($type === 'project_support') {
        $projectTitle = trim((string)($data['project'] ?? ''));
        $suffix       = po_project_support_payment_email_suffix($data);
        $base         = $projectTitle !== ''
            ? "[ПОЛИТЕХ] Новая заявка: Поддержка проект ({$projectTitle})"
            : "[ПОЛИТЕХ] Новая заявка: {$label}";
        $subject      = $base . ' — ' . $suffix;
    } else {
        $subject = "[ПОЛИТЕХ] Новая заявка: {$label}";
    }
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
                'text'  => 'Настройки PayKeeper',
                'title' => 'Маршрутизация проектов и счета PayKeeper',
                'url'   => '/local/tools/po_paykeeper_settings.php',
                'icon'  => 'main_menu_settings',
                'more_url'  => ['/local/tools/po_paykeeper_settings.php'],
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

/**
 * Получить конфиг PayKeeper из Bitrix Configuration.
 * Ожидаемая структура в .settings_extra.php:
 * [
 *   'paykeeper' => [
 *     'value' => [
 *       'base_url' => 'https://xxxxx.server.paykeeper.ru',
 *       'username' => '...',
 *       'password' => '...',
 *       'secret_word' => '...',
 *       'success_url' => 'https://site/support/?pay=success',
 *       'fail_url' => 'https://site/support/?pay=fail',
 *       'callback_url' => 'https://site/local/tools/paykeeper_callback.php',
 *     ],
 *     'readonly' => true,
 *   ],
 * ]
 */
function po_get_paykeeper_config(): array
{
    $rawConfig = [];
    try {
        $rawConfig = \Bitrix\Main\Config\Configuration::getValue('paykeeper');
    } catch (\Throwable $e) {
        $rawConfig = [];
    }

    if (isset($rawConfig['value']) && is_array($rawConfig['value'])) {
        $rawConfig = $rawConfig['value'];
    }
    if (!is_array($rawConfig)) {
        $rawConfig = [];
    }

    // Админские оверрайды (редактируются в /local/admin/po_paykeeper_settings.php)
    $adminPaykeeperRaw = '';
    try {
        $adminPaykeeperRaw = (string)\COption::GetOptionString('main', 'po_paykeeper_config_json', '');
    } catch (\Throwable $e) {
        $adminPaykeeperRaw = '';
    }
    if ($adminPaykeeperRaw !== '') {
        $adminPaykeeperConfig = json_decode($adminPaykeeperRaw, true);
        if (is_array($adminPaykeeperConfig)) {
            foreach (['default_account', 'project_accounts', 'accounts', 'project_aliases'] as $key) {
                if (array_key_exists($key, $adminPaykeeperConfig)) {
                    $rawConfig[$key] = $adminPaykeeperConfig[$key];
                }
            }
        }
    }

    $baseUrl = trim((string)($rawConfig['base_url'] ?? ''));
    if ($baseUrl !== '') {
        $baseUrl = rtrim($baseUrl, '/');
    }

    return [
        'base_url' => $baseUrl,
        'username' => trim((string)($rawConfig['username'] ?? '')),
        'password' => (string)($rawConfig['password'] ?? ''),
        'secret_word' => (string)($rawConfig['secret_word'] ?? ''),
        'success_url' => trim((string)($rawConfig['success_url'] ?? '')),
        'fail_url' => trim((string)($rawConfig['fail_url'] ?? '')),
        'callback_url' => trim((string)($rawConfig['callback_url'] ?? '')),
        'default_account' => trim((string)($rawConfig['default_account'] ?? '')),
        'project_accounts' => isset($rawConfig['project_accounts']) && is_array($rawConfig['project_accounts'])
            ? $rawConfig['project_accounts']
            : [],
        'accounts' => isset($rawConfig['accounts']) && is_array($rawConfig['accounts'])
            ? $rawConfig['accounts']
            : [],
        'project_aliases' => isset($rawConfig['project_aliases']) && is_array($rawConfig['project_aliases'])
            ? $rawConfig['project_aliases']
            : [],
    ];
}

function po_paykeeper_normalize_project_name(string $name): string
{
    $name = trim(mb_strtolower($name, 'UTF-8'));
    $name = preg_replace('/\s+/u', ' ', $name);
    if (!is_string($name)) {
        return '';
    }
    $name = preg_replace('/^на\s+/u', '', $name);
    $name = preg_replace('/^для\s+/u', '', $name);
    $name = preg_replace('/^поддержка\s+/u', 'поддержку ', $name);
    return trim((string)$name);
}

/**
 * Список аккаунтов PayKeeper (single + multi-account режимы).
 * На выходе каждый аккаунт содержит base_url, username, password, secret_word и account_key.
 */
function po_paykeeper_get_accounts(array $config): array
{
    $accounts = [];
    $shared = [
        'username' => (string)($config['username'] ?? ''),
        'password' => (string)($config['password'] ?? ''),
        'success_url' => (string)($config['success_url'] ?? ''),
        'fail_url' => (string)($config['fail_url'] ?? ''),
        'callback_url' => (string)($config['callback_url'] ?? ''),
    ];

    if (!empty($config['accounts']) && is_array($config['accounts'])) {
        foreach ($config['accounts'] as $key => $accountRaw) {
            if (!is_array($accountRaw)) {
                continue;
            }
            $baseUrl = trim((string)($accountRaw['base_url'] ?? ''));
            $secretWord = (string)($accountRaw['secret_word'] ?? '');
            $username = trim((string)($accountRaw['username'] ?? $shared['username']));
            $password = (string)($accountRaw['password'] ?? $shared['password']);
            if ($baseUrl === '' || $secretWord === '' || $username === '' || $password === '') {
                continue;
            }
            $accounts[(string)$key] = [
                'account_key' => (string)$key,
                'base_url' => rtrim($baseUrl, '/'),
                'secret_word' => $secretWord,
                'username' => $username,
                'password' => $password,
                'success_url' => trim((string)($accountRaw['success_url'] ?? $shared['success_url'])),
                'fail_url' => trim((string)($accountRaw['fail_url'] ?? $shared['fail_url'])),
                'callback_url' => trim((string)($accountRaw['callback_url'] ?? $shared['callback_url'])),
                'projects' => isset($accountRaw['projects']) && is_array($accountRaw['projects']) ? $accountRaw['projects'] : [],
                'project_patterns' => isset($accountRaw['project_patterns']) && is_array($accountRaw['project_patterns']) ? $accountRaw['project_patterns'] : [],
            ];
        }
    }

    // Обратная совместимость с single-account конфигом.
    if (empty($accounts)) {
        $baseUrl = trim((string)($config['base_url'] ?? ''));
        $secretWord = (string)($config['secret_word'] ?? '');
        $username = trim((string)($config['username'] ?? ''));
        $password = (string)($config['password'] ?? '');
        if ($baseUrl !== '' && $secretWord !== '' && $username !== '' && $password !== '') {
            $accounts['default'] = [
                'account_key' => 'default',
                'base_url' => rtrim($baseUrl, '/'),
                'secret_word' => $secretWord,
                'username' => $username,
                'password' => $password,
                'success_url' => (string)($config['success_url'] ?? ''),
                'fail_url' => (string)($config['fail_url'] ?? ''),
                'callback_url' => (string)($config['callback_url'] ?? ''),
                'projects' => [],
                'project_patterns' => [],
            ];
        }
    }

    return $accounts;
}

function po_is_paykeeper_configured(?array $config = null): bool
{
    $cfg = $config ?? po_get_paykeeper_config();
    return !empty(po_paykeeper_get_accounts($cfg));
}

/**
 * Выбрать аккаунт PayKeeper по проекту.
 * Приоритет: project_accounts (exact) -> account.projects (exact) -> account.project_patterns (contains) -> default_account -> первый.
 */
function po_paykeeper_get_account_for_project(string $project, array $config, string &$error = ''): ?array
{
    $accounts = po_paykeeper_get_accounts($config);
    if (empty($accounts)) {
        $error = 'PayKeeper не настроен.';
        return null;
    }

    $project = trim($project);
    if ($project === '') {
        $project = 'Пожертвование на ведение уставной деятельности';
    }
    $normalizedProject = po_paykeeper_normalize_project_name($project);

    $projectMap = isset($config['project_accounts']) && is_array($config['project_accounts'])
        ? $config['project_accounts']
        : [];
    $projectAliases = isset($config['project_aliases']) && is_array($config['project_aliases'])
        ? $config['project_aliases']
        : [];
    if (isset($projectAliases[$project])) {
        $aliasTarget = (string)$projectAliases[$project];
        if ($aliasTarget !== '') {
            $project = $aliasTarget;
            $normalizedProject = po_paykeeper_normalize_project_name($project);
        }
    }

    if (isset($projectMap[$project])) {
        $mappedKey = (string)$projectMap[$project];
        if (isset($accounts[$mappedKey])) {
            return $accounts[$mappedKey];
        }
    }
    foreach ($projectMap as $projectName => $accountKey) {
        if (po_paykeeper_normalize_project_name((string)$projectName) === $normalizedProject) {
            $mappedKey = (string)$accountKey;
            if (isset($accounts[$mappedKey])) {
                return $accounts[$mappedKey];
            }
        }
    }

    $projectLower = mb_strtolower($project, 'UTF-8');
    foreach ($accounts as $account) {
        $projects = isset($account['projects']) && is_array($account['projects']) ? $account['projects'] : [];
        foreach ($projects as $projectName) {
            $projectNameRaw = trim((string)$projectName);
            if (mb_strtolower($projectNameRaw, 'UTF-8') === $projectLower
                || po_paykeeper_normalize_project_name($projectNameRaw) === $normalizedProject) {
                return $account;
            }
        }
    }
    foreach ($accounts as $account) {
        $patterns = isset($account['project_patterns']) && is_array($account['project_patterns']) ? $account['project_patterns'] : [];
        foreach ($patterns as $pattern) {
            $needle = mb_strtolower(trim((string)$pattern), 'UTF-8');
            if ($needle !== '' && mb_stripos($projectLower, $needle, 0, 'UTF-8') !== false) {
                return $account;
            }
        }
    }

    $defaultKey = trim((string)($config['default_account'] ?? ''));
    if ($defaultKey !== '' && isset($accounts[$defaultKey])) {
        return $accounts[$defaultKey];
    }

    return reset($accounts) ?: null;
}

/**
 * Определить аккаунт PayKeeper по подписи callback.
 */
function po_paykeeper_get_account_for_callback_payload(array $payload, array $config): ?array
{
    $accounts = po_paykeeper_get_accounts($config);
    foreach ($accounts as $account) {
        $secretWord = (string)($account['secret_word'] ?? '');
        if ($secretWord === '') {
            continue;
        }
        if (po_paykeeper_validate_callback_signature($payload, $secretWord)) {
            return $account;
        }
    }
    return null;
}

/**
 * Преобразовать сумму вида "10 000 руб." в строку "10000.00".
 */
function po_paykeeper_normalize_amount(string $rawAmount): ?string
{
    $rawAmount = trim($rawAmount);
    if ($rawAmount === '') {
        return null;
    }

    $compact = preg_replace('/[\s\x{00A0}]+/u', '', $rawAmount);
    if (!is_string($compact) || $compact === '') {
        return null;
    }

    if (!preg_match('/\d+(?:[.,]\d+)?/u', $compact, $matches)) {
        return null;
    }

    $numeric = str_replace(',', '.', $matches[0]);
    $amount = (float)$numeric;
    if ($amount <= 0) {
        return null;
    }

    return number_format($amount, 2, '.', '');
}

function po_paykeeper_build_support_order_id(int $applicationId): string
{
    return 'SUPPORT-' . $applicationId . '-' . date('YmdHis');
}

function po_paykeeper_extract_application_id(string $orderId): int
{
    if (preg_match('/^SUPPORT-(\d+)-\d{14}$/', $orderId, $matches)) {
        return (int)$matches[1];
    }
    return 0;
}

/**
 * Добавить GET-параметр к URL возврата PayKeeper (success_url / fail_url).
 */
function po_paykeeper_append_query_param(string $url, string $name, string $value): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    $sep = strpos($url, '?') !== false ? '&' : '?';

    return $url . $sep . rawurlencode($name) . '=' . rawurlencode($value);
}

/**
 * Текст для темы письма и тела: состояние оплаты по заявке поддержки проекта.
 */
function po_project_support_payment_email_suffix(array $data): string
{
    $payment = isset($data['payment']) && is_array($data['payment']) ? $data['payment'] : [];
    $status  = (string)($payment['status'] ?? '');

    switch ($status) {
        case 'paid':
            return 'Оплачено';
        case 'cancelled':
        case 'failed':
            return 'Оплата не завершена';
        case 'pending':
        case 'new':
        default:
            return 'Ожидает оплаты';
    }
}

function po_project_support_append_payment_notice_lines(array &$lines, array $data): void
{
    $payment = isset($data['payment']) && is_array($data['payment']) ? $data['payment'] : [];
    $lines[] = '';
    $lines[] = 'Статус оплаты: ' . po_project_support_payment_email_suffix($data);
    if (($payment['status'] ?? '') === 'paid') {
        if (!empty($payment['paid_at'])) {
            $lines[] = 'Дата оплаты (PayKeeper): ' . (string)$payment['paid_at'];
        }
        if (!empty($payment['payment_id'])) {
            $lines[] = 'ID платежа PayKeeper: ' . (string)$payment['payment_id'];
        }
        if (!empty($payment['amount_rub'])) {
            $lines[] = 'Сумма оплаты: ' . (string)$payment['amount_rub'] . ' руб.';
        }
    }
    if (($payment['status'] ?? '') === 'cancelled' && !empty($payment['cancelled_at'])) {
        $lines[] = 'Пользователь вернулся с оплаты без завершения: ' . (string)$payment['cancelled_at'];
    }
}

/**
 * Возврат с fail_url: пометить заявку и один раз уведомить админа по почте.
 */
function po_project_support_process_fail_return(string $orderId): void
{
    $applicationId = po_paykeeper_extract_application_id($orderId);
    if ($applicationId <= 0) {
        return;
    }
    if (!\Bitrix\Main\Loader::includeModule('highloadblock') || !defined('HL_APPLICATIONS_ID') || HL_APPLICATIONS_ID <= 0) {
        return;
    }

    $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
    if (!$hlEntity) {
        return;
    }
    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
    $application = $hlClass::getById($applicationId)->fetch();
    if (!$application || ($application['UF_TYPE'] ?? '') !== 'project_support') {
        return;
    }

    $data = [];
    if (!empty($application['UF_DATA'])) {
        $decoded = json_decode((string)$application['UF_DATA'], true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }

    $paymentData = isset($data['payment']) && is_array($data['payment']) ? $data['payment'] : [];
    if (($paymentData['status'] ?? '') === 'paid') {
        return;
    }
    if (!empty($paymentData['fail_return_email_sent'])) {
        return;
    }
    if (($paymentData['status'] ?? '') !== 'pending') {
        return;
    }

    $paymentData['status']                  = 'cancelled';
    $paymentData['cancelled_at']            = date('c');
    $paymentData['cancel_reason']           = 'fail_url';
    $paymentData['fail_return_email_sent']   = true;
    $data['payment']                        = $paymentData;

    $upd = $hlClass::update($applicationId, [
        'UF_DATA' => json_encode($data, JSON_UNESCAPED_UNICODE),
    ]);
    if (!$upd->isSuccess()) {
        return;
    }

    if (function_exists('po_sendAdminEmail')) {
        po_sendAdminEmail('project_support', $data);
    }
}

/**
 * Выполнить запрос к JSON API PayKeeper.
 *
 * @return array ['ok' => bool, 'data' => array, 'error' => string]
 */
function po_paykeeper_api_call(array $config, string $uri, string $method = 'GET', array $params = []): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'data' => [], 'error' => 'cURL не доступен на сервере.'];
    }

    $baseUrl = rtrim((string)($config['base_url'] ?? ''), '/');
    if ($baseUrl === '') {
        return ['ok' => false, 'data' => [], 'error' => 'Не задан base_url PayKeeper.'];
    }

    $url = $baseUrl . $uri;
    $method = strtoupper($method);
    if ($method === 'GET' && !empty($params)) {
        $query = http_build_query($params);
        if ($query !== '') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
        }
    }

    $curl = curl_init();
    if ($curl === false) {
        return ['ok' => false, 'data' => [], 'error' => 'Не удалось инициализировать cURL.'];
    }

    $headers = [
        'Authorization: Basic ' . base64_encode((string)$config['username'] . ':' . (string)$config['password']),
        'Accept: application/json',
    ];
    if ($method === 'POST') {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];
    if ($method === 'POST') {
        $options[CURLOPT_POSTFIELDS] = http_build_query($params);
    }

    curl_setopt_array($curl, $options);
    $rawResponse = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($rawResponse === false) {
        return ['ok' => false, 'data' => [], 'error' => 'Ошибка cURL: ' . $curlError];
    }

    $decoded = json_decode($rawResponse, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'data' => [],
            'error' => 'Некорректный JSON-ответ PayKeeper (HTTP ' . $httpCode . ').',
        ];
    }

    if (isset($decoded['result']) && $decoded['result'] === 'fail') {
        $msg = (string)($decoded['msg'] ?? 'Неизвестная ошибка PayKeeper');
        return ['ok' => false, 'data' => $decoded, 'error' => $msg];
    }

    return ['ok' => true, 'data' => $decoded, 'error' => ''];
}

function po_paykeeper_request_token(array $config, string &$error = ''): ?string
{
    $result = po_paykeeper_api_call($config, '/info/settings/token/', 'GET');
    if (!$result['ok']) {
        $error = (string)$result['error'];
        return null;
    }

    $token = (string)($result['data']['token'] ?? '');
    if ($token === '') {
        $error = 'PayKeeper не вернул token.';
        return null;
    }

    return $token;
}

/**
 * @return array|null ['invoice_id' => string, 'invoice_url' => string]
 */
function po_paykeeper_create_invoice(array $config, array $paymentData, string &$error = ''): ?array
{
    $token = po_paykeeper_request_token($config, $error);
    if ($token === null) {
        return null;
    }

    $request = array_merge($paymentData, ['token' => $token]);
    $result = po_paykeeper_api_call($config, '/change/invoice/preview/', 'POST', $request);
    if (!$result['ok']) {
        $error = (string)$result['error'];
        return null;
    }

    $invoiceId = (string)($result['data']['invoice_id'] ?? '');
    if ($invoiceId === '') {
        $error = 'PayKeeper не вернул invoice_id.';
        return null;
    }

    $invoiceUrl = (string)($result['data']['invoice_url'] ?? '');
    if ($invoiceUrl === '') {
        $invoiceUrl = rtrim((string)$config['base_url'], '/') . '/bill/' . $invoiceId . '/';
    }

    return [
        'invoice_id' => $invoiceId,
        'invoice_url' => $invoiceUrl,
    ];
}

function po_paykeeper_validate_callback_signature(array $payload, string $secretWord): bool
{
    $id = (string)($payload['id'] ?? '');
    $sum = (string)($payload['sum'] ?? '');
    $clientId = (string)($payload['clientid'] ?? '');
    $orderId = (string)($payload['orderid'] ?? '');
    $key = strtolower((string)($payload['key'] ?? ''));

    if ($id === '' || $sum === '' || $key === '' || $secretWord === '') {
        return false;
    }

    $expectedKey = md5($id . $sum . $clientId . $orderId . $secretWord);
    return hash_equals($expectedKey, $key);
}

function po_paykeeper_build_callback_ack(string $id, string $secretWord): string
{
    return 'OK ' . md5($id . $secretWord);
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
