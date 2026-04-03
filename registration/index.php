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
    $regType      = 'fiz';
    $email        = trim($_POST['fiz_email']        ?? '');
    $password     = $_POST['fiz_password']          ?? '';
    $lastName     = trim($_POST['fiz_last_name']    ?? '');
    $firstName    = trim($_POST['fiz_first_name']   ?? '');
    $secondName   = trim($_POST['fiz_second_name']  ?? '');
    $dob          = trim($_POST['fiz_dob']          ?? '');
    $isGraduate   = ($_POST['fiz_is_graduate']      ?? '') === 'yes';
    $gradYear     = trim($_POST['fiz_grad_year']    ?? '');
    $gradDept     = trim($_POST['fiz_grad_dept']    ?? '');
    $telegram     = trim($_POST['fiz_telegram']     ?? '');
    $diplomaSer   = trim($_POST['fiz_diploma_ser']  ?? '');
    $diplomaNum   = trim($_POST['fiz_diploma_num']  ?? '');
    $diplomaDate  = trim($_POST['fiz_diploma_date'] ?? '');
    $achievements = trim($_POST['fiz_achievements'] ?? '');
    $memberType   = trim($_POST['fiz_membership_type'] ?? 'basic');
    if (!in_array($memberType, ['basic','premium','partner','honorary'])) $memberType = 'basic';
    $agreeCharter = ($_POST['fiz_agree_charter'] ?? '') === 'yes';
    $agreePd      = ($_POST['fiz_agree_pd']      ?? '') === 'yes';

    if (!$email)               $errors[] = 'Введите email';
    if (strlen($password) < 6) $errors[] = 'Пароль — не менее 6 символов';
    if (!$lastName)            $errors[] = 'Введите фамилию';
    if (!$firstName)           $errors[] = 'Введите имя';
    if ($isGraduate && (!$agreeCharter || !$agreePd)) $errors[] = 'Необходимо согласие с Уставом и политикой ПДн';

    if (empty($errors)) {
        $avatarFileId = false;
        if (!empty($_FILES['fiz_avatar']['name']) && $_FILES['fiz_avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarFileId = CFile::SaveFile(
                CFile::MakeFileArray($_FILES['fiz_avatar']['tmp_name'], $_FILES['fiz_avatar']['name']),
                'user_photo'
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
        ];
        if ($avatarFileId) $userData['PERSONAL_PHOTO'] = $avatarFileId;
        $userId = $oUser->Add($userData);
        if ($userId) {
            $USER->Login($email, $password, 'N');
            if ($isGraduate && $hlOk) {
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

// ─── Юр. лицо: обработка формы ───────────────────────────────────────────
$urDone  = false;
$urError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reg_ur_action'])) {
    $regType    = 'ur';
    $urLname    = trim($_POST['ur_last_name']  ?? '');
    $urFname    = trim($_POST['ur_first_name'] ?? '');
    $urSname    = trim($_POST['ur_second_name']?? '');
    $urEmail    = trim($_POST['ur_email']      ?? '');
    $urPassword = $_POST['ur_password']        ?? '';
    $urCompany  = trim($_POST['ur_company']    ?? '');
    $urSite     = trim($_POST['ur_site']       ?? '');
    $urCount    = trim($_POST['ur_count']      ?? '');
    $urCharter  = ($_POST['ur_agree_charter']  ?? '') === 'yes';
    $urPd       = ($_POST['ur_agree_pd']       ?? '') === 'yes';

    if (!$urLname || !$urFname || !$urEmail) $urError = 'Заполните ФИО и Email представителя.';
    elseif (strlen($urPassword) < 6)         $urError = 'Пароль — не менее 6 символов.';
    elseif (!$urCompany)                     $urError = 'Укажите название компании.';
    elseif (!$urCharter || !$urPd)           $urError = 'Необходимо согласие с Уставом и политикой ПДн.';
    else {
        $oUser = new CUser();
        $userId = $oUser->Add([
            'LOGIN'            => $urEmail,
            'EMAIL'            => $urEmail,
            'PASSWORD'         => $urPassword,
            'CONFIRM_PASSWORD' => $urPassword,
            'NAME'             => $urFname,
            'LAST_NAME'        => $urLname,
            'SECOND_NAME'      => $urSname,
            'ACTIVE'           => 'Y',
            'UF_MEMBERSHIP_STATUS' => 'pending',
            'UF_MEMBERSHIP_TYPE'   => 'partner',
        ]);
        if ($userId) {
            $USER->Login($urEmail, $urPassword, 'N');
            if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
                $hlData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
                if ($hlData) {
                    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlData)->getDataClass();
                    $hlClass::add([
                        'UF_USER_ID'     => (int)$userId,
                        'UF_TYPE'        => 'partnership',
                        'UF_STATUS'      => 'new',
                        'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                        'UF_DATA'        => json_encode([
                            'company' => $urCompany, 'site' => $urSite,
                            'contact_name' => $urLname . ' ' . $urFname . ' ' . $urSname,
                            'email' => $urEmail, 'planned_count' => $urCount,
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
            po_sendAdminEmail('partnership', [
                'company'      => $urCompany,
                'contact_name' => $urLname . ' ' . $urFname,
                'email'        => $urEmail,
                'site'         => $urSite,
            ]);
            po_logAction('form_submit', 'application', (int)$userId, 'D7 registration ur');
            $urDone = true;
        } else {
            $urError = $oUser->LAST_ERROR ?: 'Ошибка при создании аккаунта';
        }
    }
}
?>

<main>
<section class="join">
<div class="container">

    <!-- Вкладки физ/юр лицо -->
    <div style="display:flex;gap:12px;margin-bottom:32px;padding-top:32px">
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

        <form method="POST" action="/registration/" enctype="multipart/form-data">
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
                <div class="account__chapter"><h3 class="account__subtitle">Личные данные</h3></div>
                <div class="account__personal-list account__grid">
                    <input type="email"    name="fiz_email"       placeholder="Электропочта *" required
                           value="<?= htmlspecialchars($_POST['fiz_email'] ?? '') ?>">
                    <input type="password" name="fiz_password"    placeholder="Пароль (мин. 6 символов) *" required>
                    <input type="text"     name="fiz_last_name"   placeholder="Фамилия *" required
                           value="<?= htmlspecialchars($_POST['fiz_last_name'] ?? '') ?>">
                    <input type="text"     name="fiz_first_name"  placeholder="Имя *" required
                           value="<?= htmlspecialchars($_POST['fiz_first_name'] ?? '') ?>">
                    <input type="text"     name="fiz_second_name" placeholder="Отчество"
                           value="<?= htmlspecialchars($_POST['fiz_second_name'] ?? '') ?>">
                    <input type="text"     name="fiz_dob"         placeholder="Дата рождения"
                           value="<?= htmlspecialchars($_POST['fiz_dob'] ?? '') ?>">
                </div>
            </div>

            <!-- Выпускник? -->
            <div class="account__graduate" style="margin-top:24px">
                <div class="account__chapter"><h3 class="account__subtitle">Выпускник МГТУ?</h3></div>
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
                               <?= ($_POST['fiz_is_graduate'] ?? 'no') !== 'yes' ? 'checked' : '' ?>>
                        <span class="account__graduate-box"></span>Нет
                    </label>
                </div>
            </div>

            <!-- Баннер: не выпускник -->
            <div id="fiz-dont-block" class="join__dont"
                 style="display:<?= ($_POST['fiz_is_graduate'] ?? 'no') !== 'yes' ? 'block' : 'none' ?>;background:#fff8e1;border-radius:12px;padding:20px 24px;margin:16px 0;border-left:4px solid #f59e0b">
                <p style="font-size:15px;color:#555;line-height:1.6">
                    Членство в Политехническом обществе доступно выпускникам МГТУ им. Н.Э. Баумана.<br>
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
                        <input type="number" name="fiz_grad_year" placeholder="Год окончания" min="1900" max="2099"
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
                        <input type="text" name="fiz_diploma_ser"  placeholder="Серия бланка"
                               value="<?= htmlspecialchars($_POST['fiz_diploma_ser'] ?? '') ?>">
                        <input type="text" name="fiz_diploma_num"  placeholder="Номер бланка"
                               value="<?= htmlspecialchars($_POST['fiz_diploma_num'] ?? '') ?>">
                        <input type="text" name="fiz_diploma_date" placeholder="Дата выдачи"
                               value="<?= htmlspecialchars($_POST['fiz_diploma_date'] ?? '') ?>">
                        <textarea name="fiz_achievements" placeholder="Достижения (необязательно)" style="grid-column:1/-1;resize:vertical;height:80px"><?= htmlspecialchars($_POST['fiz_achievements'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Выбор тарифа -->
                <div style="margin-top:32px">
                    <div class="account__chapter"><h3 class="account__subtitle">Выбор тарифа</h3></div>
                </div>
                <div class="membership-slider swiper" style="margin-top:16px">
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
                        <div class="swiper-slide membership-slider__card membership-slider__card--gratuitous">
                            <h3 class="membership-slider__title">Почётное</h3>
                            <p class="membership-slider__name">Бесценно</p>
                            <p class="membership-slider__time">по результатам заполненной анкеты</p>
                            <button class="membership-slider__advantages">+ Возможности Базового</button>
                            <ul class="membership-slider__list">
                                <li class="membership-slider__item">Для тех, кто внёс значительный вклад в развитие технической науки и деятельности Политехнического общества.</li>
                            </ul>
                            <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="honorary">Выбрать</button>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>

                <!-- Согласия -->
                <div class="join__politic" style="margin-top:24px">
                    <div class="join__politic-question">
                        <p class="join__politic-link">
                            Ознакомлен(а) и согласен(а) с <a href="<?= defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#' ?>" target="_blank">Уставом</a> и <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a>
                        </p>
                        <div class="account__graduate-choice">
                            <label class="account__graduate-item">
                                <input type="radio" name="fiz_agree_charter" value="yes" class="account__graduate-input">
                                <span class="account__graduate-box"></span>Да
                            </label>
                            <label class="account__graduate-item">
                                <input type="radio" name="fiz_agree_charter" value="no" class="account__graduate-input">
                                <span class="account__graduate-box"></span>Нет
                            </label>
                        </div>
                    </div>
                    <div class="join__politic-question">
                        <p class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
                        <div class="account__graduate-choice">
                            <label class="account__graduate-item">
                                <input type="radio" name="fiz_agree_pd" value="yes" class="account__graduate-input">
                                <span class="account__graduate-box"></span>Да
                            </label>
                            <label class="account__graduate-item">
                                <input type="radio" name="fiz_agree_pd" value="no" class="account__graduate-input">
                                <span class="account__graduate-box"></span>Нет
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn authorization__btn" style="margin-top:24px">Вступить</button>

            </div><!-- /fiz-graduate-section -->
        </form>
    </div><!-- /join__wrapper -->
    <?php endif; ?>
    </div><!-- /block-fiz -->

    <!-- ══════════════════════════════════════════ ЮР. ЛИЦО ══ -->
    <div id="block-ur" <?= $regType !== 'ur' ? 'style="display:none"' : '' ?>>

    <?php if ($urDone): ?>
        <div class="join__wrapper" style="text-align:center;padding:60px 0">
            <div style="font-size:56px;margin-bottom:16px">🤝</div>
            <h2 class="account__title main-title">Заявка отправлена!</h2>
            <p style="color:#666;max-width:480px;margin:12px auto 24px;font-size:15px;line-height:1.6">
                Мы свяжемся с вами в течение 5 рабочих дней для обсуждения условий партнёрства.
            </p>
            <a href="/" class="btn">На главную</a>
        </div>
    <?php else: ?>

    <div class="join__wrapper">
        <h2 class="account__title main-title">Вступить в общество (юр. лиц)</h2>

        <?php if ($urError): ?>
        <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
            <p><?= htmlspecialchars($urError) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="/registration/?type=ur">
            <input type="hidden" name="reg_ur_action" value="1">

            <div class="account__personal">
                <div class="account__chapter"><h3 class="account__subtitle">Данные представителя</h3></div>
                <div class="account__personal-list account__grid--tripl">
                    <input type="text"     name="ur_last_name"   placeholder="Фамилия *" required
                           value="<?= htmlspecialchars($_POST['ur_last_name'] ?? '') ?>">
                    <input type="text"     name="ur_first_name"  placeholder="Имя *" required
                           value="<?= htmlspecialchars($_POST['ur_first_name'] ?? '') ?>">
                    <input type="text"     name="ur_second_name" placeholder="Отчество"
                           value="<?= htmlspecialchars($_POST['ur_second_name'] ?? '') ?>">
                    <input type="email"    name="ur_email"       placeholder="Электропочта *" required
                           value="<?= htmlspecialchars($_POST['ur_email'] ?? '') ?>">
                    <input type="password" name="ur_password"    placeholder="Пароль (мин. 6 символов) *" required>
                </div>
            </div>

            <div class="account__personal" style="margin-top:24px">
                <div class="account__chapter"><h3 class="account__subtitle">Сведения о компании</h3></div>
                <div class="account__personal-list account__grid--range">
                    <input type="text" name="ur_company" placeholder="Компания *" required
                           value="<?= htmlspecialchars($_POST['ur_company'] ?? '') ?>">
                    <input type="url"  name="ur_site"    placeholder="Сайт"
                           value="<?= htmlspecialchars($_POST['ur_site'] ?? '') ?>">
                    <input type="text" name="ur_count"   placeholder="Планируемое количество представителей на платформе *" required
                           value="<?= htmlspecialchars($_POST['ur_count'] ?? '') ?>">
                </div>
            </div>

            <div class="join__politic" style="margin-top:24px">
                <div class="join__politic-question">
                    <p class="join__politic-link">
                        Ознакомлен(а) и согласен(а) с <a href="<?= defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#' ?>" target="_blank">Уставом</a> и <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a>
                    </p>
                    <div class="account__graduate-choice">
                        <label class="account__graduate-item">
                            <input type="radio" name="ur_agree_charter" value="yes" class="account__graduate-input">
                            <span class="account__graduate-box"></span>Да
                        </label>
                        <label class="account__graduate-item">
                            <input type="radio" name="ur_agree_charter" value="no" class="account__graduate-input">
                            <span class="account__graduate-box"></span>Нет
                        </label>
                    </div>
                </div>
                <div class="join__politic-question">
                    <p class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
                    <div class="account__graduate-choice">
                        <label class="account__graduate-item">
                            <input type="radio" name="ur_agree_pd" value="yes" class="account__graduate-input">
                            <span class="account__graduate-box"></span>Да
                        </label>
                        <label class="account__graduate-item">
                            <input type="radio" name="ur_agree_pd" value="no" class="account__graduate-input">
                            <span class="account__graduate-box"></span>Нет
                        </label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn authorization__btn" style="margin-top:24px">Вступить</button>
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
    } else {
        if (fizBlock) fizBlock.style.display = '';
        if (urBlock)  urBlock.style.display  = 'none';
        if (tabFiz) { tabFiz.classList.remove('btn-empty'); }
        if (tabUr)  { tabUr.classList.add('btn-empty'); }
    }
}

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

// Выбор тарифа — event delegation, чтобы работало с Swiper-клонами
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.select-plan');
    if (!btn) return;
    var plan  = btn.getAttribute('data-plan');
    var field = document.getElementById('fiz-membership-type');
    if (field) field.value = plan;
    // Визуальное выделение: обводка выбранной карточки
    document.querySelectorAll('.membership-slider__card').forEach(function(card) {
        card.style.outline = '';
        card.style.boxShadow = '';
    });
    var card = btn.closest('.membership-slider__card');
    if (card) {
        card.style.outline = '2px solid #c0392b';
        card.style.boxShadow = '0 0 0 4px rgba(192,57,43,0.15)';
    }
    document.querySelectorAll('.select-plan').forEach(function(b) { b.textContent = 'Выбрать'; b.classList.remove('btn--active'); });
    btn.textContent = '✓ Выбрано';
    btn.classList.add('btn--active');
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
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
