<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мой профиль");

if (!$USER->IsAuthorized()) {
    LocalRedirect('/authorization/?back_url=/profile/');
}

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$userId = $USER->GetID();
$saveError = '';
$saveOk    = !empty($_GET['saved']);
$joinedOk  = !empty($_GET['joined']);

// — Загрузка данных пользователя —
$dbUser = CUser::GetByID($userId);
$arUser = $dbUser->Fetch() ?: [];

// — Обработчик сохранения профиля —
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_action'])) {
    $firstName   = trim($_POST['first_name']   ?? '');
    $lastName    = trim($_POST['last_name']    ?? '');
    $secondName  = trim($_POST['second_name']  ?? '');
    $dob         = trim($_POST['dob']          ?? '');
    $telegram    = trim($_POST['telegram']     ?? '');
    $gradYear    = trim($_POST['grad_year']    ?? '');
    $gradDept    = trim($_POST['grad_dept']    ?? '');
    $diplomaSeries = trim($_POST['diploma_series'] ?? '');
    $diplomaNumber = trim($_POST['diploma_number'] ?? '');
    $diplomaDate   = trim($_POST['diploma_date']   ?? '');
    $achievements  = trim($_POST['achievements']   ?? '');

    $userUpdateData = [
        'NAME'            => $firstName,
        'LAST_NAME'       => $lastName,
        'SECOND_NAME'     => $secondName,
        'UF_DOB'          => $dob,
        'UF_TELEGRAM'     => $telegram,
        'UF_GRADUATE_YEAR'=> $gradYear,
        'UF_GRADUATE_DEPT'=> $gradDept,
        'UF_DIPLOMA_SERIES' => $diplomaSeries,
        'UF_DIPLOMA_NUMBER' => $diplomaNumber,
        'UF_DIPLOMA_DATE'   => $diplomaDate,
        'PERSONAL_NOTES'    => $achievements,
    ];

    if (!empty($_FILES['photo']['name']) && (int)$_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            $saveError = 'Аватар: допустимы только jpg, jpeg, png';
        } else {
            $userUpdateData['PERSONAL_PHOTO'] = $_FILES['photo'];
        }
    }

    $result = false;
    $oUser  = new CUser();
    if ($saveError === '') {
        $result = $oUser->Update($userId, $userUpdateData);
    }

    if ($result) {
        po_logAction('profile_update', 'user', (int)$userId, 'Обновление профиля');
        LocalRedirect('/profile/?saved=1');
    } else {
        if ($saveError === '') {
            $saveError = $oUser->LAST_ERROR ?: 'Ошибка сохранения';
        }
        // Обновляем arUser свежими данными из POST
        $arUser['NAME']        = $firstName;
        $arUser['LAST_NAME']   = $lastName;
        $arUser['SECOND_NAME'] = $secondName;
        $arUser['UF_DOB']      = $dob;
        $arUser['UF_TELEGRAM'] = $telegram;
        $arUser['UF_GRADUATE_YEAR'] = $gradYear;
        $arUser['UF_GRADUATE_DEPT'] = $gradDept;
        $arUser['UF_DIPLOMA_SERIES'] = $diplomaSeries;
        $arUser['UF_DIPLOMA_NUMBER'] = $diplomaNumber;
        $arUser['UF_DIPLOMA_DATE']   = $diplomaDate;
        $arUser['PERSONAL_NOTES']    = $achievements;
    }
}

// Вспомогательные переменные
$membershipType   = (string)($arUser['UF_MEMBERSHIP_TYPE']   ?? '');
$membershipStatus = (string)($arUser['UF_MEMBERSHIP_STATUS'] ?? '');
$membershipExpires = (string)($arUser['UF_MEMBERSHIP_EXPIRES'] ?? '');
$_ug      = $USER->GetUserGroupArray();
$isMember = defined('PO_MEMBER_BASIC_ID') && (
    in_array(PO_MEMBER_BASIC_ID,   $_ug) ||
    in_array(PO_MEMBER_PREMIUM_ID, $_ug) ||
    in_array(PO_PARTNER_ID,        $_ug)
);

$groupMembershipType = '';
if (defined('PO_PARTNER_ID') && in_array(PO_PARTNER_ID, $_ug, true)) {
    $groupMembershipType = 'partner';
} elseif (defined('PO_MEMBER_PREMIUM_ID') && in_array(PO_MEMBER_PREMIUM_ID, $_ug, true)) {
    $groupMembershipType = 'premium';
} elseif (defined('PO_MEMBER_BASIC_ID') && in_array(PO_MEMBER_BASIC_ID, $_ug, true)) {
    $groupMembershipType = 'basic';
}
if ($groupMembershipType !== '') {
    $membershipType = $groupMembershipType;
}
if ($membershipStatus === 'approved') {
    $membershipStatus = 'active';
}
if ($membershipStatus === 'new') {
    $membershipStatus = 'pending';
}
if ($membershipStatus === '' && $isMember) {
    $membershipStatus = 'active';
}

$typeLabels = [
    'basic'   => ['label' => 'Базовое',         'price' => '1 000 Р',          'class' => 'account__rate--basic'],
    'premium' => ['label' => 'Профессиональное', 'price' => '50 000 Р',         'class' => 'account__rate--proff'],
    'partner' => ['label' => 'Партнёрское',      'price' => 'Инд. условия',     'class' => 'account__rate--proff'],
    'honorary'=> ['label' => 'Почётное',         'price' => 'Безвозмездно',     'class' => 'account__rate--basic'],
];
$statusLabels = [
    'pending'  => ['label' => 'На рассмотрении', 'class' => 'account__rate-status--pending'],
    'in_review'=> ['label' => 'На модерации',    'class' => 'account__rate-status--in-review'],
    'active'   => ['label' => 'Активен',         'class' => 'account__rate-status--active'],
    'expired'  => ['label' => 'Истёк',           'class' => 'account__rate-status--expired'],
    'rejected' => ['label' => 'Отклонено',        'class' => 'account__rate-status--error'],
];
$currentType   = $typeLabels[$membershipType]   ?? null;
$currentStatus = $statusLabels[$membershipStatus] ?? null;
$verificationColors = [
    'basic' => '#7f8c8d',
    'premium' => '#f0a500',
    'partner' => '#2980b9',
    'honorary' => '#8e44ad',
];
$verificationBadgeColor = $verificationColors[$membershipType] ?? '#7f8c8d';
$showVerificationBadge = $membershipStatus === 'active';
$avatarSrc = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160"><rect width="160" height="160" fill="#f1f1f1"/><circle cx="80" cy="58" r="26" fill="#c7c7c7"/><path d="M24 142c8-28 30-44 56-44s48 16 56 44" fill="#c7c7c7"/></svg>');
if (!empty($arUser['PERSONAL_PHOTO'])) {
    $avatarPath = CFile::GetPath((int)$arUser['PERSONAL_PHOTO']);
    if ($avatarPath) {
        $avatarSrc = $avatarPath;
    }
}
$profileDobInputValue = trim((string)($arUser['UF_DOB'] ?? ''));
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $profileDobInputValue)) {
    [$yyDob, $mmDob, $ddDob] = explode('-', $profileDobInputValue);
    $profileDobInputValue = $ddDob . '.' . $mmDob . '.' . $yyDob;
}
$profileDiplomaDateInputValue = trim((string)($arUser['UF_DIPLOMA_DATE'] ?? ''));
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $profileDiplomaDateInputValue)) {
    [$yyDip, $mmDip, $ddDip] = explode('-', $profileDiplomaDateInputValue);
    $profileDiplomaDateInputValue = $ddDip . '.' . $mmDip . '.' . $yyDip;
}
?>

<main>
<style>
.po-date-field {
    position: relative;
}
.po-date-field input[type="text"] {
    width: 100%;
    box-sizing: border-box;
    padding-right: 42px;
}
.po-date-field__btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    border: none;
    background: transparent;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #8a8a8a;
    cursor: pointer;
}
.po-date-field__native {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    opacity: 0;
    border: none;
    margin: 0;
    padding: 0;
    cursor: pointer;
}
.profile-section.is-view .profile-editable,
.profile-section.is-view .profile-editable[readonly] {
    pointer-events: none;
    color: #2a2a2a;
    border: 1px solid #dddddd;
    background: #f7f7f7;
}
.profile-section.is-view textarea.profile-editable {
    resize: none;
}
.profile-section.is-view .po-date-field__btn,
.profile-section.is-view .po-date-field__native {
    display: none;
}
.profile-verification-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    margin-left: 8px;
}
#profile-photo-save {
    margin-top: 12px;
}
</style>
		<section class="account">
            <div class="container">
                <div class="account__wrapper">
                    <div class="account__sidebar">
                        <div class="account__menu">
                        <?php $tab = $_GET['tab'] ?? 'profile'; ?>
                        <a href="/profile/" class="account__menu-item <?= $tab === 'profile' ? 'account__menu-item--active' : '' ?>">Мой профиль</a>
                        <a href="/profile/security/" class="account__menu-item">Безопасность</a>
                        <a href="/profile/?tab=activities" class="account__menu-item <?= $tab === 'activities' ? 'account__menu-item--active' : '' ?>">Мои активности</a>
                        <a href="/profile/?tab=applications" class="account__menu-item <?= $tab === 'applications' ? 'account__menu-item--active' : '' ?>">Мои заявки</a>
                        <?php if (defined('PO_PARTNER_ID') && in_array(PO_PARTNER_ID, $USER->GetUserGroupArray())): ?>
                        <a href="/profile/?tab=company" class="account__menu-item <?= $tab === 'company' ? 'account__menu-item--active' : '' ?>">Моя компания</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="account__main">

                    <?php if ($tab === 'activities'): ?>
                    <!-- Мои активности -->
                    <div class="account__block">
                        <h2 class="account__title">Мои активности</h2>

                        <?php
                        $_userGroupsProfile = $USER->GetUserGroupArray();
                        $tgChats = [
                            PO_MEMBER_BASIC_ID   => [
                                'title' => 'Общий чат Политехнического общества',
                                'desc'  => 'Обсуждения, новости, анонсы для всех членов общества',
                                'url'   => '#',
                                'icon'  => '💬',
                                'type'  => 'basic',
                            ],
                            PO_MEMBER_PREMIUM_ID => [
                                'title' => 'VIP-канал для почётных членов',
                                'desc'  => 'Закрытый канал с эксклюзивными материалами и нетворкингом',
                                'url'   => '#',
                                'icon'  => '⭐',
                                'type'  => 'premium',
                            ],
                            PO_PARTNER_ID        => [
                                'title' => 'Канал для партнёров',
                                'desc'  => 'Совместные проекты, вакансии и партнёрские предложения',
                                'url'   => '#',
                                'icon'  => '🤝',
                                'type'  => 'partner',
                            ],
                        ];
                        $myTgChats = [];
                        foreach ($tgChats as $groupId => $chatInfo) {
                            if (in_array($groupId, $_userGroupsProfile)) {
                                $myTgChats[] = $chatInfo;
                            }
                        }

                        $arEvents    = [];
                        $arDonations = [];
                        if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
                            $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
                            if ($hlEntity) {
                                $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                                $dbAll = $hlClass::getList([
                                    'filter' => ['UF_USER_ID' => (int)$userId, 'UF_TYPE' => ['event_reg', 'project_support']],
                                    'order'  => ['UF_DATE_CREATE' => 'DESC'],
                                ]);
                                while ($row = $dbAll->fetch()) {
                                    if ($row['UF_TYPE'] === 'event_reg') {
                                        $arEvents[] = $row;
                                    } elseif ($row['UF_TYPE'] === 'project_support') {
                                        $arDonations[] = $row;
                                    }
                                }
                            }
                        }

                        // маппинг статусов в бейджи по Figma
                        $eventBadgeClass = function($status) {
                            return match($status) {
                                'approved'  => 'activities__badge--active',
                                'in_review' => 'activities__badge--review',
                                'rejected'  => 'activities__badge--rejected',
                                default     => 'activities__badge--new',
                            };
                        };
                        $eventBadgeLabel = function($status) {
                            return match($status) {
                                'approved'  => 'Активно',
                                'in_review' => 'На рассмотрении',
                                'rejected'  => 'Отклонено',
                                default     => 'Новая',
                            };
                        };
                        // completed = approved + прошло
                        $isCompleted = function($status) {
                            return $status === 'approved';
                        };
                        ?>

                        <?php if (!empty($myTgChats)): ?>
                        <div class="activities__tg-section">
                            <p class="activities__tg-title">Доступные Telegram-чаты</p>
                            <div class="activities__tg-list">
                                <?php foreach ($myTgChats as $chat):
                                    $iconClass = 'activities__tg-icon--' . ($chat['type'] ?? 'basic');
                                ?>
                                <div class="activities__tg-card">
                                    <div class="activities__tg-icon <?= $iconClass ?>">
                                        <?= $chat['icon'] ?>
                                    </div>
                                    <div class="activities__tg-body">
                                        <div class="activities__tg-name"><?= htmlspecialchars($chat['title']) ?></div>
                                        <div class="activities__tg-desc"><?= htmlspecialchars($chat['desc']) ?></div>
                                    </div>
                                    <div class="activities__tg-action">
                                        <?php if ($chat['url'] !== '#'): ?>
                                        <a href="<?= htmlspecialchars($chat['url']) ?>" target="_blank" rel="noopener"
                                           class="btn" style="white-space:nowrap;font-size:14px;padding:8px 18px">
                                            Открыть
                                        </a>
                                        <?php else: ?>
                                        <span style="color:#aaa;font-size:13px;white-space:nowrap">Скоро</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Мероприятия -->
                        <div class="activities__section">
                            <div class="activities__section-header">
                                <span class="activities__section-title">Мероприятия</span>
                                <a href="/news/?type=events" class="activities__section-link">
                                    Показать все
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                        <path d="M12 5v14M5 12l7-7 7 7"/>
                                    </svg>
                                </a>
                            </div>

                            <?php if (empty($arEvents)): ?>
                            <div class="activities__empty">
                                Вы пока не регистрировались на мероприятия.
                                <a href="/news/?type=events">Посмотреть ближайшие события →</a>
                            </div>
                            <?php else: ?>
                            <div class="activities__events-grid">
                                <?php foreach ($arEvents as $ev):
                                    $evData      = json_decode($ev['UF_DATA'] ?? '{}', true) ?: [];
                                    $evTitle     = htmlspecialchars($evData['event_name'] ?? ('Событие #' . ($ev['UF_ELEMENT_ID'] ?? $ev['ID'])));
                                    $evDateRaw   = !empty($ev['UF_DATE_CREATE']) ? $ev['UF_DATE_CREATE']->format('d.m.Y') : '';
                                    $evStatus    = $ev['UF_STATUS'] ?? 'new';
                                    $evBadgeCls  = $eventBadgeClass($evStatus);
                                    $evBadgeLbl  = $eventBadgeLabel($evStatus);
                                    $evCompleted = $isCompleted($evStatus);
                                    $evImg       = $evData['event_image'] ?? '';
                                ?>
                                <div class="activities__event-card">
                                    <?php if ($evImg): ?>
                                    <img src="<?= htmlspecialchars($evImg) ?>"
                                         alt="<?= $evTitle ?>"
                                         class="activities__event-img<?= $evCompleted ? ' activities__event-img--completed' : '' ?>">
                                    <?php else: ?>
                                    <div class="activities__event-img" style="display:flex;align-items:center;justify-content:center;background:#e8e8e8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#c0c0c0" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    </div>
                                    <?php endif; ?>
                                    <div class="activities__event-body">
                                        <div class="activities__event-badge-row">
                                            <span class="activities__badge <?= $evBadgeCls ?>"><?= $evBadgeLbl ?></span>
                                        </div>
                                        <div class="activities__event-title"><?= $evTitle ?></div>
                                        <?php if ($evDateRaw): ?>
                                        <div class="activities__event-date"><?= $evDateRaw ?></div>
                                        <?php endif; ?>
                                        <a href="/news/<?= (int)($ev['UF_ELEMENT_ID'] ?? 0) ?>/"
                                           class="activities__event-btn<?= $evCompleted ? ' activities__event-btn--disabled' : '' ?>">
                                            <?= $evCompleted ? 'Завершено' : 'Подробнее' ?>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- История пожертвований -->
                        <div class="activities__section" id="activities-donations">
                            <div class="activities__section-header">
                                <span class="activities__section-title">История пожертвований</span>
                                <?php if (count($arDonations) > 4): ?>
                                <button class="activities__section-link" id="activities-donations-toggle" type="button" data-count="<?= count($arDonations) ?>">
                                    Показать все
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                        <path d="M12 5v14M5 12l7-7 7 7"/>
                                    </svg>
                                </button>
                                <?php endif; ?>
                            </div>

                            <?php if (empty($arDonations)): ?>
                            <div class="activities__empty">
                                Вы пока не поддерживали проекты.
                                <a href="/projects/">Посмотреть проекты →</a>
                            </div>
                            <?php else: ?>
                            <div class="activities__donations-grid" id="activities-donations-grid">
                                <?php foreach ($arDonations as $i => $don):
                                    $donData     = json_decode($don['UF_DATA'] ?? '{}', true) ?: [];
                                    $donTitle    = htmlspecialchars($donData['project_name'] ?? ('Проект #' . ($don['UF_ELEMENT_ID'] ?? $don['ID'])));
                                    $donAmount   = htmlspecialchars($donData['amount'] ?? '—');
                                    $donDateRaw  = !empty($don['UF_DATE_CREATE']) ? $don['UF_DATE_CREATE']->format('d.m.Y') : '';
                                    $donStatus   = $don['UF_STATUS'] ?? 'new';
                                    $donBadgeCls = $eventBadgeClass($donStatus);
                                    $donBadgeLbl = $eventBadgeLabel($donStatus);
                                    $hiddenClass = $i >= 4 ? ' activities__donation-card--hidden' : '';
                                ?>
                                <div class="activities__donation-card<?= $hiddenClass ?>"<?= $i >= 4 ? ' style="display:none"' : '' ?>>
                                    <div class="activities__donation-meta">
                                        <span class="activities__badge <?= $donBadgeCls ?>"><?= $donBadgeLbl ?></span>
                                        <?php if ($donDateRaw): ?>
                                        <span class="activities__donation-date"><?= $donDateRaw ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activities__donation-title"><?= $donTitle ?></div>
                                    <div class="activities__donation-amount">
                                        <span class="activities__donation-amount-label">Сумма</span>
                                        <span class="activities__donation-amount-value"><?= $donAmount ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>

                    <?php elseif ($tab === 'applications'): ?>
                    <!-- Мои заявки -->
                    <div class="account__block">
                        <h2 class="account__title">Мои заявки</h2>
                        <?php
                        $typeLabelsHL = [
                            'membership'         => 'Вступление в общество (D1)',
                            'project_support'    => 'Поддержка проекта (D2)',
                            'event_reg'          => 'Регистрация на событие (D3)',
                            'event_registration' => 'Регистрация на событие (D3)',
                            'reference_visit'    => 'Участие в референс-визите (D4)',
                            'reference_org'      => 'Организация референс-визита (D5)',
                            'competency_request' => 'Запрос в витрине компетенций (D6)',
                            'partnership'        => 'Индустриальное партнёрство (D7)',
                            'vacancy'            => 'Вакансия (карьерная платформа)',
                            'resume'             => 'Резюме (карьерная платформа)',
                            'access_recovery'    => 'Восстановление доступа',
                        ];
                        $statusLabelsHL = [
                            'new'       => ['label' => 'Новая',            'color' => '#888'],
                            'in_review' => ['label' => 'На рассмотрении',  'color' => '#2980b9'],
                            'approved'  => ['label' => 'Одобрено',         'color' => '#27ae60'],
                            'rejected'  => ['label' => 'Отклонено',        'color' => '#e74c3c'],
                        ];
                        $arApplications = [];
                        if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
                            $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
                            if ($hlEntity) {
                                $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                                $dbApps  = $hlClass::getList([
                                    'filter' => ['UF_USER_ID' => (int)$userId],
                                    'order'  => ['UF_DATE_CREATE' => 'DESC'],
                                ]);
                                while ($row = $dbApps->fetch()) {
                                    $arApplications[] = $row;
                                }
                            }
                        }
                        if (empty($arApplications)):
                        ?>
                        <p style="color:#888;margin-top:16px">У вас пока нет заявок.</p>
                        <?php else: ?>
                        <table style="width:100%;border-collapse:collapse;margin-top:16px;font-size:14px">
                            <thead>
                                <tr style="background:#f5f5f5">
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Тип заявки</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Дата подачи</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Статус</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Комментарий</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($arApplications as $app):
                                $appType    = $typeLabelsHL[$app['UF_TYPE'] ?? ''] ?? htmlspecialchars($app['UF_TYPE'] ?? '—');
                                $appDate    = !empty($app['UF_DATE_CREATE']) ? $app['UF_DATE_CREATE']->format('d.m.Y H:i') : '—';
                                $stKey      = $app['UF_STATUS'] ?? 'new';
                                $stInfo     = $statusLabelsHL[$stKey] ?? ['label' => htmlspecialchars($stKey), 'color' => '#888'];
                                $appData    = json_decode($app['UF_DATA'] ?? '{}', true) ?: [];
                                $appComment = htmlspecialchars($appData['admin_comment'] ?? '');
                            ?>
                            <tr style="border-bottom:1px solid #f0f0f0">
                                <td style="padding:10px"><?= $appType ?></td>
                                <td style="padding:10px;white-space:nowrap"><?= htmlspecialchars($appDate) ?></td>
                                <td style="padding:10px;font-weight:600;color:<?= $stInfo['color'] ?>"><?= $stInfo['label'] ?></td>
                                <td style="padding:10px;color:#555;font-size:13px"><?= $appComment ?: '<span style="color:#ccc">—</span>' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>

                    <?php elseif ($tab === 'company'): ?>
                    <!-- Для представителей компаний -->
                    <div class="account__block">
                        <h2 class="account__title">Моя компания</h2>
                        <?php
                        $isPartner = defined('PO_PARTNER_ID') && in_array(PO_PARTNER_ID, $USER->GetUserGroupArray());
                        if (!$isPartner):
                        ?>
                        <p style="color:#888;margin-top:16px">Этот раздел доступен только представителям компаний-партнёров.</p>
                        <?php else: ?>

                        <!-- Статус партнёрства -->
                        <?php
                        $partnerStatus = $arUser['UF_MEMBERSHIP_STATUS'] ?? '';
                        $partnerStatusLabels = [
                            'pending'   => ['label' => 'Заявка на рассмотрении', 'color' => '#2980b9'],
                            'approved'  => ['label' => 'Партнёр общества',       'color' => '#27ae60'],
                            'rejected'  => ['label' => 'Отклонено',              'color' => '#e74c3c'],
                        ];
                        $pst = $partnerStatusLabels[$partnerStatus] ?? ['label' => 'Партнёр общества', 'color' => '#27ae60'];
                        ?>
                        <div style="background:#f0f8ff;border-radius:10px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:16px">
                            <span style="font-size:32px">🏢</span>
                            <div>
                                <div style="font-weight:700;font-size:16px;color:<?= $pst['color'] ?>"><?= $pst['label'] ?></div>
                                <?php
                                $companyName = $arUser['UF_COMPANY_NAME'] ?? '';
                                if ($companyName):
                                ?>
                                <div style="color:#555;font-size:14px;margin-top:4px"><?= htmlspecialchars($companyName) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Действия -->
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Доступные возможности</h3>
                        </div>
                        <div style="display:grid;gap:12px;margin-top:16px">
                            <div style="background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                                <div>
                                    <div style="font-weight:600;margin-bottom:4px">Вакансии</div>
                                    <div style="color:#666;font-size:13px">Разместите вакансии вашей компании для выпускников</div>
                                </div>
                                <a href="/resume-form/?form=vacancy" class="btn" style="font-size:13px;padding:10px 20px">Разместить вакансию</a>
                            </div>
                            <div style="background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                                <div>
                                    <div style="font-weight:600;margin-bottom:4px">База резюме</div>
                                    <div style="color:#666;font-size:13px">Просматривайте резюме выпускников МВТУ (МГТУ) им. Н.Э. Баумана</div>
                                </div>
                                <a href="/resume-form/" class="btn btn-empty" style="font-size:13px;padding:10px 20px">Перейти к платформе</a>
                            </div>
                            <div style="background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                                <div>
                                    <div style="font-weight:600;margin-bottom:4px">Витрина компетенций</div>
                                    <div style="color:#666;font-size:13px">Отправьте запрос в НОЦ, СКБ или кафедры МВТУ (МГТУ) им. Н.Э. Баумана</div>
                                </div>
                                <a href="/competencies/" class="btn btn-empty" style="font-size:13px;padding:10px 20px">Открыть витрину</a>
                            </div>
                            <div style="background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                                <div>
                                    <div style="font-weight:600;margin-bottom:4px">Организовать референс-визит</div>
                                    <div style="color:#666;font-size:13px">Показать ваше производство выпускникам и партнёрам</div>
                                </div>
                                <a href="/reference/" class="btn btn-empty" style="font-size:13px;padding:10px 20px">Подать заявку</a>
                            </div>
                        </div>

                        <!-- Мои вакансии из HL -->
                        <?php
                        $arMyVacancies = [];
                        if (defined('HL_VACANCIES_ID') && HL_VACANCIES_ID > 0) {
                            $hlVacEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_VACANCIES_ID)->fetch();
                            if ($hlVacEntity) {
                                $hlVacClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlVacEntity)->getDataClass();
                                $dbMyVac = $hlVacClass::getList([
                                    'filter' => ['UF_USER_ID' => (int)$userId],
                                    'order'  => ['UF_DATE_CREATE' => 'DESC'],
                                ]);
                                while ($vrow = $dbMyVac->fetch()) $arMyVacancies[] = $vrow;
                            }
                        }
                        ?>
                        <?php if (!empty($arMyVacancies)): ?>
                        <div class="account__chapter" style="margin-top:32px">
                            <h3 class="account__subtitle">Мои вакансии</h3>
                        </div>
                        <table style="width:100%;border-collapse:collapse;margin-top:12px;font-size:14px">
                            <thead>
                                <tr style="background:#f5f5f5">
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Должность</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Дата</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $vacStatuses = ['pending' => 'На модерации', 'approved' => 'Опубликована', 'rejected' => 'Отклонена'];
                            foreach ($arMyVacancies as $vac):
                                $vacDate = !empty($vac['UF_DATE_CREATE']) ? $vac['UF_DATE_CREATE']->format('d.m.Y') : '—';
                            ?>
                            <tr style="border-bottom:1px solid #f0f0f0">
                                <td style="padding:10px"><?= htmlspecialchars($vac['UF_POSITION'] ?? '—') ?></td>
                                <td style="padding:10px"><?= $vacDate ?></td>
                                <td style="padding:10px;color:#888"><?= $vacStatuses[$vac['UF_STATUS'] ?? 'pending'] ?? htmlspecialchars($vac['UF_STATUS']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>

                        <?php endif; ?>
                    </div>

                    <?php else: ?>
                        <div class="account__block">
                        <h2 class="account__title">Мой профиль</h2>

                        <?php if ($saveOk): ?>
                            <div class="authorization__alert authorization__alert--success" style="margin-bottom:16px">
                                <p>Данные успешно сохранены</p>
                            </div>
                        <?php endif; ?>
                        <?php if ($joinedOk): ?>
                            <div class="authorization__alert authorization__alert--success" style="margin-bottom:16px">
                                <p>Ваша заявка на вступление принята и находится на рассмотрении</p>
                            </div>
                        <?php endif; ?>
                        <?php if ($saveError): ?>
                            <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                                <p><?= htmlspecialchars($saveError) ?></p>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/profile/" enctype="multipart/form-data">
                            <input type="hidden" name="update_action" value="1">

                            <div class="account__photo">
                                <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="" class="account__photo-image" id="profile-photo-preview">
                                <div class="account__photo-content">
                                    <label class="account__photo-upload" for="profile-photo-input">
                                        Загрузить аватар
                                        <input type="file" name="photo" class="account__photo-input" id="profile-photo-input" accept="image/png, image/jpeg">
                                    </label>
                                    <p>Изображение 300x300, формат jpg, png</p>
                                    <button type="submit" class="btn authorization__btn" id="profile-photo-save" style="display:none">Сохранить аватар</button>
                                </div>
                            </div>

                            <div class="account__personal profile-section is-view" data-section-id="personal">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Личные данные</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list account__grid">
                                    <input type="text" name="last_name" class="profile-editable" placeholder="Фамилия"
                                           value="<?= htmlspecialchars($arUser['LAST_NAME']   ?? '') ?>">
                                    <input type="email" placeholder="e-mail"
                                           value="<?= htmlspecialchars($arUser['EMAIL'] ?? '') ?>" readonly
                                           title="Email изменяется через раздел Безопасность">
                                    <input type="text" name="first_name" class="profile-editable" placeholder="Имя"
                                           value="<?= htmlspecialchars($arUser['NAME']        ?? '') ?>">
                                    <div class="po-date-field">
                                        <input type="text" name="dob" id="profile-dob" class="profile-editable" placeholder="Дата рождения (ДД.ММ.ГГГГ)"
                                               value="<?= htmlspecialchars($profileDobInputValue) ?>" inputmode="numeric" maxlength="10">
                                        <button type="button" class="po-date-field__btn" data-picker-target="profile-dob-picker" aria-label="Открыть календарь">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                                <path d="M8 3v4M16 3v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                        <input type="date" id="profile-dob-picker" class="po-date-field__native" tabindex="-1" aria-hidden="true">
                                    </div>
                                    <input type="text" name="second_name" class="profile-editable" placeholder="Отчество"
                                           value="<?= htmlspecialchars($arUser['SECOND_NAME'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="account__personal profile-section is-view" id="graduate-data" data-section-id="graduate">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Данные выпускника</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list account__personal-list--short account__grid">
                                    <input type="number" name="grad_year" class="profile-editable" placeholder="Год окончания" min="1900" max="2099"
                                           value="<?= htmlspecialchars($arUser['UF_GRADUATE_YEAR'] ?? '') ?>">
                                    <input type="text" name="grad_dept" class="profile-editable" placeholder="Выпускающая кафедра"
                                           value="<?= htmlspecialchars($arUser['UF_GRADUATE_DEPT'] ?? '') ?>">
                                    <input type="text" name="telegram" class="profile-editable" placeholder="Telegram"
                                           value="<?= htmlspecialchars($arUser['UF_TELEGRAM'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="account__personal profile-section is-view" id="diploma-data" data-section-id="diploma">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Сведения о дипломе</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list account__personal-list--short account__grid">
                                    <input type="text" name="diploma_series" class="profile-editable" placeholder="Серия бланка"
                                           value="<?= htmlspecialchars($arUser['UF_DIPLOMA_SERIES'] ?? '') ?>">
                                    <input type="text" name="diploma_number" class="profile-editable" placeholder="Номер бланка"
                                           value="<?= htmlspecialchars($arUser['UF_DIPLOMA_NUMBER'] ?? '') ?>">
                                    <div class="po-date-field">
                                        <input type="text" name="diploma_date" id="profile-diploma-date" class="profile-editable" placeholder="Дата выдачи (ДД.ММ.ГГГГ)"
                                               value="<?= htmlspecialchars($profileDiplomaDateInputValue) ?>" inputmode="numeric" maxlength="10">
                                        <button type="button" class="po-date-field__btn" data-picker-target="profile-diploma-picker" aria-label="Открыть календарь">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                                <path d="M8 3v4M16 3v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                        <input type="date" id="profile-diploma-picker" class="po-date-field__native" tabindex="-1" aria-hidden="true">
                                    </div>
                                </div>
                            </div>
                            <div class="account__personal profile-section is-view" data-section-id="achievements">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Достижения</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list">
                                    <textarea name="achievements" class="profile-editable" placeholder="Достижения (необязательно)" style="width:100%;box-sizing:border-box;resize:vertical;height:96px"><?= htmlspecialchars($arUser['PERSONAL_NOTES'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </form>

                        <!-- Блок тарифа -->
                        <div class="account__chapter" style="margin-top:40px">
                            <h3 class="account__subtitle">Ваш тариф</h3>
                            </div>
                            
                        <?php if ($currentType && $membershipStatus): ?>
                        <div class="account__rate <?= $currentType['class'] ?>">
                                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/rate-conus.png" alt="" class="account__rate-conus">
                                <div class="account__rate-info">
                                <span class="account__rate-status <?= $currentStatus['class'] ?? '' ?>">
                                    <?= htmlspecialchars($currentStatus['label'] ?? $membershipStatus) ?>
                                </span>
                                <?php if ($membershipExpires && $membershipStatus === 'active'): ?>
                                    <div class="account__rate-date">
                                    <span>Срок действия</span> до <?= htmlspecialchars($membershipExpires) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <h4 class="account__rate-plan">
                                <?= htmlspecialchars($currentType['label']) ?>
                                <?php if ($showVerificationBadge): ?>
                                <span class="profile-verification-badge" style="background:<?= htmlspecialchars($verificationBadgeColor) ?>">✓</span>
                                <?php endif; ?>
                            </h4>
                            <p class="account__rate-price"><?= htmlspecialchars($currentType['price']) ?></p>
                            <p class="account__rate-when">ежегодно</p>
                            <?php if ($membershipStatus === 'active'): ?>
                                <div class="account__rate-buttons account__grid">
                                    <?php if ($membershipType === 'basic'): ?>
                                    <a href="/join/" class="account__rate-btn btn">Продлить или изменить тип членства</a>
                                    <?php else: ?>
                                    <a href="/join/" class="account__rate-btn account__rate-btn--changes btn">Изменить тариф</a>
                                    <?php endif; ?>
                            </div>
                            <?php elseif ($membershipStatus === 'pending'): ?>
                            <p style="margin-top:12px;color:#666">
                                Заявка принята. После проверки модератором членство будет активировано.
                            </p>
                            <?php endif; ?>
                                </div>
                        <?php else: ?>
                        <div class="account__rate">
                            <p>У вас пока нет активного членства.</p>
                            <a href="/join/" class="btn" style="margin-top:12px">Вступить в общество</a>
                                </div>
                        <?php endif; ?>

                    </div>
                    <?php endif; ?>

                </div>
                </div>
            </div>
        </section>
	</main>

<script>
(function() {
    function dateToRu(val) {
        if (!val) return '';
        var p = val.split('-');
        if (p.length === 3 && p[0].length === 4) return p[2] + '.' + p[1] + '.' + p[0];
        return val;
    }
    function normalizeRuDate(raw) {
        var value = (raw || '').trim();
        if (!value) return '';
        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return dateToRu(value);
        var digits = value.replace(/[^\d]/g, '');
        if (digits.length === 8) {
            return digits.slice(0, 2) + '.' + digits.slice(2, 4) + '.' + digits.slice(4, 8);
        }
        return value;
    }
    function ruToIsoDate(raw) {
        var value = normalizeRuDate(raw);
        if (!/^\d{2}\.\d{2}\.\d{4}$/.test(value)) return '';
        var parts = value.split('.');
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }
    function setupDateField(textInputId, pickerInputId) {
        var textInput = document.getElementById(textInputId);
        var pickerInput = document.getElementById(pickerInputId);
        if (!textInput || !pickerInput) return;
        textInput.addEventListener('input', function() {
            var digits = this.value.replace(/[^\d]/g, '').slice(0, 8);
            if (digits.length >= 5) this.value = digits.slice(0, 2) + '.' + digits.slice(2, 4) + '.' + digits.slice(4);
            else if (digits.length >= 3) this.value = digits.slice(0, 2) + '.' + digits.slice(2);
            else this.value = digits;
            var iso = ruToIsoDate(this.value);
            if (iso) pickerInput.value = iso;
        });
        textInput.addEventListener('blur', function() {
            this.value = normalizeRuDate(this.value);
            var iso = ruToIsoDate(this.value);
            if (iso) pickerInput.value = iso;
        });
        pickerInput.addEventListener('change', function() {
            textInput.value = dateToRu(this.value);
        });
        var isoInitial = ruToIsoDate(textInput.value);
        if (isoInitial) pickerInput.value = isoInitial;
    }
    setupDateField('profile-dob', 'profile-dob-picker');
    setupDateField('profile-diploma-date', 'profile-diploma-picker');
    document.querySelectorAll('[data-picker-target]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var picker = document.getElementById(this.getAttribute('data-picker-target'));
            if (!picker) return;
            if (typeof picker.showPicker === 'function') picker.showPicker();
            else { picker.focus(); picker.click(); }
        });
    });

    var profileForm = document.querySelector('form[action="/profile/"]');
    if (!profileForm) return;

    var photoInput = document.getElementById('profile-photo-input');
    var photoPreview = document.getElementById('profile-photo-preview');
    var photoSaveBtn = document.getElementById('profile-photo-save');
    if (photoInput && photoPreview && photoSaveBtn) {
        photoInput.addEventListener('change', function() {
            var file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) {
                photoSaveBtn.style.display = 'none';
                return;
            }
            var allowed = ['image/jpeg', 'image/png'];
            if (allowed.indexOf(file.type) === -1) {
                alert('Допустимы только изображения JPG и PNG.');
                this.value = '';
                photoSaveBtn.style.display = 'none';
                return;
            }
            var reader = new FileReader();
            reader.onload = function(event) {
                photoPreview.src = event.target.result;
            };
            reader.readAsDataURL(file);
            photoSaveBtn.style.display = 'inline-block';
        });
    }

    function setSectionMode(section, isEdit) {
        section.classList.toggle('is-view', !isEdit);
        section.classList.toggle('is-edit', isEdit);
        var controls = section.querySelectorAll('.profile-editable');
        controls.forEach(function(control) {
            if (isEdit) {
                control.removeAttribute('readonly');
            } else {
                control.setAttribute('readonly', 'readonly');
            }
        });
        var button = section.querySelector('[data-toggle-edit]');
        if (button) {
            button.textContent = isEdit ? 'Сохранить' : 'Редактировать';
        }
    }

    var sections = Array.prototype.slice.call(profileForm.querySelectorAll('.profile-section'));
    sections.forEach(function(section) {
        setSectionMode(section, false);
        var button = section.querySelector('[data-toggle-edit]');
        if (!button) return;
        button.addEventListener('click', function() {
            if (section.classList.contains('is-view')) {
                sections.forEach(function(other) {
                    if (other !== section) setSectionMode(other, false);
                });
                setSectionMode(section, true);
            } else {
                profileForm.submit();
            }
        });
    });

    // Activities: "Показать все" для пожертвований
    var donationsToggle = document.getElementById('activities-donations-toggle');
    if (donationsToggle) {
        var donationsExpanded = false;
        donationsToggle.addEventListener('click', function() {
            var grid = document.getElementById('activities-donations-grid');
            if (!grid) return;

            donationsExpanded = !donationsExpanded;

            var allCards = grid.querySelectorAll('.activities__donation-card');
            allCards.forEach(function(el, idx) {
                el.style.display = (donationsExpanded || idx < 4) ? '' : 'none';
            });

            if (donationsExpanded) {
                this.innerHTML = 'Свернуть <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 19V5M5 12l7 7 7-7"/></svg>';
            } else {
                this.innerHTML = 'Показать все <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12l7-7 7 7"/></svg>';
            }
        });
    }
})();
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
