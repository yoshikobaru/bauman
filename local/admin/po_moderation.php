<?php
/**
 * Панель модерации заявок — Политехническое общество
 * URL: /local/admin/po_moderation.php
 * Доступ: Администраторы и Модераторы (группа PO_MODERATOR_ID)
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Loader;

// --- Проверка прав ---
$isModerator = $USER->IsAdmin()
    || (defined('PO_MODERATOR_ID') && in_array(PO_MODERATOR_ID, $USER->GetUserGroupArray()));

if (!$USER->IsAuthorized()) {
    $APPLICATION->AuthForm('Авторизуйтесь для доступа к панели модерации.');
}
if (!$isModerator) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
    echo '<div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message">Нет доступа. Требуется роль Модератора или Администратора.</div></div>';
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
    die();
}

Loader::includeModule('highloadblock');

// --- Справочники ---
$typeLabels = [
    'membership'         => 'Вступление (D1)',
    'project_support'    => 'Поддержка проекта (D2)',
    'event_reg'          => 'Регистрация на событие (D3)',
    'reference_visit'    => 'Участие в визите (D4)',
    'reference_org'      => 'Организация визита (D5)',
    'competency_request' => 'Витрина компетенций (D6)',
    'partnership'        => 'Партнёрство (D7)',
    'access_recovery'    => 'Восстановление доступа',
];

$membershipTypeLabels = [
    'basic'    => 'Базовое',
    'premium'  => 'Профессиональное',
    'partner'  => 'Партнёрское',
    'honorary' => 'Почётное',
];

$statusLabels = [
    'new'       => 'Новая',
    'in_review' => 'На рассмотрении',
    'approved'  => 'Одобрено',
    'rejected'  => 'Отклонено',
];

$statusColors = [
    'new'       => '#e67e22',
    'in_review' => '#2980b9',
    'approved'  => '#27ae60',
    'rejected'  => '#c0392b',
];

// Тип членства → группа Битрикс
$membershipGroups = [
    'basic'    => defined('PO_MEMBER_BASIC_ID')    ? PO_MEMBER_BASIC_ID    : 0,
    'premium'  => defined('PO_MEMBER_PREMIUM_ID')  ? PO_MEMBER_PREMIUM_ID  : 0,
    'partner'  => defined('PO_PARTNER_ID')          ? PO_PARTNER_ID         : 0,
    'honorary' => function_exists('po_member_group_id') ? po_member_group_id('honorary') : 0,
];

// --- HL-блок ---
$hlClass  = null;
$hlError  = '';
if (defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
    $hlEntityData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
    if ($hlEntityData) {
        $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntityData)->getDataClass();
    } else {
        $hlError = 'HL-блок Applications не найден (HL_APPLICATIONS_ID=' . HL_APPLICATIONS_ID . ')';
    }
} else {
    $hlError = 'Константа HL_APPLICATIONS_ID не задана или равна 0.';
}

// --- Обработка действий ---
$actionResult = ['ok' => false, 'msg' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && $hlClass) {
    $action    = trim($_POST['action']     ?? '');
    $appId     = (int)($_POST['app_id']    ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');
    $comment   = trim($_POST['comment']    ?? '');

    if ($appId > 0 && $action === 'update_status') {
        $app = $hlClass::getById($appId)->fetch();

        if ($app && array_key_exists($newStatus, $statusLabels)) {
            // Обновляем статус + комментарий модератора
            $appData = json_decode($app['UF_DATA'] ?? '{}', true) ?: [];
            if ($comment !== '') {
                $appData['_moderator_comment'] = $comment;
            }
            $appData['_moderated_by'] = $USER->GetID();
            $appData['_moderated_at'] = date('d.m.Y H:i');

            $updateRes = $hlClass::update($appId, [
                'UF_STATUS' => $newStatus,
                'UF_DATA'   => json_encode($appData, JSON_UNESCAPED_UNICODE),
            ]);

            if ($updateRes->isSuccess()) {
                $actionResult = ['ok' => true, 'msg' => 'Статус обновлён.'];
                po_logAction('admin_status_change', 'application', $appId,
                    'Статус заявки #' . $appId . ' → ' . $newStatus . ($comment ? ' | ' . $comment : ''));

                $resolvedUserId = function_exists('po_application_resolve_user_id')
                    ? po_application_resolve_user_id($app, $appData)
                    : (int)$app['UF_USER_ID'];

                // --- При одобрении заявки на членство: переводим пользователя в группу ---
                if ($newStatus === 'approved' && $app['UF_TYPE'] === 'membership' && $resolvedUserId > 0) {
                    $membershipType = $appData['membership_type'] ?? 'basic';
                    $targetGroup    = $membershipGroups[$membershipType] ?? $membershipGroups['basic'];
                    $typeLabel      = $membershipTypeLabels[$membershipType] ?? $membershipType;

                    if ($targetGroup > 0) {
                        $currentGroups = CUser::GetUserGroup($resolvedUserId);
                        // Убираем все членские группы и регистрированных
                        $removeGroups = array_filter([
                            defined('PO_REGISTERED_ID')       ? PO_REGISTERED_ID       : 0,
                            defined('PO_MEMBER_BASIC_ID')     ? PO_MEMBER_BASIC_ID     : 0,
                            defined('PO_MEMBER_PREMIUM_ID')   ? PO_MEMBER_PREMIUM_ID   : 0,
                            function_exists('po_member_group_id') ? po_member_group_id('honorary') : 0,
                            defined('PO_PARTNER_ID')          ? PO_PARTNER_ID          : 0,
                        ]);
                        $newGroups = array_values(array_filter($currentGroups, fn($g) => !in_array((int)$g, $removeGroups)));
                        $newGroups[] = $targetGroup;
                        CUser::SetUserGroup($resolvedUserId, $newGroups);
                        $oUserUpdate = new CUser();
                        $oUserUpdate->Update($resolvedUserId, [
                            'UF_MEMBERSHIP_STATUS'  => 'active',
                            'UF_MEMBERSHIP_TYPE'    => $membershipType,
                            'UF_MEMBERSHIP_EXPIRES' => date('d.m.Y', strtotime('+1 year')),
                        ]);
                        if ((int)$app['UF_USER_ID'] <= 0) {
                            $hlClass::update($appId, ['UF_USER_ID' => $resolvedUserId]);
                        }
                        $actionResult['msg'] .= ' Пользователь #' . $resolvedUserId . ' переведён в группу «' . $typeLabel . '».';
                    } else {
                        $actionResult['msg'] .= ' Внимание: группа для типа «' . $typeLabel . '» не настроена — назначьте группу вручную.';
                    }
                } elseif ($newStatus === 'approved' && $app['UF_TYPE'] === 'membership' && $resolvedUserId <= 0) {
                    $actionResult['msg'] .= ' Внимание: пользователь не найден по email — группу нужно назначить вручную.';
                }
                if (in_array($newStatus, ['in_review', 'rejected'], true) && $app['UF_TYPE'] === 'membership' && $resolvedUserId > 0) {
                    $statusForUser = $newStatus === 'in_review' ? 'in_review' : 'rejected';
                    $membershipType = $appData['membership_type'] ?? '';
                    $oUserUpdate = new CUser();
                    $oUserUpdate->Update($resolvedUserId, [
                        'UF_MEMBERSHIP_STATUS' => $statusForUser,
                        'UF_MEMBERSHIP_TYPE'   => $membershipType,
                    ]);
                }

                // --- Email-уведомление пользователю при смене статуса ---
                if ($resolvedUserId > 0) {
                    $userRow = CUser::GetByID($resolvedUserId)->Fetch();
                    if ($userRow && $userRow['EMAIL']) {
                        $statusText  = $statusLabels[$newStatus] ?? $newStatus;
                        $typeText    = $typeLabels[$app['UF_TYPE']] ?? $app['UF_TYPE'];
                        $commentText = $comment ? "\n\nКомментарий: {$comment}" : '';
                        $adminEmail  = defined('PO_ADMIN_EMAIL') ? PO_ADMIN_EMAIL : 'noreply@bauman-polytech.ru';
                        $toEmail     = $userRow['EMAIL'];
                        $subject     = '[ПОЛИТЕХ] Статус вашей заявки изменён';
                        $body        =
                            "Уважаемый(ая) {$userRow['NAME']},\n\n" .
                            "Статус вашей заявки «{$typeText}» изменён на: {$statusText}.{$commentText}\n\n" .
                            "Политехническое общество выпускников МГТУ им. Н.Э. Баумана";
                        $headers  = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
                        $headers .= "From: {$adminEmail}\r\n";
                        $headers .= "Reply-To: {$adminEmail}\r\n";
                        @mail($toEmail, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
                    }
                }
            } else {
                $actionResult = ['ok' => false, 'msg' => 'Ошибка: ' . implode(', ', $updateRes->getErrorMessages())];
            }
        }
    }
}

// --- Фильтры ---
$filterType   = trim($_GET['filter_type']   ?? '');
$filterStatus = trim($_GET['filter_status'] ?? '');
$filterUser   = trim($_GET['filter_user']   ?? '');
$detailId     = (int)($_GET['detail'] ?? 0);

// --- Загрузка заявок ---
$applications = [];
if ($hlClass) {
    $arFilter = [];
    if ($filterType   && array_key_exists($filterType,   $typeLabels))   $arFilter['UF_TYPE']   = $filterType;
    if ($filterStatus && array_key_exists($filterStatus, $statusLabels)) $arFilter['UF_STATUS'] = $filterStatus;

    $res = $hlClass::getList([
        'filter' => $arFilter,
        'order'  => ['UF_DATE_CREATE' => 'DESC'],
        'limit'  => 200,
    ]);
    while ($row = $res->fetch()) {
        // Фильтр по ФИО/email (на стороне PHP)
        if ($filterUser) {
            $appData = json_decode($row['UF_DATA'] ?? '{}', true) ?: [];
            $search = mb_strtolower($filterUser);
            $haystack = mb_strtolower(
                ($appData['first_name'] ?? '') . ' ' .
                ($appData['last_name']  ?? '') . ' ' .
                ($appData['name']       ?? '') . ' ' .
                ($appData['fio']        ?? '') . ' ' .
                ($appData['email']      ?? '') . ' ' .
                ($appData['old_email']  ?? '') . ' ' .
                ($appData['company']    ?? '')
            );
            if (strpos($haystack, $search) === false) continue;
        }
        $applications[] = $row;
    }
}

// Подсчёт по статусам для бейджей
$counts = ['new' => 0, 'in_review' => 0, 'approved' => 0, 'rejected' => 0];
if ($hlClass) {
    foreach ($statusLabels as $sKey => $sLabel) {
        $c = $hlClass::getList(['filter' => ['UF_STATUS' => $sKey], 'count_total' => true]);
        $counts[$sKey] = $c->getCount();
    }
}

// Деталь заявки
$detailApp     = null;
$detailAppData = [];
if ($detailId > 0 && $hlClass) {
    $detailApp = $hlClass::getById($detailId)->fetch();
    if ($detailApp) {
        $detailAppData = json_decode($detailApp['UF_DATA'] ?? '{}', true) ?: [];
    }
}

$APPLICATION->SetTitle('Модерация заявок — Политехническое общество');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
?>

<style>
.po-mod { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
.po-mod h1 { font-size: 22px; margin: 0 0 20px; color: #1a1a2e; }
.po-stat-bar { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
.po-stat { padding: 10px 20px; border-radius: 8px; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; }
.po-filter-form { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin-bottom: 20px; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
.po-filter-form label { font-size: 12px; color: #666; display: block; margin-bottom: 4px; }
.po-filter-form select, .po-filter-form input { padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; min-width: 160px; }
.po-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.po-table th { background: #1a1a2e; color: #fff; padding: 10px 12px; text-align: left; font-weight: 600; }
.po-table td { padding: 10px 12px; border-bottom: 1px solid #eee; vertical-align: top; }
.po-table tr:hover td { background: #f8f9fa; }
.po-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; color: #fff; font-size: 11px; font-weight: 600; white-space: nowrap; }
.po-btn { padding: 5px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-block; }
.po-btn-blue   { background: #2980b9; color: #fff; }
.po-btn-green  { background: #27ae60; color: #fff; }
.po-btn-red    { background: #c0392b; color: #fff; }
.po-btn-grey   { background: #95a5a6; color: #fff; }
.po-detail { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 24px; }
.po-detail h3 { margin: 0 0 12px; font-size: 16px; }
.po-detail table { width: 100%; border-collapse: collapse; }
.po-detail table td { padding: 6px 10px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.po-detail table td:first-child { font-weight: 600; color: #555; width: 200px; }
.po-alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
.po-alert-ok  { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.po-alert-err { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.po-update-form { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.po-update-form select { padding: 4px 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px; }
.po-update-form input[type=text] { padding: 4px 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px; min-width: 200px; }
.po-empty { text-align: center; padding: 40px; color: #999; font-size: 14px; }
.po-user-info { color: #555; font-size: 12px; }
</style>

<div class="po-mod">

<?php if ($hlError): ?>
<div class="po-alert po-alert-err"><?= htmlspecialchars($hlError) ?></div>
<?php endif; ?>

<?php if ($actionResult['msg']): ?>
<div class="po-alert <?= $actionResult['ok'] ? 'po-alert-ok' : 'po-alert-err' ?>">
    <?= htmlspecialchars($actionResult['msg']) ?>
</div>
<?php endif; ?>

<!-- Главные вкладки: Заявки / Логи -->
<?php $mainSection = ($_GET['section'] ?? 'applications'); ?>
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid #dee2e6">
    <a href="?" style="padding:10px 22px;font-size:14px;font-weight:600;text-decoration:none;border-bottom:<?= $mainSection !== 'logs' ? '3px solid #1a1a2e;color:#1a1a2e' : '3px solid transparent;color:#888' ?>;margin-bottom:-2px">Заявки</a>
    <a href="?section=logs" style="padding:10px 22px;font-size:14px;font-weight:600;text-decoration:none;border-bottom:<?= $mainSection === 'logs' ? '3px solid #1a1a2e;color:#1a1a2e' : '3px solid transparent;color:#888' ?>;margin-bottom:-2px">Логи действий</a>
</div>

<?php if ($mainSection === 'logs'): ?>
<?php
$logClass = null;
if (defined('HL_LOGS_ID') && HL_LOGS_ID > 0) {
    $logEntityData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_LOGS_ID)->fetch();
    if ($logEntityData) {
        $logClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($logEntityData)->getDataClass();
    }
}
if (!$logClass): ?>
<div class="adm-info-message-wrap adm-info-message-red">
    <div class="adm-info-message">
        HL-блок логов не найден. Запустите <code>/setup_logs.php</code> и обновите <code>HL_LOGS_ID</code> в <code>init.php</code>.
    </div>
</div>
<?php else: ?>
<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;background:#f8f9fa;padding:14px;border-radius:8px;margin-bottom:16px">
    <input type="hidden" name="section" value="logs">
    <div>
        <label style="font-size:12px;color:#666;display:block;margin-bottom:4px">Тип действия</label>
        <select name="log_action" style="padding:6px 10px;border:1px solid #ced4da;border-radius:4px;font-size:13px">
            <option value="">— Все —</option>
            <?php foreach (['login','logout','form_submit','profile_update','admin_status_change'] as $la): ?>
            <option value="<?= $la ?>" <?= ($_GET['log_action'] ?? '') === $la ? 'selected' : '' ?>><?= $la ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label style="font-size:12px;color:#666;display:block;margin-bottom:4px">Дата с</label>
        <input type="date" name="log_from" value="<?= htmlspecialchars($_GET['log_from'] ?? '') ?>"
               style="padding:6px 10px;border:1px solid #ced4da;border-radius:4px;font-size:13px">
    </div>
    <div>
        <label style="font-size:12px;color:#666;display:block;margin-bottom:4px">Дата по</label>
        <input type="date" name="log_to" value="<?= htmlspecialchars($_GET['log_to'] ?? '') ?>"
               style="padding:6px 10px;border:1px solid #ced4da;border-radius:4px;font-size:13px">
    </div>
    <div>
        <label style="font-size:12px;color:#666;display:block;margin-bottom:4px">User ID</label>
        <input type="number" name="log_user" value="<?= (int)($_GET['log_user'] ?? 0) ?: '' ?>"
               placeholder="любой" style="padding:6px 10px;border:1px solid #ced4da;border-radius:4px;font-size:13px;width:90px">
    </div>
    <button type="submit" class="po-btn po-btn-blue" style="padding:8px 16px">Применить</button>
    <a href="?section=logs" class="po-btn po-btn-grey" style="padding:8px 16px">Сбросить</a>
</form>
<?php
$logFilter = [];
if (!empty($_GET['log_action']))  $logFilter['UF_ACTION']  = $_GET['log_action'];
if (!empty($_GET['log_user']))    $logFilter['UF_USER_ID'] = (int)$_GET['log_user'];
try {
    if (!empty($_GET['log_from'])) $logFilter['>=UF_DATE_CREATE'] = \Bitrix\Main\Type\DateTime::createFromPhp(new DateTime($_GET['log_from'] . ' 00:00:00'));
    if (!empty($_GET['log_to']))   $logFilter['<=UF_DATE_CREATE'] = \Bitrix\Main\Type\DateTime::createFromPhp(new DateTime($_GET['log_to'] . ' 23:59:59'));
} catch (Exception $e) {}

$logRows = [];
$dbLogs = $logClass::getList([
    'filter' => $logFilter,
    'order'  => ['UF_DATE_CREATE' => 'DESC'],
    'limit'  => 300,
]);
while ($lr = $dbLogs->fetch()) $logRows[] = $lr;
?>
<table class="po-table">
    <thead>
        <tr>
            <th style="width:150px">Дата</th>
            <th style="width:80px">User ID</th>
            <th style="width:160px">Действие</th>
            <th>Описание</th>
            <th style="width:120px">IP</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($logRows)): ?>
    <tr><td colspan="5" class="po-empty">Логи не найдены.</td></tr>
    <?php else: ?>
    <?php
    $actionColors = [
        'login'               => '#27ae60',
        'logout'              => '#7f8c8d',
        'form_submit'         => '#2980b9',
        'profile_update'      => '#8e44ad',
        'admin_status_change' => '#e67e22',
    ];
    foreach ($logRows as $lr):
        $lDate  = !empty($lr['UF_DATE_CREATE']) ? $lr['UF_DATE_CREATE']->format('d.m.Y H:i:s') : '—';
        $lUser  = $lr['UF_USER_ID'] > 0
            ? '<a href="/bitrix/admin/user_admin.php?ID=' . $lr['UF_USER_ID'] . '" target="_blank">#' . $lr['UF_USER_ID'] . '</a>'
            : '<span style="color:#aaa">гость</span>';
        $lColor = $actionColors[$lr['UF_ACTION'] ?? ''] ?? '#555';
    ?>
    <tr>
        <td style="color:#666"><?= $lDate ?></td>
        <td><?= $lUser ?></td>
        <td><span class="po-badge" style="background:<?= $lColor ?>"><?= htmlspecialchars($lr['UF_ACTION'] ?? '') ?></span></td>
        <td><?= htmlspecialchars($lr['UF_DESCRIPTION'] ?? '') ?></td>
        <td style="color:#888;font-size:12px"><?= htmlspecialchars($lr['UF_IP'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
<p style="margin-top:8px;font-size:12px;color:#999">Показано записей: <?= count($logRows) ?></p>
<?php endif; ?>
<?php endif; // section=logs ?>

<!-- Заявки — показываем только если не в разделе логов -->
<?php if ($mainSection !== 'logs'): ?>

<!-- Бейджи статусов -->
<div class="po-stat-bar">
    <a class="po-stat" style="background:#6c757d;" href="?">Все (<?= array_sum($counts) ?>)</a>
    <a class="po-stat" style="background:<?= $statusColors['new'] ?>;" href="?filter_status=new">Новые (<?= $counts['new'] ?>)</a>
    <a class="po-stat" style="background:<?= $statusColors['in_review'] ?>;" href="?filter_status=in_review">На рассмотрении (<?= $counts['in_review'] ?>)</a>
    <a class="po-stat" style="background:<?= $statusColors['approved'] ?>;" href="?filter_status=approved">Одобрено (<?= $counts['approved'] ?>)</a>
    <a class="po-stat" style="background:<?= $statusColors['rejected'] ?>;" href="?filter_status=rejected">Отклонено (<?= $counts['rejected'] ?>)</a>
</div>

<!-- Фильтры -->
<form method="GET" class="po-filter-form">
    <div>
        <label>Тип заявки</label>
        <select name="filter_type">
            <option value="">— Все типы —</option>
            <?php foreach ($typeLabels as $key => $label): ?>
            <option value="<?= $key ?>" <?= $filterType === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label>Статус</label>
        <select name="filter_status">
            <option value="">— Все статусы —</option>
            <?php foreach ($statusLabels as $key => $label): ?>
            <option value="<?= $key ?>" <?= $filterStatus === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label>Поиск (ФИО, email, компания)</label>
        <input type="text" name="filter_user" value="<?= htmlspecialchars($filterUser) ?>" placeholder="Введите текст...">
    </div>
    <div>
        <button type="submit" class="po-btn po-btn-blue">Применить</button>
        <a href="?" class="po-btn po-btn-grey" style="margin-left:4px;">Сбросить</a>
    </div>
</form>

<!-- Детальный просмотр заявки -->
<?php if ($detailApp): ?>
<?php
    $detailUser = null;
    if ($detailApp['UF_USER_ID'] > 0) {
        $detailUser = CUser::GetByID($detailApp['UF_USER_ID'])->Fetch();
    }
    $detailStatus = $detailApp['UF_STATUS'] ?? 'new';
    $modComment   = $detailAppData['_moderator_comment'] ?? '';
    unset($detailAppData['_moderator_comment'], $detailAppData['_moderated_by'], $detailAppData['_moderated_at']);
?>
<div class="po-detail">
    <h3>
        Заявка #<?= (int)$detailApp['ID'] ?> —
        <?= htmlspecialchars($typeLabels[$detailApp['UF_TYPE']] ?? $detailApp['UF_TYPE']) ?>
        &nbsp;
        <span class="po-badge" style="background:<?= $statusColors[$detailStatus] ?? '#999' ?>">
            <?= $statusLabels[$detailStatus] ?? $detailStatus ?>
        </span>
        <a href="?" style="float:right;font-size:12px;color:#999;">✕ Закрыть</a>
    </h3>

    <table>
        <tr><td>Дата подачи</td><td><?= $detailApp['UF_DATE_CREATE'] ? (new DateTime($detailApp['UF_DATE_CREATE']))->format('d.m.Y H:i') : '—' ?></td></tr>
        <?php if ($detailUser): ?>
        <tr>
            <td>Пользователь</td>
            <td>
                <?= htmlspecialchars(trim(($detailUser['LAST_NAME'] ?? '') . ' ' . ($detailUser['NAME'] ?? ''))) ?>
                <br><span style="color:#999"><?= htmlspecialchars($detailUser['EMAIL'] ?? '') ?></span>
                <br><a href="/bitrix/admin/user_edit.php?ID=<?= (int)$detailApp['UF_USER_ID'] ?>&lang=ru" target="_blank" style="font-size:11px;">Открыть профиль →</a>
            </td>
        </tr>
        <?php endif; ?>
        <?php foreach ($detailAppData as $k => $v): if (strpos($k, '_') === 0) continue; ?>
        <tr>
            <td><?= htmlspecialchars($k) ?></td>
            <td><?= htmlspecialchars((string)$v) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if ($modComment): ?>
        <tr><td>Комментарий модератора</td><td style="color:#666;font-style:italic;"><?= htmlspecialchars($modComment) ?></td></tr>
        <?php endif; ?>
    </table>

    <!-- Форма смены статуса -->
    <form method="POST" style="margin-top:16px;padding-top:16px;border-top:1px solid #eee;">
        <input type="hidden" name="sessid"    value="<?= bitrix_sessid() ?>">
        <input type="hidden" name="action"    value="update_status">
        <input type="hidden" name="app_id"    value="<?= (int)$detailApp['ID'] ?>">
        <div class="po-update-form">
            <select name="new_status">
                <?php foreach ($statusLabels as $key => $label): ?>
                <option value="<?= $key ?>" <?= $detailStatus === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="comment" value="" placeholder="Комментарий для пользователя (необязательно)">
            <button type="submit" class="po-btn po-btn-green">Сохранить статус</button>
        </div>
        <?php if ($detailApp['UF_TYPE'] === 'membership' && ($detailApp['UF_STATUS'] ?? '') !== 'approved'): ?>
        <p style="margin-top:8px;font-size:11px;color:#888;">
            ⚠ При выборе статуса «Одобрено» пользователь будет автоматически переведён в группу по типу членства
            (<?= htmlspecialchars($membershipTypeLabels[$detailAppData['membership_type'] ?? 'basic'] ?? 'Базовое') ?>)
            и получит email-уведомление. Если в заявке нет привязки к аккаунту, поиск выполняется по email.
        </p>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>

<!-- Таблица заявок -->
<?php if (empty($applications)): ?>
<div class="po-empty">Заявок не найдено.</div>
<?php else: ?>
<table class="po-table">
    <thead>
        <tr>
            <th style="width:50px">#</th>
            <th>Тип</th>
            <th>Дата</th>
            <th>Заявитель</th>
            <th>Статус</th>
            <th>Быстрые действия</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($applications as $app):
        $appData  = json_decode($app['UF_DATA'] ?? '{}', true) ?: [];
        $appStatus = $app['UF_STATUS'] ?? 'new';

        // Имя заявителя из данных формы
        $applicantName  = trim(($appData['first_name'] ?? '') . ' ' . ($appData['last_name'] ?? ''));
        if (empty(trim($applicantName))) {
            $applicantName = $appData['fio'] ?? $appData['name'] ?? $appData['company'] ?? $appData['contact_name'] ?? '—';
        }
        $applicantEmail = $appData['email'] ?? $appData['old_email'] ?? '';
        $membershipTypeLabel = '';
        if (($app['UF_TYPE'] ?? '') === 'membership' && !empty($appData['membership_type'])) {
            $membershipTypeLabel = $membershipTypeLabels[$appData['membership_type']] ?? $appData['membership_type'];
        }

        // Имя из профиля пользователя если есть
        $linkedUserName = '';
        if ($app['UF_USER_ID'] > 0) {
            $lu = CUser::GetByID($app['UF_USER_ID'])->Fetch();
            if ($lu) {
                $linkedUserName = trim(($lu['LAST_NAME'] ?? '') . ' ' . ($lu['NAME'] ?? ''));
            }
        }

        $dateStr = '';
        if ($app['UF_DATE_CREATE']) {
            try { $dateStr = (new DateTime($app['UF_DATE_CREATE']))->format('d.m.Y H:i'); } catch (\Exception $e) {}
        }
    ?>
    <tr>
        <td><?= (int)$app['ID'] ?></td>
        <td><?= htmlspecialchars($typeLabels[$app['UF_TYPE']] ?? $app['UF_TYPE']) ?><?php if ($membershipTypeLabel): ?><br><span class="po-user-info"><?= htmlspecialchars($membershipTypeLabel) ?></span><?php endif; ?></td>
        <td style="white-space:nowrap"><?= $dateStr ?></td>
        <td>
            <?= htmlspecialchars($applicantName) ?>
            <?php if ($applicantEmail): ?>
            <br><span class="po-user-info"><?= htmlspecialchars($applicantEmail) ?></span>
            <?php endif; ?>
            <?php if ($linkedUserName): ?>
            <br><span class="po-user-info">👤 <?= htmlspecialchars($linkedUserName) ?></span>
            <?php endif; ?>
        </td>
        <td>
            <span class="po-badge" style="background:<?= $statusColors[$appStatus] ?? '#999' ?>">
                <?= htmlspecialchars($statusLabels[$appStatus] ?? $appStatus) ?>
            </span>
        </td>
        <td>
            <a href="?detail=<?= (int)$app['ID'] ?>&filter_type=<?= urlencode($filterType) ?>&filter_status=<?= urlencode($filterStatus) ?>"
               class="po-btn po-btn-blue">Детали / Статус</a>

            <?php if ($appStatus === 'new'): ?>
            <form method="POST" style="display:inline;margin-left:4px;">
                <input type="hidden" name="sessid"     value="<?= bitrix_sessid() ?>">
                <input type="hidden" name="action"     value="update_status">
                <input type="hidden" name="app_id"     value="<?= (int)$app['ID'] ?>">
                <input type="hidden" name="new_status" value="in_review">
                <button type="submit" class="po-btn po-btn-grey">→ В работу</button>
            </form>
            <?php endif; ?>

            <?php if (in_array($appStatus, ['new', 'in_review'])): ?>
            <form method="POST" style="display:inline;margin-left:4px;">
                <input type="hidden" name="sessid"     value="<?= bitrix_sessid() ?>">
                <input type="hidden" name="action"     value="update_status">
                <input type="hidden" name="app_id"     value="<?= (int)$app['ID'] ?>">
                <input type="hidden" name="new_status" value="approved">
                <button type="submit" class="po-btn po-btn-green"
                    onclick="return confirm('Одобрить заявку #<?= (int)$app['ID'] ?>?')">✓ Одобрить</button>
            </form>
            <form method="POST" style="display:inline;margin-left:4px;">
                <input type="hidden" name="sessid"     value="<?= bitrix_sessid() ?>">
                <input type="hidden" name="action"     value="update_status">
                <input type="hidden" name="app_id"     value="<?= (int)$app['ID'] ?>">
                <input type="hidden" name="new_status" value="rejected">
                <button type="submit" class="po-btn po-btn-red"
                    onclick="return confirm('Отклонить заявку #<?= (int)$app['ID'] ?>?')">✕ Отклонить</button>
            </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p style="margin-top:8px;font-size:12px;color:#999;">Показано: <?= count($applications) ?> заявок.</p>
<?php endif; ?>

<?php endif; // конец секции заявок ($mainSection !== 'logs') ?>

<!-- (старый блок логов удалён — логи теперь во вкладке выше) -->

</div>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'); ?>
