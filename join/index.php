<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Вступить в общество");
$APPLICATION->SetPageProperty('description', 'Вступите в Политехническое общество выпускников МГТУ им. Н.Э. Баумана. Выберите тип членства: Базовое, Профессиональное, Партнёрское или Почётное.');

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$_ug      = $USER->IsAuthorized() ? $USER->GetUserGroupArray() : [];
$isMember = defined('PO_MEMBER_BASIC_ID') && (
    in_array(PO_MEMBER_BASIC_ID,   $_ug) ||
    in_array(PO_MEMBER_PREMIUM_ID, $_ug) ||
    in_array(PO_PARTNER_ID,        $_ug)
);
$isAuthorized = $USER->IsAuthorized();

$errors   = [];
$joinDone = false;
$joinType = 'basic';

$moderationTypes = ['premium', 'partner', 'honorary'];

function po_saveMembershipApplication(int $userId, string $type, array $data): void
{
    if (!defined('HL_APPLICATIONS_ID') || HL_APPLICATIONS_ID <= 0) return;
    $hlEntityData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
    if (!$hlEntityData) return;
    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntityData)->getDataClass();
    $hlClass::add([
        'UF_USER_ID'     => $userId,
        'UF_TYPE'        => 'membership',
        'UF_STATUS'      => in_array($type, ['premium', 'partner', 'honorary']) ? 'in_review' : 'new',
        'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
        'UF_DATA'        => json_encode(array_merge($data, ['membership_type' => $type]), JSON_UNESCAPED_UNICODE),
    ]);
}

// — Обработчик формы вступления —
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['join_action'])) {
    $lastName       = trim($_POST['last_name']   ?? '');
    $firstName      = trim($_POST['first_name']  ?? '');
    $secondName     = trim($_POST['second_name'] ?? '');
    $email          = trim($_POST['email']       ?? '');
    $password       = $_POST['password'] ?? '';
    $isGraduate     = ($_POST['is_graduate'] ?? '') === 'yes';
    $gradYear       = (int)($_POST['grad_year']  ?? 0);
    $gradDept       = trim($_POST['grad_dept']   ?? '');
    $telegram       = trim($_POST['telegram']    ?? '');
    $diplomaSeries  = trim($_POST['diploma_series'] ?? '');
    $diplomaNumber  = trim($_POST['diploma_number'] ?? '');
    $diplomaDate    = trim($_POST['diploma_date']   ?? '');
    $membershipType = trim($_POST['membership_type'] ?? 'basic');
    if (!in_array($membershipType, ['basic', 'premium', 'partner', 'honorary'])) {
        $membershipType = 'basic';
    }
    $joinType     = $membershipType;
    $agreeCharter = ($_POST['agree_charter'] ?? '') === 'yes';
    $agreePd      = ($_POST['agree_pd']      ?? '') === 'yes';

    if (!$agreeCharter || !$agreePd) {
        $errors[] = 'Необходимо согласие с Уставом и политикой ПДн';
    }

    if (!$isAuthorized) {
        if (!$email)               $errors[] = 'Введите email';
        if (strlen($password) < 6) $errors[] = 'Пароль должен содержать не менее 6 символов';
        if (!$lastName)            $errors[] = 'Введите фамилию';
        if (!$firstName)           $errors[] = 'Введите имя';

        if (empty($errors)) {
            $oUser  = new CUser();
            $userId = $oUser->Add([
                'LOGIN'            => $email,
                'EMAIL'            => $email,
                'PASSWORD'         => $password,
                'CONFIRM_PASSWORD' => $password,
                'NAME'             => $firstName,
                'LAST_NAME'        => $lastName,
                'SECOND_NAME'      => $secondName,
                'ACTIVE'           => 'Y',
                'GROUP_ID'         => [PO_REGISTERED_ID],
                'UF_MEMBERSHIP_STATUS' => 'pending',
                'UF_MEMBERSHIP_TYPE'   => $membershipType,
                'UF_GRADUATE_YEAR'     => $isGraduate ? $gradYear : '',
                'UF_GRADUATE_DEPT'     => $isGraduate ? $gradDept : '',
                'UF_TELEGRAM'          => $telegram,
                'UF_DIPLOMA_SERIES'    => $diplomaSeries,
                'UF_DIPLOMA_NUMBER'    => $diplomaNumber,
                'UF_DIPLOMA_DATE'      => $diplomaDate,
            ]);

            if ($userId) {
                $USER->Login($email, $password, 'N');
                if ($hlOk) {
                    po_saveMembershipApplication((int)$userId, $membershipType, [
                        'first_name' => $firstName, 'last_name' => $lastName,
                        'email'      => $email,
                    ]);
                }
                if (in_array($membershipType, $moderationTypes)) {
                    po_sendAdminEmail('membership', [
                        'membership_type' => $membershipType,
                        'first_name' => $firstName, 'last_name' => $lastName,
                        'email' => $email,
                    ]);
                }
                $joinDone = true;
                po_logAction('form_submit', 'application', 0, 'D1 vstuplenie v obschestvo');
            } else {
                $errors[] = $oUser->LAST_ERROR ?: 'Ошибка при создании аккаунта';
            }
        }
    } else {
        // Авторизованный не-член — обновляем UF_ поля
        $userId  = (int)$USER->GetID();
        $dbUser  = CUser::GetByID($userId);
        $arCurr  = $dbUser->Fetch() ?: [];
        $oUser   = new CUser();
        $result  = $oUser->Update($userId, [
            'UF_MEMBERSHIP_STATUS' => 'pending',
            'UF_MEMBERSHIP_TYPE'   => $membershipType,
            'UF_GRADUATE_YEAR'     => $isGraduate ? $gradYear : '',
            'UF_GRADUATE_DEPT'     => $isGraduate ? $gradDept : '',
            'UF_TELEGRAM'          => $telegram,
            'UF_DIPLOMA_SERIES'    => $diplomaSeries,
            'UF_DIPLOMA_NUMBER'    => $diplomaNumber,
            'UF_DIPLOMA_DATE'      => $diplomaDate,
        ]);

        if ($result) {
            if ($hlOk) {
                po_saveMembershipApplication($userId, $membershipType, [
                    'first_name' => $arCurr['NAME']      ?? '',
                    'last_name'  => $arCurr['LAST_NAME'] ?? '',
                    'email'      => $arCurr['EMAIL']     ?? '',
                ]);
            }
            if (in_array($membershipType, $moderationTypes)) {
                po_sendAdminEmail('membership', [
                    'membership_type' => $membershipType,
                    'first_name' => $arCurr['NAME']      ?? '',
                    'last_name'  => $arCurr['LAST_NAME'] ?? '',
                    'email'      => $arCurr['EMAIL']     ?? '',
                ]);
            }
            $joinDone = true;
            po_logAction('form_submit', 'application', 0, 'D1 update profile join');
        } else {
            $errors[] = $oUser->LAST_ERROR ?: 'Ошибка сохранения данных';
        }
    }
}

// Данные текущего пользователя для предзаполнения
$arCurrentUser = [];
if ($isAuthorized) {
    $dbUser = CUser::GetByID($USER->GetID());
    $arCurrentUser = $dbUser->Fetch() ?: [];
}

// — D7: Индустриальное партнёрство (юр. лицо) —
$d7Done  = false;
$d7Error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d7_action'])) {
    $d7Company  = trim($_POST['d7_company']  ?? '');
    $d7Contact  = trim($_POST['d7_contact']  ?? '');
    $d7Site     = trim($_POST['d7_site']     ?? '');
    $d7Email    = trim($_POST['d7_email']    ?? '');
    $d7Phone    = trim($_POST['d7_phone']    ?? '');
    $d7Count    = trim($_POST['d7_count']    ?? '');
    $d7AgreePd  = ($_POST['d7_agree_pd']     ?? '') === 'yes';

    if (!$d7Company || !$d7Contact || !$d7Email) {
        $d7Error = 'Заполните обязательные поля: Компания, ФИО, Email.';
    } elseif (!$d7AgreePd) {
        $d7Error = 'Необходимо согласие с политикой ПДн.';
    } else {
        $saved = false;
        if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
            $hlEntityData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
            if ($hlEntityData) {
                $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntityData)->getDataClass();
                $res = $hlClass::add([
                    'UF_USER_ID'     => $USER->IsAuthorized() ? (int)$USER->GetID() : 0,
                    'UF_TYPE'        => 'partnership',
                    'UF_STATUS'      => 'new',
                    'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                    'UF_DATA'        => json_encode([
                        'company'       => $d7Company,
                        'contact_name'  => $d7Contact,
                        'site'          => $d7Site,
                        'email'         => $d7Email,
                        'phone'         => $d7Phone,
                        'planned_count' => $d7Count,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
                $saved = $res->isSuccess();
                if (!$saved) $d7Error = 'Ошибка сохранения. Попробуйте позже.';
            }
        } else {
            $saved = true;
        }
        if ($saved) {
            $d7Done = true;
            po_logAction('form_submit', 'application', 0, 'D7 industrial partnership');
            po_sendAdminEmail('partnership', [
                'company'      => $d7Company,
                'contact_name' => $d7Contact,
                'email'        => $d7Email,
                'phone'        => $d7Phone,
                'site'         => $d7Site,
            ]);
        }
    }
}
?>

<main>
    <!-- Переключатель Физ. / Юр. лицо -->
    <section style="background:#f5f5f5;padding:24px 0;border-bottom:1px solid #e0e0e0">
        <div class="container">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <span style="font-weight:600;color:#333">Вступить как:</span>
                <button id="btn-fiz" onclick="po_switchJoinType('fiz')" class="btn"
                        style="padding:10px 24px">Физическое лицо</button>
                <button id="btn-ur"  onclick="po_switchJoinType('ur')"  class="btn btn-empty"
                        style="padding:10px 24px">Юридическое лицо (партнёрство)</button>
            </div>
        </div>
    </section>

    <!-- D7: Блок для юридических лиц -->
    <section id="join-ur-block" style="display:none">
        <div class="container" style="padding-top:40px;padding-bottom:40px">
            <div class="join__wrapper">
                <?php if ($d7Done): ?>
                <div style="text-align:center;padding:40px 0">
                    <div style="font-size:48px;margin-bottom:12px">🤝</div>
                    <h2 class="account__title main-title">Заявка на партнёрство отправлена!</h2>
                    <p style="margin-top:12px;color:#666;max-width:480px;margin-left:auto;margin-right:auto">
                        Мы свяжемся с вами в течение 5 рабочих дней для обсуждения условий партнёрства.
                    </p>
                    <a href="/" class="btn" style="margin-top:20px">На главную</a>
                </div>
                <?php else: ?>
                <h2 class="account__title main-title">Индустриальное партнёрство</h2>
                <p style="margin-bottom:24px;color:#666">
                    Для компаний, НИИ и организаций. После отправки заявки мы свяжемся с вами в течение 5 рабочих дней.
                </p>
                <?php if ($d7Error): ?>
                <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                    <p><?= htmlspecialchars($d7Error) ?></p>
                </div>
                <?php endif; ?>
                <form method="POST" action="/join/#join-ur-block">
                    <input type="hidden" name="d7_action" value="1">
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Данные компании</h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="text"  name="d7_company" placeholder="Название компании *" required
                                   value="<?= htmlspecialchars($_POST['d7_company'] ?? '') ?>">
                            <input type="url"   name="d7_site"    placeholder="Сайт компании"
                                   value="<?= htmlspecialchars($_POST['d7_site'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Контакты представителя</h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="text"  name="d7_contact" placeholder="ФИО представителя *" required
                                   value="<?= htmlspecialchars($_POST['d7_contact'] ?? '') ?>">
                            <input type="email" name="d7_email"   placeholder="Email *" required
                                   value="<?= htmlspecialchars($_POST['d7_email'] ?? ($arCurrentUser['EMAIL'] ?? '')) ?>">
                            <input type="tel"   name="d7_phone"   placeholder="Телефон"
                                   value="<?= htmlspecialchars($_POST['d7_phone'] ?? '') ?>">
                            <input type="number" name="d7_count"  placeholder="Планируемое кол-во представителей *" min="1" required
                                   value="<?= htmlspecialchars($_POST['d7_count'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <p class="join__politic-link">Согласен с <a href="#">политикой обработки ПДн</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="d7_agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="d7_agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">Отправить заявку на партнёрство</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Блок для физических лиц -->
    <section id="join-fiz-block">
    <section class="join">
        <div class="container">

            <?php if (!empty($errors)): ?>
                <div class="authorization__alert authorization__alert--error" style="margin-bottom:20px">
                    <?php foreach ($errors as $msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($joinDone): ?>
                <div class="join__wrapper" style="text-align:center;padding:60px 20px">
                    <?php if ($joinType === 'basic'): ?>
                        <div style="font-size:48px;margin-bottom:16px">✅</div>
                        <h2 class="account__title main-title" style="margin-bottom:12px">Заявка принята!</h2>
                        <p style="font-size:16px;color:#555;max-width:480px;margin:0 auto 24px">
                            Ваша заявка на <strong>Базовое членство</strong> зарегистрирована.
                            В течение 1–2 рабочих дней на ваш email придёт письмо с реквизитами для оплаты взноса (5 000 ₽/год).
                        </p>
                        <a href="/profile/" class="btn">Перейти в личный кабинет</a>
                    <?php else:
                        $typeLabelsD = [
                            'premium' => 'Профессиональное',
                            'partner' => 'Партнёрское',
                            'honorary'=> 'Почётное',
                        ];
                        $tLabel = $typeLabelsD[$joinType] ?? $joinType;
                    ?>
                        <div style="font-size:48px;margin-bottom:16px">🤝</div>
                        <h2 class="account__title main-title" style="margin-bottom:12px">Заявка передана на рассмотрение</h2>
                        <p style="font-size:16px;color:#555;max-width:480px;margin:0 auto 24px">
                            Ваша заявка на <strong><?= htmlspecialchars($tLabel) ?> членство</strong> принята и передана модераторам.
                            Мы свяжемся с вами по email в течение 3–5 рабочих дней.
                        </p>
                        <a href="/profile/" class="btn">Перейти в личный кабинет</a>
                    <?php endif; ?>
                </div>

            <?php elseif ($isMember): ?>
            <!-- Сценарий 3: уже член общества -->
            <div class="join__wrapper">
                <h2 class="account__title main-title">Вы уже член общества</h2>
                <div class="account__chapter">
                    <h3 class="account__subtitle">Ваш тариф</h3>
                </div>
                <div class="account__rate account__rate--proff">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/my_profile/rate-conus.png" alt="" class="account__rate-conus">
                    <h4 class="account__rate-plan">
                        <?php
                        $typeLabels = [
                            'basic'   => 'Базовое',
                            'premium' => 'Профессиональное',
                            'partner' => 'Партнёрское',
                            'honorary'=> 'Почётное',
                        ];
                        echo htmlspecialchars($typeLabels[$arCurrentUser['UF_MEMBERSHIP_TYPE'] ?? ''] ?? 'Активное');
                        ?>
                    </h4>
                    <div class="account__rate-buttons">
                        <a href="/profile/" class="account__rate-btn btn">Перейти в личный кабинет</a>
                    </div>
                </div>
            </div>

            <?php elseif ($isAuthorized): ?>
            <!-- Сценарий 2: авторизован, не член — сокращённая форма -->
            <div class="join__wrapper">
                <h2 class="account__title main-title">Вступить в общество</h2>
                <p style="margin-bottom:16px;color:#666">
                    Ваши данные предзаполнены из профиля. Выберите тариф и отправьте заявку.
                </p>
                <form method="POST" action="/join/">
                    <input type="hidden" name="join_action" value="1">
                    <input type="hidden" name="membership_type" value="basic" id="membership_type">
                    <div class="account__chapter">
                        <h3 class="account__subtitle">Личные данные</h3>
                    </div>
                    <div class="join__grid">
                        <input type="text" name="last_name"   placeholder="Фамилия"
                               value="<?= htmlspecialchars($arCurrentUser['LAST_NAME'] ?? '') ?>">
                        <input type="text" name="first_name"  placeholder="Имя"
                               value="<?= htmlspecialchars($arCurrentUser['NAME'] ?? '') ?>">
                        <input type="text" name="second_name" placeholder="Отчество"
                               value="<?= htmlspecialchars($arCurrentUser['SECOND_NAME'] ?? '') ?>">
                        <input type="text" name="telegram"    placeholder="Telegram"
                               value="<?= htmlspecialchars($arCurrentUser['UF_TELEGRAM'] ?? '') ?>">
                    </div>
                    <div class="account__graduate">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Выпускник МГТУ?</h3>
                        </div>
                        <div class="account__graduate-choice">
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="yes" class="account__graduate-input"
                                       <?= !empty($arCurrentUser['UF_GRADUATE_YEAR']) ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>Да
                            </label>
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="no" class="account__graduate-input"
                                       <?= empty($arCurrentUser['UF_GRADUATE_YEAR']) ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>Нет
                            </label>
                        </div>
                    </div>
                    <!-- Выбор тарифа (авторизованный пользователь) -->
                    <div class="account__chapter" style="margin-top:24px">
                        <h3 class="account__subtitle">Выберите тариф</h3>
                    </div>
                    <div class="membership-slider swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide membership-slider__card">
                                <h3 class="membership-slider__title">Базовое</h3>
                                <p class="membership-slider__name">5 000 Р</p>
                                <p class="membership-slider__time">ежегодно</p>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">Возможность размещения резюме на карьерной платформе;</li>
                                    <li class="membership-slider__item">Доступ в закрытый карьерный канал с вакансиями;</li>
                                    <li class="membership-slider__item">Участие в активностях и мероприятиях общества;</li>
                                    <li class="membership-slider__item">Доступ к витрине компетенций партнёров.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan btn--active" data-plan="basic">Выбрать</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
                                <h3 class="membership-slider__title">Профессиональное</h3>
                                <p class="membership-slider__name">50 000 Р</p>
                                <p class="membership-slider__time">ежегодно</p>
                                <button class="membership-slider__advantages">+ Возможности Базового</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">Участие в закрытом чате членов общества уровня «Бизнес»;</li>
                                    <li class="membership-slider__item">Размещение информации о компании на площадках общества;</li>
                                    <li class="membership-slider__item">Доступ к базе резюме выпускников.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="premium">Выбрать</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--honorary">
                                <h3 class="membership-slider__title">Партнёрское</h3>
                                <p class="membership-slider__name membership-slider__name--small">Индивидуальные условия</p>
                                <p class="membership-slider__time">обсуждается индивидуально</p>
                                <button class="membership-slider__advantages">+ Возможности профессионального</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">Участие в закрытых мероприятиях;</li>
                                    <li class="membership-slider__item">Право стать членом правления.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="partner">Выбрать</button>
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <div class="join__politic">
                        <div class="join__politic-question">
                            <p class="join__politic-link">
                                Ознакомлен(а) и согласен(а) с <a href="#">Уставом</a> и <a href="#">Положением о членских взносах</a>
                            </p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                        <div class="join__politic-question">
                            <p class="join__politic-link">Согласен с <a href="#">политикой обработки ПДн</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn">Подать заявку</button>
                </form>
            </div>

            <?php else: ?>
            <!-- Сценарий 1: гость — полная форма регистрации -->
            <div class="join__wrapper">
                <h2 class="account__title main-title">Вступить в общество</h2>
                <form method="POST" action="/join/" enctype="multipart/form-data">
                    <input type="hidden" name="join_action" value="1">
                    <input type="hidden" name="membership_type" value="basic" id="membership_type">
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Личные данные</h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="email" name="email"       placeholder="Электропочта" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <input type="password" name="password" placeholder="Пароль (мин. 6 символов)" required>
                            <input type="text" name="last_name"    placeholder="Фамилия" required
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                            <input type="text" name="first_name"   placeholder="Имя" required
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                            <input type="text" name="second_name"  placeholder="Отчество"
                                   value="<?= htmlspecialchars($_POST['second_name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__graduate">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Выпускник МГТУ?</h3>
                        </div>
                        <div class="account__graduate-choice">
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="yes" class="account__graduate-input" id="grad-yes"
                                       <?= ($_POST['is_graduate'] ?? '') === 'yes' ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>Да
                            </label>
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="no"  class="account__graduate-input" id="grad-no"
                                       <?= ($_POST['is_graduate'] ?? 'no') !== 'yes' ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>Нет
                            </label>
                        </div>
                    </div>
                    <div class="account__personal" id="graduate-data"
                         style="<?= ($_POST['is_graduate'] ?? 'no') !== 'yes' ? 'display:none' : '' ?>">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Данные выпускника</h3>
                        </div>
                        <div class="account__personal-list account__personal-list--short account__grid">
                            <input type="number" name="grad_year" placeholder="Год окончания" min="1900" max="2099"
                                   value="<?= (int)($_POST['grad_year'] ?? 0) ?: '' ?>">
                            <input type="text"   name="grad_dept" placeholder="Выпускающая кафедра"
                                   value="<?= htmlspecialchars($_POST['grad_dept'] ?? '') ?>">
                            <input type="text"   name="telegram"  placeholder="Telegram"
                                   value="<?= htmlspecialchars($_POST['telegram'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__personal" id="diploma-data"
                         style="<?= ($_POST['is_graduate'] ?? 'no') !== 'yes' ? 'display:none' : '' ?>">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Сведения о дипломе</h3>
                        </div>
                        <div class="account__personal-list account__personal-list--short account__grid">
                            <input type="text" name="diploma_series" placeholder="Серия бланка"
                                   value="<?= htmlspecialchars($_POST['diploma_series'] ?? '') ?>">
                            <input type="text" name="diploma_number" placeholder="Номер бланка"
                                   value="<?= htmlspecialchars($_POST['diploma_number'] ?? '') ?>">
                            <input type="text" name="diploma_date"   placeholder="Дата выдачи"
                                   value="<?= htmlspecialchars($_POST['diploma_date'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Выбор тарифа -->
                    <div class="membership-slider swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide membership-slider__card">
                                <h3 class="membership-slider__title">Базовое</h3>
                                <p class="membership-slider__name">5 000 Р</p>
                                <p class="membership-slider__time">ежегодно</p>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">Возможность размещения резюме на карьерной платформе Политехнического общества;</li>
                                    <li class="membership-slider__item">Доступ в закрытый карьерный канал с вакансиями;</li>
                                    <li class="membership-slider__item">Участие в активностях и мероприятиях общества;</li>
                                    <li class="membership-slider__item">Доступ к витрине компетенций партнёров.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="basic">Выбрать</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
                                <h3 class="membership-slider__title">Профессиональное</h3>
                                <p class="membership-slider__name">50 000 Р</p>
                                <p class="membership-slider__time">ежегодно</p>
                                <button class="membership-slider__advantages">+ Возможности Базового</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">Участие в закрытом чате членов общества уровня «Бизнес»;</li>
                                    <li class="membership-slider__item">Размещение информации о компании на площадках общества;</li>
                                    <li class="membership-slider__item">Возможность предложить собственный проект для поиска спонсоров;</li>
                                    <li class="membership-slider__item">Доступ к базе резюме выпускников на карьерной платформе.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="premium">Выбрать</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--honorary">
                                <h3 class="membership-slider__title">Партнёрское</h3>
                                <p class="membership-slider__name membership-slider__name--small">Индивидуальные условия</p>
                                <p class="membership-slider__time">обсуждается индивидуально</p>
                                <button class="membership-slider__advantages">+ Возможности профессионального</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">Участие в закрытых мероприятиях;</li>
                                    <li class="membership-slider__item">Право стать членом правления.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="partner">Выбрать</button>
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>

                    <div class="join__politic">
                        <div class="join__politic-question">
                            <p class="join__politic-link">
                                Ознакомлен(а) и согласен(а) с <a href="#">Уставом</a> и <a href="#">Положением о членских взносах</a>
                            </p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                        <div class="join__politic-question">
                            <p class="join__politic-link">Согласен с <a href="#">политикой обработки ПДн</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn">Вступить</button>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </section>
    </section><!-- /join-fiz-block -->
</main>

<script>
// Переключатель Физ. / Юр. лицо
function po_switchJoinType(type) {
    var fizBlock = document.getElementById('join-fiz-block');
    var urBlock  = document.getElementById('join-ur-block');
    var btnFiz   = document.getElementById('btn-fiz');
    var btnUr    = document.getElementById('btn-ur');
    if (type === 'ur') {
        if (fizBlock) fizBlock.style.display = 'none';
        if (urBlock)  urBlock.style.display  = '';
        if (btnFiz) btnFiz.classList.add('btn-empty');
        if (btnUr)  btnUr.classList.remove('btn-empty');
    } else {
        if (fizBlock) fizBlock.style.display = '';
        if (urBlock)  urBlock.style.display  = 'none';
        if (btnFiz) btnFiz.classList.remove('btn-empty');
        if (btnUr)  btnUr.classList.add('btn-empty');
    }
}
// Если POST вернул d7_action — показываем юр. блок
<?php if (!empty($_POST['d7_action']) || $d7Done): ?>
po_switchJoinType('ur');
<?php endif; ?>

// Показать/скрыть поля выпускника
document.querySelectorAll('[name="is_graduate"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var show = this.value === 'yes';
        ['graduate-data','diploma-data'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.style.display = show ? '' : 'none';
        });
    });
});
// Выбор тарифа
document.querySelectorAll('.select-plan').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var plan = this.getAttribute('data-plan');
        var field = document.getElementById('membership_type');
        if (field) field.value = plan;
        document.querySelectorAll('.select-plan').forEach(function(b) { b.classList.remove('btn--active'); });
        this.classList.add('btn--active');
    });
});
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
