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
    $telegram    = trim($_POST['telegram']     ?? '');
    $isGraduate  = ($_POST['is_graduate'] ?? '') === 'yes';
    $gradYear    = (int)($_POST['grad_year']   ?? 0);
    $gradDept    = trim($_POST['grad_dept']    ?? '');
    $diplomaSeries = trim($_POST['diploma_series'] ?? '');
    $diplomaNumber = trim($_POST['diploma_number'] ?? '');
    $diplomaDate   = trim($_POST['diploma_date']   ?? '');

    $oUser  = new CUser();
    $result = $oUser->Update($userId, [
        'NAME'        => $firstName,
        'LAST_NAME'   => $lastName,
        'SECOND_NAME' => $secondName,
        'UF_TELEGRAM'       => $telegram,
        'UF_GRADUATE_YEAR'  => $isGraduate ? ($gradYear ?: '') : '',
        'UF_GRADUATE_DEPT'  => $isGraduate ? $gradDept : '',
        'UF_DIPLOMA_SERIES' => $diplomaSeries,
        'UF_DIPLOMA_NUMBER' => $diplomaNumber,
        'UF_DIPLOMA_DATE'   => $diplomaDate,
    ]);

    if ($result) {
        LocalRedirect('/profile/?saved=1');
    } else {
        $saveError = $oUser->LAST_ERROR ?: 'Ошибка сохранения';
        // Обновляем arUser свежими данными из POST
        $arUser['NAME']        = $firstName;
        $arUser['LAST_NAME']   = $lastName;
        $arUser['SECOND_NAME'] = $secondName;
    }
}

// Вспомогательные переменные
$membershipType   = $arUser['UF_MEMBERSHIP_TYPE']   ?? '';
$membershipStatus = $arUser['UF_MEMBERSHIP_STATUS'] ?? '';
$membershipExpires = $arUser['UF_MEMBERSHIP_EXPIRES'] ?? '';
$isGrad           = !empty($arUser['UF_GRADUATE_YEAR']);
$_ug      = $USER->GetUserGroupArray();
$isMember = defined('PO_MEMBER_BASIC_ID') && (
    in_array(PO_MEMBER_BASIC_ID,   $_ug) ||
    in_array(PO_MEMBER_PREMIUM_ID, $_ug) ||
    in_array(PO_PARTNER_ID,        $_ug)
);

$typeLabels = [
    'basic'   => ['label' => 'Базовое',         'price' => '5 000 Р',          'class' => ''],
    'premium' => ['label' => 'Профессиональное', 'price' => '50 000 Р',         'class' => 'account__rate--proff'],
    'partner' => ['label' => 'Партнёрское',      'price' => 'Инд. условия',     'class' => 'account__rate--proff'],
    'honorary'=> ['label' => 'Почётное',         'price' => 'Безвозмездно',     'class' => ''],
];
$statusLabels = [
    'pending'  => ['label' => 'На рассмотрении', 'class' => 'account__rate-status--pending'],
    'active'   => ['label' => 'Активный',         'class' => ''],
    'rejected' => ['label' => 'Отклонено',        'class' => 'account__rate-status--error'],
];
$currentType   = $typeLabels[$membershipType]   ?? null;
$currentStatus = $statusLabels[$membershipStatus] ?? null;
?>

<main>
    <section class="account">
        <div class="container">
            <div class="account__wrapper">
                <div class="account__sidebar">
                    <div class="account__menu">
                        <?php $tab = $_GET['tab'] ?? 'profile'; ?>
                        <a href="/profile/" class="account__menu-item <?= $tab === 'profile' ? 'account__menu-item--active' : '' ?>">Мой профиль</a>
                        <a href="/profile/security/" class="account__menu-item">Безопасность</a>
                        <a href="/profile/?tab=membership" class="account__menu-item <?= $tab === 'membership' ? 'account__menu-item--active' : '' ?>">Моё членство</a>
                        <a href="/profile/?tab=activities" class="account__menu-item <?= $tab === 'activities' ? 'account__menu-item--active' : '' ?>">Мои активности</a>
                        <a href="/profile/?tab=applications" class="account__menu-item <?= $tab === 'applications' ? 'account__menu-item--active' : '' ?>">Мои заявки</a>
                    </div>
                </div>

                <div class="account__main">

                    <?php if ($tab === 'membership'): ?>
                    <!-- Моё членство -->
                    <div class="account__block">
                        <h2 class="account__title">Моё членство</h2>
                        <?php if ($currentType && $membershipStatus): ?>
                        <div class="account__rate <?= $currentType['class'] ?>" style="margin-top:24px">
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
                            <h4 class="account__rate-plan"><?= htmlspecialchars($currentType['label']) ?></h4>
                            <p class="account__rate-price"><?= htmlspecialchars($currentType['price']) ?></p>
                            <p class="account__rate-when">ежегодно</p>
                            <?php if ($membershipStatus === 'active'): ?>
                            <div class="account__rate-buttons account__grid">
                                <button class="account__rate-btn btn" disabled title="Оплата будет доступна после подключения Газпромбанк-эквайринга">Продлить</button>
                                <a href="/join/" class="account__rate-btn account__rate-btn--changes btn">Изменить тариф</a>
                            </div>
                            <?php elseif ($membershipStatus === 'pending'): ?>
                            <div style="margin-top:16px;padding:12px 16px;background:#fff9e6;border-radius:8px;border-left:3px solid #f0a500">
                                <strong>Заявка принята.</strong> После проверки модератором и подтверждения оплаты членство будет активировано.
                            </div>
                            <?php elseif ($membershipStatus === 'in_review'): ?>
                            <div style="margin-top:16px;padding:12px 16px;background:#e8f4fd;border-radius:8px;border-left:3px solid #2980b9">
                                <strong>На рассмотрении.</strong> Ваша заявка передана модератору. Ожидайте ответа по email.
                            </div>
                            <?php elseif ($membershipStatus === 'rejected'): ?>
                            <div style="margin-top:16px;padding:12px 16px;background:#fdecea;border-radius:8px;border-left:3px solid #e74c3c">
                                <strong>Заявка отклонена.</strong> Обратитесь к администратору или подайте новую заявку.
                                <br><a href="/join/" class="btn" style="margin-top:10px;display:inline-block">Подать новую заявку</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div style="margin-top:24px;padding:32px;text-align:center;background:#f8f8f8;border-radius:12px">
                            <p style="font-size:16px;color:#555;margin-bottom:16px">У вас пока нет активного членства в обществе.</p>
                            <a href="/join/" class="btn">Вступить в общество</a>
                        </div>
                        <?php endif; ?>
                        <!-- Привилегии текущего тарифа -->
                        <?php if ($isMember && $membershipType): ?>
                        <div class="account__chapter" style="margin-top:32px">
                            <h3 class="account__subtitle">Привилегии вашего тарифа</h3>
                        </div>
                        <?php
                        $privileges = [
                            'basic' => [
                                'Размещение резюме на карьерной платформе',
                                'Доступ в закрытый карьерный канал с вакансиями',
                                'Участие в активностях и мероприятиях общества',
                                'Доступ к витрине компетенций партнёров',
                            ],
                            'premium' => [
                                'Все привилегии Базового тарифа',
                                'Участие в закрытом чате членов уровня «Бизнес»',
                                'Размещение информации о компании на площадках общества',
                                'Доступ к базе резюме выпускников',
                            ],
                            'partner' => [
                                'Все привилегии Профессионального тарифа',
                                'Участие в закрытых мероприятиях',
                                'Право стать членом правления',
                                'Индивидуальное сопровождение',
                            ],
                            'honorary' => [
                                'Все привилегии',
                                'Почётный статус',
                                'Особые условия взаимодействия',
                            ],
                        ];
                        $privList = $privileges[$membershipType] ?? [];
                        ?>
                        <ul style="margin-top:12px;padding-left:20px">
                            <?php foreach ($privList as $priv): ?>
                            <li style="margin-bottom:8px;color:#444"><?= htmlspecialchars($priv) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>

                    <?php elseif ($tab === 'activities'): ?>
                    <!-- Мои активности -->
                    <div class="account__block">
                        <h2 class="account__title">Мои активности</h2>

                        <!-- Telegram-чаты по уровню членства -->
                        <?php
                        $_userGroupsProfile = $USER->GetUserGroupArray();
                        $tgChats = [
                            PO_MEMBER_BASIC_ID   => [
                                'title' => 'Общий чат Политехнического общества',
                                'desc'  => 'Обсуждения, новости, анонсы для всех членов общества',
                                'url'   => '#',
                                'icon'  => '💬',
                            ],
                            PO_MEMBER_PREMIUM_ID => [
                                'title' => 'VIP-канал для почётных членов',
                                'desc'  => 'Закрытый канал с эксклюзивными материалами и нетворкингом',
                                'url'   => '#',
                                'icon'  => '⭐',
                            ],
                            PO_PARTNER_ID        => [
                                'title' => 'Канал для партнёров',
                                'desc'  => 'Совместные проекты, вакансии и партнёрские предложения',
                                'url'   => '#',
                                'icon'  => '🤝',
                            ],
                        ];
                        $myTgChats = [];
                        foreach ($tgChats as $groupId => $chatInfo) {
                            if (in_array($groupId, $_userGroupsProfile)) {
                                $myTgChats[] = $chatInfo;
                            }
                        }
                        ?>
                        <?php if (!empty($myTgChats)): ?>
                        <div style="margin-bottom:32px">
                            <h3 class="account__subtitle" style="margin-bottom:16px">Мои Telegram-чаты</h3>
                            <div style="display:grid;gap:12px">
                                <?php foreach ($myTgChats as $chat): ?>
                                <div style="display:flex;align-items:center;gap:16px;background:#f0f8ff;border-radius:10px;padding:16px 20px;border-left:3px solid #2980b9">
                                    <span style="font-size:28px"><?= $chat['icon'] ?></span>
                                    <div style="flex:1">
                                        <div style="font-weight:600;font-size:15px;margin-bottom:2px"><?= htmlspecialchars($chat['title']) ?></div>
                                        <div style="color:#666;font-size:13px"><?= htmlspecialchars($chat['desc']) ?></div>
                                    </div>
                                    <?php if ($chat['url'] !== '#'): ?>
                                    <a href="<?= htmlspecialchars($chat['url']) ?>" target="_blank" rel="noopener"
                                       class="btn" style="white-space:nowrap;font-size:13px;padding:8px 16px">
                                        Открыть
                                    </a>
                                    <?php else: ?>
                                    <span style="color:#aaa;font-size:12px;white-space:nowrap">Скоро</span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php
                        $arEvents   = [];
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
                        $actStatusLabels = [
                            'new'       => ['label' => 'Новая',        'color' => '#888'],
                            'in_review' => ['label' => 'На рассмотрении','color' => '#2980b9'],
                            'approved'  => ['label' => 'Одобрено',     'color' => '#27ae60'],
                            'rejected'  => ['label' => 'Отклонено',    'color' => '#e74c3c'],
                        ];
                        ?>
                        <!-- Секция событий -->
                        <div class="account__chapter" style="margin-top:24px">
                            <h3 class="account__subtitle">Мои события</h3>
                        </div>
                        <?php if (empty($arEvents)): ?>
                        <p style="color:#888;margin-top:12px">Вы пока не регистрировались на события.</p>
                        <a href="/news/?type=events" style="display:inline-block;margin-top:8px;color:#1a73e8">Посмотреть ближайшие события →</a>
                        <?php else: ?>
                        <table style="width:100%;border-collapse:collapse;margin-top:12px;font-size:14px">
                            <thead>
                                <tr style="background:#f5f5f5">
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Событие</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Дата заявки</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($arEvents as $ev):
                                $evData   = json_decode($ev['UF_DATA'] ?? '{}', true) ?: [];
                                $evTitle  = htmlspecialchars($evData['event_name'] ?? ('Событие #' . ($ev['UF_ELEMENT_ID'] ?? $ev['ID'])));
                                $evDate   = !empty($ev['UF_DATE_CREATE']) ? $ev['UF_DATE_CREATE']->format('d.m.Y H:i') : '';
                                $evSt     = $actStatusLabels[$ev['UF_STATUS'] ?? 'new'] ?? ['label' => $ev['UF_STATUS'], 'color' => '#888'];
                            ?>
                            <tr style="border-bottom:1px solid #f0f0f0">
                                <td style="padding:10px"><?= $evTitle ?></td>
                                <td style="padding:10px"><?= htmlspecialchars($evDate) ?></td>
                                <td style="padding:10px;color:<?= $evSt['color'] ?>;font-weight:600"><?= htmlspecialchars($evSt['label']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>

                        <!-- Секция пожертвований/поддержки проектов -->
                        <div class="account__chapter" style="margin-top:32px">
                            <h3 class="account__subtitle">История поддержки проектов</h3>
                        </div>
                        <?php if (empty($arDonations)): ?>
                        <p style="color:#888;margin-top:12px">Вы пока не поддерживали проекты.</p>
                        <a href="/projects/" style="display:inline-block;margin-top:8px;color:#1a73e8">Посмотреть проекты →</a>
                        <?php else: ?>
                        <table style="width:100%;border-collapse:collapse;margin-top:12px;font-size:14px">
                            <thead>
                                <tr style="background:#f5f5f5">
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Проект</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Сумма</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Дата</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($arDonations as $don):
                                $donData  = json_decode($don['UF_DATA'] ?? '{}', true) ?: [];
                                $donTitle = htmlspecialchars($donData['project_name'] ?? ('Проект #' . ($don['UF_ELEMENT_ID'] ?? $don['ID'])));
                                $donSum   = htmlspecialchars($donData['amount'] ?? '—');
                                $donDate  = !empty($don['UF_DATE_CREATE']) ? $don['UF_DATE_CREATE']->format('d.m.Y H:i') : '';
                                $donSt    = $actStatusLabels[$don['UF_STATUS'] ?? 'new'] ?? ['label' => $don['UF_STATUS'], 'color' => '#888'];
                            ?>
                            <tr style="border-bottom:1px solid #f0f0f0">
                                <td style="padding:10px"><?= $donTitle ?></td>
                                <td style="padding:10px"><?= $donSum ?></td>
                                <td style="padding:10px"><?= htmlspecialchars($donDate) ?></td>
                                <td style="padding:10px;color:<?= $donSt['color'] ?>;font-weight:600"><?= htmlspecialchars($donSt['label']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>

                    <?php elseif ($tab === 'applications'): ?>
                    <!-- Мои заявки -->
                    <div class="account__block">
                        <h2 class="account__title">Мои заявки</h2>
                        <?php
                        $typeLabelsHL = [
                            'event_registration' => 'Регистрация на событие',
                            'reference_visit'    => 'Участие в референс-визите',
                            'reference_org'      => 'Организация референс-визита',
                            'competency_request' => 'Запрос в витрине компетенций',
                            'partnership'        => 'Индустриальное партнёрство',
                            'project_support'    => 'Поддержка проекта',
                        ];
                        $statusLabelsHL = [
                            'new'        => 'Новая',
                            'in_progress'=> 'В обработке',
                            'approved'   => 'Одобрено',
                            'rejected'   => 'Отклонено',
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
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Тип</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Дата</th>
                                    <th style="padding:10px;text-align:left;border-bottom:1px solid #eee">Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($arApplications as $app):
                                $appType   = $typeLabelsHL[$app['UF_TYPE']] ?? htmlspecialchars($app['UF_TYPE']);
                                $appDate   = !empty($app['UF_DATE_CREATE']) ? $app['UF_DATE_CREATE']->format('d.m.Y H:i') : '';
                                $appStatus = $statusLabelsHL[$app['UF_STATUS']] ?? htmlspecialchars($app['UF_STATUS'] ?? 'новая');
                            ?>
                            <tr style="border-bottom:1px solid #f0f0f0">
                                <td style="padding:10px"><?= $appType ?></td>
                                <td style="padding:10px"><?= htmlspecialchars($appDate) ?></td>
                                <td style="padding:10px"><?= $appStatus ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
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
                                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/avatar.png" alt="" class="account__photo-image">
                                <div class="account__photo-content">
                                    <label class="account__photo-upload">
                                        Загрузить аватар
                                        <input type="file" name="photo" class="account__photo-input" accept="image/png, image/jpeg">
                                    </label>
                                    <p>Изображение 300x300, формат jpg, png</p>
                                </div>
                            </div>

                            <div class="account__personal">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Личные данные</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list account__grid">
                                    <input type="text" name="last_name"   placeholder="Фамилия"
                                           value="<?= htmlspecialchars($arUser['LAST_NAME']   ?? '') ?>">
                                    <input type="text" name="first_name"  placeholder="Имя"
                                           value="<?= htmlspecialchars($arUser['NAME']        ?? '') ?>">
                                    <input type="text" name="second_name" placeholder="Отчество"
                                           value="<?= htmlspecialchars($arUser['SECOND_NAME'] ?? '') ?>">
                                    <input type="email" placeholder="Электропочта"
                                           value="<?= htmlspecialchars($arUser['EMAIL'] ?? '') ?>" readonly
                                           title="Email изменяется через раздел Безопасность">
                                </div>
                            </div>

                            <div class="account__graduate">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Выпускник МГТУ?</h3>
                                </div>
                                <div class="account__graduate-choice">
                                    <label class="account__graduate-item">
                                        <input type="radio" name="is_graduate" value="yes" class="account__graduate-input" id="grad-yes"
                                               <?= $isGrad ? 'checked' : '' ?>>
                                        <span class="account__graduate-box"></span>Да
                                    </label>
                                    <label class="account__graduate-item">
                                        <input type="radio" name="is_graduate" value="no"  class="account__graduate-input" id="grad-no"
                                               <?= !$isGrad ? 'checked' : '' ?>>
                                        <span class="account__graduate-box"></span>Нет
                                    </label>
                                </div>
                            </div>

                            <div class="account__personal" id="graduate-data" style="<?= !$isGrad ? 'display:none' : '' ?>">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Данные выпускника</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list account__personal-list--short account__grid">
                                    <input type="number" name="grad_year" placeholder="Год окончания" min="1900" max="2099"
                                           value="<?= htmlspecialchars($arUser['UF_GRADUATE_YEAR'] ?? '') ?>">
                                    <input type="text" name="grad_dept" placeholder="Выпускающая кафедра"
                                           value="<?= htmlspecialchars($arUser['UF_GRADUATE_DEPT'] ?? '') ?>">
                                    <input type="text" name="telegram" placeholder="Telegram"
                                           value="<?= htmlspecialchars($arUser['UF_TELEGRAM'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="account__personal" id="diploma-data" style="<?= !$isGrad ? 'display:none' : '' ?>">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Сведения о дипломе</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list account__personal-list--short account__grid">
                                    <input type="text" name="diploma_series" placeholder="Серия бланка"
                                           value="<?= htmlspecialchars($arUser['UF_DIPLOMA_SERIES'] ?? '') ?>">
                                    <input type="text" name="diploma_number" placeholder="Номер бланка"
                                           value="<?= htmlspecialchars($arUser['UF_DIPLOMA_NUMBER'] ?? '') ?>">
                                    <input type="text" name="diploma_date"   placeholder="Дата выдачи"
                                           value="<?= htmlspecialchars($arUser['UF_DIPLOMA_DATE']   ?? '') ?>">
                                </div>
                            </div>

                            <button type="submit" class="btn authorization__btn" style="margin-top:24px">Сохранить</button>
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
                            <h4 class="account__rate-plan"><?= htmlspecialchars($currentType['label']) ?></h4>
                            <p class="account__rate-price"><?= htmlspecialchars($currentType['price']) ?></p>
                            <p class="account__rate-when">ежегодно</p>
                            <?php if ($membershipStatus === 'active'): ?>
                            <div class="account__rate-buttons account__grid">
                                <button class="account__rate-btn btn">Продлить</button>
                                <a href="/join/" class="account__rate-btn account__rate-btn--changes btn">Изменить тариф</a>
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

                        <!-- Привязка соцсетей (статичная заготовка) -->
                        <div class="account__log" style="margin-top:40px">
                            <div class="account__chapter">
                                <h3 class="account__subtitle">Привязка аккаунта для быстрого входа</h3>
                            </div>
                            <div class="account__log-wrapper">
                                <button class="account__log-btn" disabled>
                                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/yandex-icon.png" alt="">
                                    Войти через Яндекс
                                </button>
                                <button class="account__log-btn" disabled>
                                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/gos-icon.png" alt="">
                                    Войти через Госуслуги
                                </button>
                                <button class="account__log-btn" disabled>
                                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/vk-icon.png" alt="">
                                    Войти через Вконтакте
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.querySelectorAll('[name="is_graduate"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var show = this.value === 'yes';
        ['graduate-data','diploma-data'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.style.display = show ? '' : 'none';
        });
    });
});
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
