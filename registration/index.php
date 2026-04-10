<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Регистрация");

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$errors   = [];
$regDone  = false;
$regType  = $_GET['type'] ?? 'fiz'; // fiz | ur
$postType = $_POST['reg_type'] ?? 'fiz';

// ─── Физ. лицо: обработка формы ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reg_fiz_action'])) {
    $regType         = 'fiz';
    $email           = trim($_POST['fiz_email']          ?? '');
    $password        = $_POST['fiz_password']            ?? '';
    $passwordConfirm = $_POST['fiz_password_confirm']    ?? '';
    $lastName        = trim($_POST['fiz_last_name']      ?? '');
    $firstName       = trim($_POST['fiz_first_name']     ?? '');
    $secondName      = trim($_POST['fiz_second_name']    ?? '');
    $dobRaw          = trim($_POST['fiz_dob']            ?? '');
    $isGraduate      = ($_POST['fiz_is_graduate']        ?? '') === 'yes';
    $gradYear        = trim($_POST['fiz_grad_year']      ?? '');
    $gradDept        = trim($_POST['fiz_grad_dept']      ?? '');
    $telegram        = trim($_POST['fiz_telegram']       ?? '');
    $diplomaSer      = trim($_POST['fiz_diploma_ser']    ?? '');
    $diplomaNum      = trim($_POST['fiz_diploma_num']    ?? '');
    $diplomaDate     = trim($_POST['fiz_diploma_date']   ?? '');
    $achievements    = trim($_POST['fiz_achievements']   ?? '');
    $memberType      = trim($_POST['fiz_membership_type'] ?? 'basic');
    if (!in_array($memberType, ['basic','premium','partner','honorary'])) $memberType = 'basic';
    $agreeCharter    = ($_POST['fiz_agree_charter'] ?? '') === 'yes';

    // Normalize DOB: accept YYYY-MM-DD (from type=date) or DD.MM.YYYY
    $dob = '';
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dobRaw, $m)) {
        $dob = $m[3] . '.' . $m[2] . '.' . $m[1];
    } elseif (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dobRaw)) {
        $dob = $dobRaw;
    }

    if (!$email)                     $errors[] = 'Введите e-mail';
    if (strlen($password) < 8)       $errors[] = 'Пароль — не менее 8 символов';
    if (!preg_match('/^[A-Za-z0-9@$!%*?&_\-#.]+$/', $password))
                                     $errors[] = 'Пароль может содержать только латинские буквы, цифры и символы: @$!%*?&_-#.';
    if ($password !== $passwordConfirm) $errors[] = 'Пароли не совпадают';
    if (!$lastName)                  $errors[] = 'Введите фамилию';
    if (!$firstName)                 $errors[] = 'Введите имя';
    if (!$dob)                       $errors[] = 'Укажите дату рождения в формате ДД.ММ.ГГГГ';
    if ($isGraduate) {
        if (!$agreeCharter)          $errors[] = 'Необходимо согласие с Уставом и политикой ПДн';
        if (!$diplomaSer)            $errors[] = 'Укажите серию бланка диплома';
        if (!$diplomaNum)            $errors[] = 'Укажите номер бланка диплома';
        if (!$diplomaDate)           $errors[] = 'Укажите дату выдачи диплома';
        // Скан диплома обязателен если год окончания ≤ 2020
        $gradYearInt = (int)$gradYear;
        if ($gradYearInt > 0 && $gradYearInt <= 2020) {
            if (empty($_FILES['fiz_diploma_scan']['name']) || $_FILES['fiz_diploma_scan']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Прикрепите скан диплома (pdf или jpg) — обязательно для выпускников до 2020 года включительно';
            }
        }
    }

    if (empty($errors)) {
        $avatarFileId = false;
        if (!empty($_FILES['fiz_avatar']['name']) && $_FILES['fiz_avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarFileId = CFile::SaveFile(
                CFile::MakeFileArray($_FILES['fiz_avatar']['tmp_name'], $_FILES['fiz_avatar']['name']),
                'user_photo'
            );
        }
        $diplomaScanId = false;
        if ($isGraduate && !empty($_FILES['fiz_diploma_scan']['name']) && $_FILES['fiz_diploma_scan']['error'] === UPLOAD_ERR_OK) {
            $diplomaScanId = CFile::SaveFile(
                CFile::MakeFileArray($_FILES['fiz_diploma_scan']['tmp_name'], $_FILES['fiz_diploma_scan']['name']),
                'diploma_scan'
            );
        }
        $oUser = new CUser();
        $userData = [
            'LOGIN'            => $email,
            'EMAIL'            => $email,
            'PASSWORD'         => $password,
            'CONFIRM_PASSWORD' => $password,
            'NAME'             => $firstName,
            'LAST_NAME'        => $lastName,
            'SECOND_NAME'      => $secondName,
            'ACTIVE'           => 'Y',
            'UF_MEMBERSHIP_STATUS' => $isGraduate ? 'pending' : 'non_graduate',
            'UF_MEMBERSHIP_TYPE'   => $isGraduate ? $memberType : '',
            'UF_GRADUATE_YEAR'     => $isGraduate ? $gradYear : '',
            'UF_GRADUATE_DEPT'     => $isGraduate ? $gradDept : '',
            'UF_TELEGRAM'          => $telegram,
            'UF_DIPLOMA_SERIES'    => $diplomaSer,
            'UF_DIPLOMA_NUMBER'    => $diplomaNum,
            'UF_DIPLOMA_DATE'      => $diplomaDate,
            'UF_DOB'               => $dob,
        ];
        if ($avatarFileId)   $userData['PERSONAL_PHOTO']    = $avatarFileId;
        if ($diplomaScanId)  $userData['UF_DIPLOMA_SCAN']   = $diplomaScanId;
        $userId = $oUser->Add($userData);
        if ($userId) {
            $USER->Login($email, $password, 'N');
            if ($isGraduate && $hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
                $hlData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
                if ($hlData) {
                    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlData)->getDataClass();
                    $hlClass::add([
                        'UF_USER_ID'     => (int)$userId,
                        'UF_TYPE'        => 'membership',
                        'UF_STATUS'      => 'new',
                        'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                        'UF_DATA'        => json_encode([
                            'membership_type' => $memberType,
                            'last_name' => $lastName, 'first_name' => $firstName,
                            'email' => $email, 'grad_year' => $gradYear, 'grad_dept' => $gradDept,
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
            po_sendAdminEmail('membership', [
                'type'       => $isGraduate ? $memberType : 'non_graduate',
                'last_name'  => $lastName, 'first_name' => $firstName,
                'email'      => $email,
                'is_graduate'=> $isGraduate ? 'да' : 'нет',
            ]);
            po_logAction('form_submit', 'application', (int)$userId, 'D1 registration fiz');
            $regDone = true;
        } else {
            $errors[] = $oUser->LAST_ERROR ?: 'Ошибка при создании аккаунта';
        }
    }
}

// ─── Юр. лицо (D7: Индустриальное партнёрство) ───────────────────────────
$urDone  = false;
$urError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reg_ur_action'])) {
    $regType   = 'ur';
    $urCompany = trim($_POST['ur_company'] ?? '');
    $urContact = trim($_POST['ur_contact'] ?? '');
    $urSite    = trim($_POST['ur_site']    ?? '');
    $urEmail   = trim($_POST['ur_email']   ?? '');
    $urPhone   = trim($_POST['ur_phone']   ?? '');
    $urPd      = ($_POST['ur_agree_pd']    ?? '') === 'yes';

    if (!$urCompany || !$urContact || !$urEmail || !$urPhone) {
        $urError = 'Заполните обязательные поля: Компания, ФИО, e-mail, Телефон.';
    } elseif (!$urPd) {
        $urError = 'Необходимо согласие с политикой ПДн.';
    } else {
        $saved = false;
        if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
            $hlData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
            if ($hlData) {
                $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlData)->getDataClass();
                $res = $hlClass::add([
                    'UF_USER_ID'     => 0,
                    'UF_TYPE'        => 'partnership',
                    'UF_STATUS'      => 'new',
                    'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                    'UF_DATA'        => json_encode([
                        'company' => $urCompany, 'contact_name' => $urContact,
                        'site' => $urSite, 'email' => $urEmail, 'phone' => $urPhone,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
                $saved = $res->isSuccess();
                if (!$saved) $urError = 'Ошибка сохранения. Попробуйте позже.';
            }
        } else {
            $saved = true;
        }
        if ($saved) {
            $urDone = true;
            po_logAction('form_submit', 'application', 0, 'D7 registration ur partnership');
            po_sendAdminEmail('partnership', [
                'company' => $urCompany, 'contact_name' => $urContact,
                'email' => $urEmail, 'phone' => $urPhone, 'site' => $urSite,
            ]);
        }
    }
}
?>

<main>
<section class="join">
<div class="container">

    <!-- Вкладки физ/юр лицо -->
    <div style="display:flex;gap:12px;margin-bottom:32px;padding-top:16px">
        <button id="tab-fiz" onclick="switchRegType('fiz')"
                class="btn <?= $regType !== 'ur' ? '' : 'btn-empty' ?>"
                style="padding:10px 28px">Физическое лицо</button>
        <button id="tab-ur"  onclick="switchRegType('ur')"
                class="btn <?= $regType === 'ur' ? '' : 'btn-empty' ?>"
                style="padding:10px 28px">Юридическое лицо</button>
    </div>

    <!-- ══════════════════════════════════════════ ФИЗ. ЛИЦО ══ -->
    <div id="block-fiz" <?= $regType === 'ur' ? 'style="display:none"' : '' ?>>

    <?php if ($regDone): ?>
        <div class="join__wrapper" style="text-align:center;padding:60px 0">
            <div style="font-size:56px;margin-bottom:16px">✅</div>
            <h2 class="account__title main-title">Аккаунт создан!</h2>
            <p style="color:#666;max-width:480px;margin:12px auto 24px;font-size:15px;line-height:1.6">
                Ваша заявка принята. Мы свяжемся с вами для подтверждения членства.
            </p>
            <a href="/profile/" class="btn">Перейти в профиль</a>
        </div>
    <?php else: ?>

    <div class="join__wrapper">
        <h2 class="account__title main-title">Вступить в общество</h2>

        <?php if (!empty($errors)): ?>
        <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
            <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/registration/" enctype="multipart/form-data" id="form-fiz" novalidate>
            <input type="hidden" name="reg_fiz_action" value="1">
            <input type="hidden" name="fiz_membership_type" value="basic" id="fiz-membership-type">

            <!-- Аватар -->
            <div class="account__photo" style="margin-bottom:24px">
                <div style="display:flex;align-items:center;gap:16px;margin-top:8px">
                    <div id="fiz-avatar-preview" style="width:80px;height:80px;border-radius:50%;background:#e0e0e0;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                        <img id="fiz-avatar-img" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none">
                        <svg id="fiz-avatar-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </div>
                    <label style="cursor:pointer">
                        <span class="btn btn-empty" style="font-size:13px">Загрузить аватар</span><br>
                        <span style="font-size:11px;color:#999;display:block;margin-top:4px">Изображение 300×300, jpg/png</span>
                        <input type="file" name="fiz_avatar" accept="image/*" style="display:none" id="fiz-avatar-input">
                    </label>
                </div>
            </div>

            <!-- Личные данные -->
            <div class="account__personal">
                <div class="account__chapter"><h3 class="account__subtitle">Личные данные <span style="color:#e31e24;font-size:13px;font-weight:400;margin-left:8px">* — обязательные поля</span></h3></div>
                <div class="account__personal-list account__grid">
                    <input type="email" name="fiz_email" placeholder="e-mail *" required
                           value="<?= htmlspecialchars($_POST['fiz_email'] ?? '') ?>">

                    <!-- Пароль с показом -->
                    <div style="position:relative">
                        <input type="password" name="fiz_password" id="fiz-pass" placeholder="Пароль (мин. 8 символов) *" required
                               minlength="8" pattern="[A-Za-z0-9@$!%*?&_\-#.]{8,}"
                               style="width:100%;box-sizing:border-box;padding-right:44px">
                        <button type="button" class="toggle-pass" data-target="fiz-pass"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#999">
                            <svg id="fiz-pass-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="fiz-pass-eye-off" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>

                    <!-- Подтверждение пароля -->
                    <div style="position:relative">
                        <input type="password" name="fiz_password_confirm" id="fiz-pass-confirm" placeholder="Повторите пароль *" required
                               style="width:100%;box-sizing:border-box;padding-right:44px">
                        <button type="button" class="toggle-pass" data-target="fiz-pass-confirm"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#999">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>

                    <input type="text" name="fiz_last_name"   placeholder="Фамилия *" required
                           value="<?= htmlspecialchars($_POST['fiz_last_name'] ?? '') ?>">
                    <input type="text" name="fiz_first_name"  placeholder="Имя *" required
                           value="<?= htmlspecialchars($_POST['fiz_first_name'] ?? '') ?>">
                    <input type="text" name="fiz_second_name" placeholder="Отчество"
                           value="<?= htmlspecialchars($_POST['fiz_second_name'] ?? '') ?>">

                    <!-- Дата рождения с календарём -->
                    <div style="position:relative">
                        <input type="date" name="fiz_dob" id="fiz-dob" placeholder="Дата рождения *" required
                               max="<?= date('Y-m-d') ?>"
                               value="<?php
                                    $dobPost = $_POST['fiz_dob'] ?? '';
                                    if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $dobPost, $m2))
                                        echo $m2[3].'-'.$m2[2].'-'.$m2[1];
                                    else echo htmlspecialchars($dobPost);
                               ?>"
                               style="width:100%;box-sizing:border-box">
                    </div>
                </div>
                <p style="font-size:12px;color:#888;margin-top:6px">Дата рождения: формат ДД.ММ.ГГГГ</p>
            </div>

            <!-- Выпускник? -->
            <div class="account__graduate" style="margin-top:24px">
                <div class="account__chapter"><h3 class="account__subtitle">Выпускник МГТУ (МВТУ) им. Н.Э. Баумана?</h3></div>
                <div class="account__graduate-choice">
                    <label class="account__graduate-item">
                        <input type="radio" name="fiz_is_graduate" value="yes" id="fiz-grad-yes"
                               class="account__graduate-input"
                               <?= ($_POST['fiz_is_graduate'] ?? '') === 'yes' ? 'checked' : '' ?>>
                        <span class="account__graduate-box"></span>Да
                    </label>
                    <label class="account__graduate-item">
                        <input type="radio" name="fiz_is_graduate" value="no" id="fiz-grad-no"
                               class="account__graduate-input"
                               <?= ($_POST['fiz_is_graduate'] ?? '') === 'no' ? 'checked' : '' ?>>
                        <span class="account__graduate-box"></span>Нет
                    </label>
                </div>
            </div>

            <!-- Баннер: не выпускник -->
            <div id="fiz-dont-block" class="join__dont"
                 style="display:<?= ($_POST['fiz_is_graduate'] ?? '') === 'no' ? 'block' : 'none' ?>;background:#fff8e1;border-radius:12px;padding:20px 24px;margin:16px 0;border-left:4px solid #f59e0b">
                <p style="font-size:15px;color:#555;line-height:1.6">
                    Членство в Политехническом обществе доступно выпускникам МВТУ (МГТУ) им. Н.Э. Баумана.<br>
                    Если вы хотите сотрудничать в другом формате — свяжитесь с нами:
                    <a href="mailto:info@bauman-polytech.ru" style="font-weight:600">info@bauman-polytech.ru</a>
                </p>
            </div>

            <!-- Данные выпускника (только если Да) -->
            <div id="fiz-graduate-section"
                 style="display:<?= ($_POST['fiz_is_graduate'] ?? '') === 'yes' ? '' : 'none' ?>">

                <div class="account__personal" style="margin-top:24px">
                    <div class="account__chapter"><h3 class="account__subtitle">Данные выпускника</h3></div>
                    <div class="account__personal-list account__personal-list--short account__grid">
                        <input type="number" name="fiz_grad_year" id="fiz-grad-year" placeholder="Год окончания" min="1900" max="2099"
                               value="<?= htmlspecialchars($_POST['fiz_grad_year'] ?? '') ?>">
                        <input type="text"   name="fiz_grad_dept" placeholder="Выпускающая кафедра"
                               value="<?= htmlspecialchars($_POST['fiz_grad_dept'] ?? '') ?>">
                        <input type="text"   name="fiz_telegram"  placeholder="Telegram"
                               value="<?= htmlspecialchars($_POST['fiz_telegram'] ?? '') ?>">
                    </div>
                </div>

                <div class="account__personal" style="margin-top:24px">
                    <div class="account__chapter"><h3 class="account__subtitle">Сведения о дипломе</h3></div>
                    <div class="account__personal-list account__personal-list--short account__grid">
                        <input type="text" name="fiz_diploma_ser"  placeholder="Серия бланка *" id="fiz-dip-ser"
                               value="<?= htmlspecialchars($_POST['fiz_diploma_ser'] ?? '') ?>">
                        <input type="text" name="fiz_diploma_num"  placeholder="Номер бланка *" id="fiz-dip-num"
                               value="<?= htmlspecialchars($_POST['fiz_diploma_num'] ?? '') ?>">
                        <input type="text" name="fiz_diploma_date" placeholder="Дата выдачи *" id="fiz-dip-date"
                               value="<?= htmlspecialchars($_POST['fiz_diploma_date'] ?? '') ?>">
                    </div>
                    <!-- Скан диплома (обязателен если год ≤ 2020) -->
                    <div id="fiz-diploma-scan-block" style="margin-top:16px;display:none">
                        <label style="font-size:14px;color:#333;display:block;margin-bottom:8px">
                            Скан диплома * <span style="color:#888;font-size:12px">(pdf или jpg, обязательно для выпускников 2020 года и ранее)</span>
                        </label>
                        <input type="file" name="fiz_diploma_scan" id="fiz-diploma-scan-input"
                               accept=".pdf,.jpg,.jpeg" style="font-size:14px">
                    </div>
                </div>

                <div class="account__personal" style="margin-top:24px">
                    <div class="account__chapter"><h3 class="account__subtitle">Достижения</h3></div>
                    <div class="account__personal-list">
                        <textarea name="fiz_achievements" placeholder="Достижения (необязательно)" style="width:100%;box-sizing:border-box;resize:vertical;height:80px"><?= htmlspecialchars($_POST['fiz_achievements'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Выбор тарифа -->
                <div style="margin-top:32px">
                    <div class="account__chapter"><h3 class="account__subtitle">Выбор тарифа</h3></div>
                </div>
                <div class="membership-slider swiper" style="margin-top:16px">
                    <div class="swiper-wrapper">
                        <!-- Базовое -->
                        <div class="swiper-slide membership-slider__card">
                            <h3 class="membership-slider__title">Базовое</h3>
                            <p class="membership-slider__name">1 000 Р</p>
                            <p class="membership-slider__time">ежегодно</p>
                            <ul class="membership-slider__list">
                                <li class="membership-slider__item">Возможность размещения резюме на карьерной платформе Политехнического общества;</li>
                                <li class="membership-slider__item">Доступ в закрытый карьерный канал с вакансиями от профильных компаний;</li>
                                <li class="membership-slider__item">Участие в активностях, выставках и мероприятиях Политехнического общества;</li>
                                <li class="membership-slider__item">Доступ в электронную библиотеку МГТУ (в разработке);</li>
                                <li class="membership-slider__item">Доступ к витрине компетенций партнёров Политехнического общества, кафедр, студенческих конструкторских бюро и научно-образовательных центров МГТУ.</li>
                            </ul>
                            <button type="button" class="membership-slider__join btn btn-empty select-plan btn--active" data-plan="basic">Выбрать</button>
                        </div>
                        <!-- Профессиональное -->
                        <div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
                            <h3 class="membership-slider__title">Профессиональное</h3>
                            <p class="membership-slider__name">50 000 Р</p>
                            <p class="membership-slider__time">ежегодно</p>
                            <button class="membership-slider__advantages">+ Возможности Базового</button>
                            <ul class="membership-slider__list">
                                <li class="membership-slider__item">Участие в закрытом чате членов общества уровня «Бизнес»;</li>
                                <li class="membership-slider__item">Размещение информации и новостей о компании на площадках Политехнического общества;</li>
                                <li class="membership-slider__item">Возможность предложить собственный проект для поиска спонсоров и поддержки Политехнического общества;</li>
                                <li class="membership-slider__item">Участие в бизнес-мероприятиях Политехнического общества в онлайн и очном форматах;</li>
                                <li class="membership-slider__item">Доступ к базе резюме выпускников на карьерной платформе Политехнического общества.</li>
                            </ul>
                            <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="premium">Выбрать</button>
                        </div>
                        <!-- Партнёрское -->
                        <div class="swiper-slide membership-slider__card membership-slider__card--honorary">
                            <h3 class="membership-slider__title">Партнёрское</h3>
                            <p class="membership-slider__name membership-slider__name--small">Персональные условия</p>
                            <p class="membership-slider__time">обсуждается индивидуально</p>
                            <button class="membership-slider__advantages">+ Возможности профессионального</button>
                            <ul class="membership-slider__list">
                                <li class="membership-slider__item">Участие в закрытых мероприятиях Политехнического общества;</li>
                                <li class="membership-slider__item">Право стать членом Совета Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана;</li>
                                <li class="membership-slider__item">Участие в закрытом чате партнёров Политехнического общества.</li>
                            </ul>
                            <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="partner">Выбрать</button>
                        </div>
                        <!-- Почётное -->
                        <div class="swiper-slide membership-slider__card membership-slider__card--gratuitous">
                            <h3 class="membership-slider__title">Почётное</h3>
                            <p class="membership-slider__name">Бесценно</p>
                            <p class="membership-slider__time">по результатам заполненной анкеты</p>
                            <button class="membership-slider__advantages">+ Возможности Базового</button>
                            <ul class="membership-slider__list">
                                <li class="membership-slider__item">Для тех, кто внёс значительный вклад в развитие технической науки, образования, технологий и деятельности Политехнического общества.</li>
                            </ul>
                            <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="honorary">Выбрать</button>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>

                <!-- Согласие (единое: Устав + ПДн) -->
                <div class="join__politic" style="margin-top:24px">
                    <div class="join__politic-question">
                        <p class="join__politic-link">
                            Ознакомлен(а) и согласен(а) с <a href="<?= defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#' ?>" target="_blank">Уставом</a> и <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a> *
                        </p>
                        <div class="account__graduate-choice">
                            <label class="account__graduate-item">
                                <input type="radio" name="fiz_agree_charter" value="yes" class="account__graduate-input"
                                       <?= ($_POST['fiz_agree_charter'] ?? '') === 'yes' ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>Да
                            </label>
                            <label class="account__graduate-item">
                                <input type="radio" name="fiz_agree_charter" value="no" class="account__graduate-input"
                                       <?= ($_POST['fiz_agree_charter'] ?? '') === 'no' ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>Нет
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn authorization__btn" id="fiz-submit-btn" style="margin-top:24px">Вступить</button>

            </div><!-- /fiz-graduate-section -->

            <!-- Блок для юр. лиц — Индустриальное партнёрство -->
            <div style="margin-top:32px">
                <div class="partner__wrapper" style="background:#1a2035;border-radius:16px;padding:32px;color:#fff;display:flex;gap:32px;align-items:center;flex-wrap:wrap">
                    <div style="flex:0 0 auto;max-width:280px">
                        <h3 style="font-size:22px;font-weight:700;margin-bottom:8px;color:#fff">Индустриальное партнерство</h3>
                        <p style="font-size:14px;color:rgba(255,255,255,0.7);margin-bottom:20px">Для юридических лиц</p>
                        <button type="button" class="btn" onclick="switchRegType('ur')">Стать партнером</button>
                    </div>
                    <div style="flex:1;min-width:220px">
                        <ul style="list-style:none;padding:0;margin:0 0 12px;display:flex;flex-direction:column;gap:8px">
                            <li style="font-size:14px;color:rgba(255,255,255,0.85);padding-left:20px;position:relative"><span style="position:absolute;left:0;color:#e31e24">•</span>Все преимущества базового и бизнес членства</li>
                            <li style="font-size:14px;color:rgba(255,255,255,0.85);padding-left:20px;position:relative"><span style="position:absolute;left:0;color:#e31e24">•</span>Возможность состоять в индустриальном клубе Политехнического общества</li>
                            <li style="font-size:14px;color:rgba(255,255,255,0.85);padding-left:20px;position:relative"><span style="position:absolute;left:0;color:#e31e24">•</span>Доступ к витрине компетенций, возможность разместить заказ/взять задачу</li>
                            <li style="font-size:14px;color:rgba(255,255,255,0.85);padding-left:20px;position:relative"><span style="position:absolute;left:0;color:#e31e24">•</span>Рекламные возможности площадок и мероприятий Политехнического общества</li>
                        </ul>
                        <p style="font-size:13px;color:rgba(255,255,255,0.5)">Стоимость обсуждается индивидуально.</p>
                    </div>
                </div>
            </div>

        </form>
    </div><!-- /join__wrapper -->
    <?php endif; ?>
    </div><!-- /block-fiz -->

    <!-- ══════════════════════════════════════════ ЮР. ЛИЦО ══ -->
    <div id="block-ur" <?= $regType !== 'ur' ? 'style="display:none"' : '' ?>>

    <?php if ($urDone): ?>
        <div class="join__wrapper" style="text-align:center;padding:60px 0">
            <div style="font-size:56px;margin-bottom:16px">🤝</div>
            <h2 class="account__title main-title">Заявка на партнёрство отправлена!</h2>
            <p style="color:#666;max-width:480px;margin:12px auto 24px;font-size:15px;line-height:1.6">
                Мы свяжемся с вами в течение 5 рабочих дней для обсуждения условий партнёрства.
            </p>
            <a href="/" class="btn">На главную</a>
        </div>
    <?php else: ?>

    <div class="join__wrapper">
        <h2 class="account__title main-title">Индустриальное партнёрство</h2>
        <p style="margin-bottom:24px;color:#666">Для компаний, НИИ и организаций. После отправки заявки мы свяжемся с вами в течение 5 рабочих дней.</p>

        <?php if ($urError): ?>
        <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
            <p><?= htmlspecialchars($urError) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="/registration/?type=ur">
            <input type="hidden" name="reg_ur_action" value="1">

            <div class="account__personal">
                <div class="account__chapter"><h3 class="account__subtitle">Данные компании <span style="color:#e31e24;font-size:13px;font-weight:400;margin-left:8px">* — обязательные поля</span></h3></div>
                <div class="account__personal-list account__grid">
                    <input type="text" name="ur_company" placeholder="Название компании *" required
                           value="<?= htmlspecialchars($_POST['ur_company'] ?? '') ?>">
                    <input type="url"  name="ur_site"    placeholder="Сайт компании"
                           value="<?= htmlspecialchars($_POST['ur_site'] ?? '') ?>">
                </div>
            </div>

            <div class="account__personal" style="margin-top:24px">
                <div class="account__chapter"><h3 class="account__subtitle">Контакты представителя</h3></div>
                <div class="account__personal-list account__grid">
                    <input type="text"  name="ur_contact" placeholder="ФИО представителя *" required
                           value="<?= htmlspecialchars($_POST['ur_contact'] ?? '') ?>">
                    <input type="email" name="ur_email"   placeholder="e-mail *" required
                           value="<?= htmlspecialchars($_POST['ur_email'] ?? '') ?>">
                    <input type="tel"   name="ur_phone"   placeholder="Телефон *" required
                           value="<?= htmlspecialchars($_POST['ur_phone'] ?? '') ?>">
                </div>
            </div>

            <div class="join__politic" style="margin-top:24px">
                <div class="join__politic-question">
                    <p class="join__politic-link">Ознакомлен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a> *</p>
                    <div class="account__graduate-choice">
                        <label class="account__graduate-item">
                            <input type="radio" name="ur_agree_pd" value="yes" class="account__graduate-input"
                                   <?= ($_POST['ur_agree_pd'] ?? '') === 'yes' ? 'checked' : '' ?>>
                            <span class="account__graduate-box"></span>Да
                        </label>
                        <label class="account__graduate-item">
                            <input type="radio" name="ur_agree_pd" value="no" class="account__graduate-input"
                                   <?= ($_POST['ur_agree_pd'] ?? '') === 'no' ? 'checked' : '' ?>>
                            <span class="account__graduate-box"></span>Нет
                        </label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn authorization__btn" style="margin-top:24px">Отправить заявку на партнёрство</button>
        </form>
    </div>
    <?php endif; ?>
    </div><!-- /block-ur -->

</div><!-- /container -->
</section>
</main>

<script>
function switchRegType(type) {
    var fizBlock = document.getElementById('block-fiz');
    var urBlock  = document.getElementById('block-ur');
    var tabFiz   = document.getElementById('tab-fiz');
    var tabUr    = document.getElementById('tab-ur');
    if (type === 'ur') {
        if (fizBlock) fizBlock.style.display = 'none';
        if (urBlock)  urBlock.style.display  = '';
        if (tabFiz) { tabFiz.classList.add('btn-empty'); }
        if (tabUr)  { tabUr.classList.remove('btn-empty'); }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        if (fizBlock) fizBlock.style.display = '';
        if (urBlock)  urBlock.style.display  = 'none';
        if (tabFiz) { tabFiz.classList.remove('btn-empty'); }
        if (tabUr)  { tabUr.classList.add('btn-empty'); }
    }
}

// Показ/скрытие пароля
document.querySelectorAll('.toggle-pass').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var inp = document.getElementById(this.getAttribute('data-target'));
        if (!inp) return;
        var isPass = inp.type === 'password';
        inp.type = isPass ? 'text' : 'password';
        // Переключаем иконки если они есть внутри кнопки
        var eyes    = this.querySelectorAll('svg');
        if (eyes.length === 2) {
            eyes[0].style.display = isPass ? 'none' : '';
            eyes[1].style.display = isPass ? '' : 'none';
        }
    });
});

// Переключатель "выпускник"
document.querySelectorAll('[name="fiz_is_graduate"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var isGrad  = this.value === 'yes';
        var section = document.getElementById('fiz-graduate-section');
        var dont    = document.getElementById('fiz-dont-block');
        if (section) section.style.display = isGrad ? '' : 'none';
        if (dont)    dont.style.display    = isGrad ? 'none' : 'block';
    });
});

// Показывать поле скана диплома если год ≤ 2020
document.addEventListener('input', function(e) {
    if (e.target && e.target.id === 'fiz-grad-year') {
        var year = parseInt(e.target.value, 10);
        var scanBlock = document.getElementById('fiz-diploma-scan-block');
        var scanInput = document.getElementById('fiz-diploma-scan-input');
        if (!scanBlock) return;
        var show = year > 0 && year <= 2020;
        scanBlock.style.display = show ? 'block' : 'none';
        if (scanInput) scanInput.required = show;
    }
});
// Инициализация скана при загрузке (если год уже заполнен после ошибки)
(function() {
    var yearEl = document.getElementById('fiz-grad-year');
    if (yearEl && yearEl.value) {
        var year = parseInt(yearEl.value, 10);
        var scanBlock = document.getElementById('fiz-diploma-scan-block');
        var scanInput = document.getElementById('fiz-diploma-scan-input');
        if (scanBlock && year > 0 && year <= 2020) {
            scanBlock.style.display = 'block';
            if (scanInput) scanInput.required = true;
        }
    }
})();

// Выбор тарифа
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.select-plan');
    if (!btn) return;
    var plan = btn.getAttribute('data-plan');

    var field = document.getElementById('fiz-membership-type');
    if (field) field.value = plan;

    document.querySelectorAll('.select-plan').forEach(function(b) {
        b.textContent = 'Выбрать';
        b.classList.remove('btn--active');
    });
    btn.textContent = '✓ Выбрано';
    btn.classList.add('btn--active');

    var submitBtn = document.getElementById('fiz-submit-btn');
    if (submitBtn) {
        submitBtn.textContent = plan === 'honorary' ? 'Подать заявку' : 'Вступить';
    }
});

// Превью аватара
(function() {
    var inp = document.getElementById('fiz-avatar-input');
    if (!inp) return;
    inp.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            var img  = document.getElementById('fiz-avatar-img');
            var icon = document.getElementById('fiz-avatar-icon');
            if (img)  { img.src = e.target.result; img.style.display = 'block'; }
            if (icon) icon.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
})();

// Клиентская валидация перед отправкой
(function() {
    var form = document.getElementById('form-fiz');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        var pass    = document.getElementById('fiz-pass');
        var confirm = document.getElementById('fiz-pass-confirm');
        if (!pass || !confirm) return;
        if (pass.value.length < 8) {
            e.preventDefault();
            alert('Пароль должен содержать не менее 8 символов.');
            pass.focus();
            return;
        }
        var allowed = /^[A-Za-z0-9@$!%*?&_\-#.]+$/;
        if (!allowed.test(pass.value)) {
            e.preventDefault();
            alert('Пароль может содержать только латинские буквы, цифры и символы: @$!%*?&_-#.');
            pass.focus();
            return;
        }
        if (pass.value !== confirm.value) {
            e.preventDefault();
            alert('Пароли не совпадают.');
            confirm.focus();
            return;
        }
    });
})();
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
