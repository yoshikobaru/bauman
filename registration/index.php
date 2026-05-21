<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Регистрация");

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$errors   = [];
$regDone  = false;
$urDone   = false;
$urError  = '';
$regType  = $_GET['type'] ?? 'fiz'; // fiz | ur
$postType = $_POST['reg_type'] ?? 'fiz';
$regFlashFiz = function_exists('po_flash_get') ? po_flash_get('registration_fiz') : null;
if (is_array($regFlashFiz)) {
    $regDone = !empty($regFlashFiz['done']);
    $errors = is_array($regFlashFiz['errors'] ?? null) ? $regFlashFiz['errors'] : [];
    if (!empty($regFlashFiz['form']) && is_array($regFlashFiz['form'])) {
        $_POST = array_merge($_POST, $regFlashFiz['form']);
    }
    $regType = 'fiz';
}
$regFlashUr = function_exists('po_flash_get') ? po_flash_get('registration_ur') : null;
if (is_array($regFlashUr)) {
    $urDone = !empty($regFlashUr['done']);
    $urError = (string)($regFlashUr['error'] ?? '');
    if (!empty($regFlashUr['form']) && is_array($regFlashUr['form'])) {
        $_POST = array_merge($_POST, $regFlashUr['form']);
    }
    $regType = 'ur';
}

// ─── Физ. лицо: обработка формы ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reg_fiz_action'])) {
    $regType         = 'fiz';
    $email           = trim($_POST['fiz_email']          ?? '');
    $password        = $_POST['fiz_password']            ?? '';
    $passwordConfirm = $_POST['fiz_password_confirm']    ?? '';
    $lastName        = trim($_POST['fiz_last_name']      ?? '');
    $firstName       = trim($_POST['fiz_first_name']     ?? '');
    $secondName      = trim($_POST['fiz_second_name']    ?? '');
    $phone           = trim($_POST['fiz_phone']        ?? '');
    $dobRaw          = trim($_POST['fiz_dob']            ?? '');
    $isGraduate      = ($_POST['fiz_is_graduate']        ?? '') === 'yes';
    $gradYear        = trim($_POST['fiz_grad_year']      ?? '');
    $gradDept        = trim($_POST['fiz_grad_dept']      ?? '');
    $telegram        = trim($_POST['fiz_telegram']       ?? '');
    $diplomaSer      = trim($_POST['fiz_diploma_ser']    ?? '');
    $diplomaNum      = trim($_POST['fiz_diploma_num']    ?? '');
    $achievements    = trim($_POST['fiz_achievements']   ?? '');
    $memberType      = trim($_POST['fiz_membership_type'] ?? 'basic');
    if (!in_array($memberType, ['basic','premium','partner','honorary'])) $memberType = 'basic';
    $agreeCharter    = !empty($_POST['fiz_agree_charter']);
    $wasMember       = ($_POST['fiz_was_member'] ?? '') === 'yes';
    // diploma date from hidden field (DD.MM.ГГГГ) or fallback from date input
    $diplomaDateRaw  = trim($_POST['fiz_diploma_date_hidden'] ?? trim($_POST['fiz_diploma_date'] ?? ''));
    $diplomaDate     = '';
    if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $diplomaDateRaw)) {
        $diplomaDate = $diplomaDateRaw;
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $diplomaDateRaw)) {
        // YYYY-MM-DD → DD.MM.YYYY
        $parts = explode('-', $diplomaDateRaw);
        $diplomaDate = $parts[2] . '.' . $parts[1] . '.' . $parts[0];
    }

    // Normalize DOB: accept DD.MM.YYYY or YYYY-MM-DD (from type=date)
    $dobRaw2 = trim($_POST['fiz_dob_hidden'] ?? $dobRaw);
    $dob = '';
    if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dobRaw2)) {
        $dob = $dobRaw2;
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dobRaw2)) {
        $parts = explode('-', $dobRaw2);
        $dob = $parts[2] . '.' . $parts[1] . '.' . $parts[0];
    } elseif (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dobRaw)) {
        $dob = $dobRaw;
    }

    // File extension helpers
    $allowedAvatar  = ['jpg','jpeg','png'];
    $allowedDiploma = ['pdf','jpg','jpeg'];
    $allowedScan    = ['pdf','jpg','jpeg','png'];
    function po_regFileExt($file, $allowed) {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return true;
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        return in_array($ext, $allowed);
    }

    if (!$email)                     $errors[] = 'Введите e-mail';
    if (strlen($password) < 8)       $errors[] = 'Пароль — не менее 8 символов';
    if (!preg_match('/^[A-Za-z0-9@$!%*?&_\-#.]+$/', $password))
                                     $errors[] = 'Пароль может содержать только латинские буквы, цифры и символы: @$!%*?&_-#.';
    if ($password !== $passwordConfirm) $errors[] = 'Пароли не совпадают';
    if (!$lastName)                  $errors[] = 'Введите фамилию';
    if (!$firstName)                 $errors[] = 'Введите имя';
    if (!$phone)                     $errors[] = 'Введите номер телефона';
    if (!$dob)                       $errors[] = 'Укажите дату рождения';
    // Avatar extension check
    if (!empty($_FILES['fiz_avatar']['name']) && !po_regFileExt($_FILES['fiz_avatar'], $allowedAvatar)) {
        $errors[] = 'Аватар: допустимы только jpg, jpeg, png';
    }
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
            } elseif (!po_regFileExt($_FILES['fiz_diploma_scan'], $allowedDiploma)) {
                $errors[] = 'Скан диплома: допустимы только pdf, jpg, jpeg';
            }
        }
        // Membership scan (optional) extension check
        if (!empty($_FILES['fiz_membership_scan']['name']) && !po_regFileExt($_FILES['fiz_membership_scan'], $allowedScan)) {
            $errors[] = 'Скан удостоверения: допустимы только pdf, jpg, jpeg, png';
        }
    }

    if (empty($errors)) {
        $diplomaScanId = false;
        if ($isGraduate && !empty($_FILES['fiz_diploma_scan']['name']) && $_FILES['fiz_diploma_scan']['error'] === UPLOAD_ERR_OK) {
            $diplomaScanId = CFile::SaveFile($_FILES['fiz_diploma_scan'], 'diploma_scan');
        }
        $membershipScanId = false;
        if ($wasMember && !empty($_FILES['fiz_membership_scan']['name']) && $_FILES['fiz_membership_scan']['error'] === UPLOAD_ERR_OK) {
            $membershipScanId = CFile::SaveFile($_FILES['fiz_membership_scan'], 'membership_scan');
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
            'UF_PERSONAL_PHONE'    => $phone,
            'UF_DIPLOMA_SERIES'    => $diplomaSer,
            'UF_DIPLOMA_NUMBER'    => $diplomaNum,
            'UF_DIPLOMA_DATE'      => $diplomaDate,
            'UF_DOB'               => $dob,
            'PERSONAL_NOTES'       => $achievements,
        ];
        if (!empty($_FILES['fiz_avatar']['name']) && $_FILES['fiz_avatar']['error'] === UPLOAD_ERR_OK) {
            $userData['PERSONAL_PHOTO'] = $_FILES['fiz_avatar'];
        }
        if ($diplomaScanId)     $userData['UF_DIPLOMA_SCAN']      = $diplomaScanId;
        if ($membershipScanId)  $userData['UF_MEMBERSHIP_SCAN']   = $membershipScanId;
        $userId = $oUser->Add($userData);
        if ($userId) {
            $avatarFileId = 0;
            $createdUser = CUser::GetByID((int)$userId)->Fetch();
            if (!empty($createdUser['PERSONAL_PHOTO'])) {
                $avatarFileId = (int)$createdUser['PERSONAL_PHOTO'];
            }
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
            $fileLinks = [];
            $attachments = [];
            $filesMeta = [];
            $host = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']) ? ('https://' . $_SERVER['HTTP_HOST']) : '';

            $appendFileData = function(string $fieldName, $fileId, string $label, string $linkKey) use (&$fileLinks, &$attachments, &$filesMeta, $host) {
                if (!$fileId) return;
                $filePath = CFile::GetPath((int)$fileId);
                if (!$filePath) return;
                $fileLinks[$linkKey] = $host . $filePath;
                $originalName = $_FILES[$fieldName]['name'] ?? basename($filePath);
                $filesMeta[] = $label . ': ' . $originalName;
                $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
                if (is_file($absolutePath)) {
                    $attachments[] = ['path' => $absolutePath, 'name' => $originalName];
                }
            };
            $appendFileData('fiz_avatar', $avatarFileId, 'Аватар', 'avatar');
            $appendFileData('fiz_diploma_scan', $diplomaScanId, 'Скан диплома', 'diploma_scan');
            $appendFileData('fiz_membership_scan', $membershipScanId, 'Скан удостоверения', 'membership_scan');

            po_sendAdminEmail('membership', [
                'email'                    => $email,
                'тип_заявки'               => $isGraduate ? 'выпускник' : 'не выпускник',
                'тип_членства'             => $isGraduate ? $memberType : 'non_graduate',
                'фамилия'                  => $lastName,
                'имя'                      => $firstName,
                'отчество'                 => $secondName,
                'дата_рождения'            => $dob,
                'выпускник_бауманки'       => $isGraduate ? 'да' : 'нет',
                'год_окончания'            => $gradYear,
                'выпускающая_кафедра'      => $gradDept,
                'telegram'                 => $telegram,
                'телефон'                  => $phone,
                'вступал_ранее'            => $wasMember ? 'да' : 'нет',
                'серия_диплома'            => $diplomaSer,
                'номер_диплома'            => $diplomaNum,
                'дата_выдачи_диплома'      => $diplomaDate,
                'достижения'               => $achievements,
                'согласие_с_уставом_и_пдн' => $agreeCharter ? 'да' : 'нет',
                'id_пользователя'          => (string)$userId,
                'загруженные_файлы'        => $filesMeta ? implode('; ', $filesMeta) : 'Нет',
                'file_links'               => $fileLinks,
            ], [
                'attachments' => $attachments,
            ]);
            $confirmationSent = po_sendMembershipConfirmationEmail($email);
            if (!$confirmationSent) {
                error_log('[registration] confirmation email not sent for user #' . (int)$userId . ', email=' . $email);
            }
            po_logAction('form_submit', 'application', (int)$userId, 'D1 registration fiz');
            $regDone = true;
        } else {
            $errors[] = $oUser->LAST_ERROR ?: 'Ошибка при создании аккаунта';
        }
    }
    if (function_exists('po_flash_set')) {
        po_flash_set('registration_fiz', [
            'done' => $regDone,
            'errors' => $errors,
            'form' => $regDone ? [] : $_POST,
        ]);
    }
    LocalRedirect('/registration/?type=fiz&status=' . ($regDone ? 'success' : 'error'));
    exit;
}

// ─── Юр. лицо (D7: Индустриальное партнёрство) ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reg_ur_action'])) {
    $regType   = 'ur';
    $urCompany = trim($_POST['ur_company'] ?? '');
    $urContact = trim($_POST['ur_contact'] ?? '');
    $urSite    = trim($_POST['ur_site']    ?? '');
    $urEmail   = trim($_POST['ur_email']   ?? '');
    $urPhone   = trim($_POST['ur_phone']   ?? '');
    $urPd      = !empty($_POST['ur_agree_pd']);

    if (!$urCompany || !$urContact || !$urEmail || !$urPhone) {
        $urError = 'Заполните обязательные поля: Компания, ФИО, e-mail, Телефон.';
    } elseif (!po_is_valid_partnership_phone($urPhone)) {
        $urError = 'Укажите телефон цифрами и символами пробел, + и -.';
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
    if (function_exists('po_flash_set')) {
        po_flash_set('registration_ur', [
            'done' => $urDone,
            'error' => $urError,
            'form' => $urDone ? [] : $_POST,
        ]);
    }
    LocalRedirect('/registration/?type=ur&status=' . ($urDone ? 'success' : 'error'));
    exit;
}

$fizDobInputValue = trim($_POST['fiz_dob'] ?? '');
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fizDobInputValue)) {
    [$y, $m, $d] = explode('-', $fizDobInputValue);
    $fizDobInputValue = $d . '.' . $m . '.' . $y;
}
$fizDiplomaDateInputValue = trim($_POST['fiz_diploma_date'] ?? '');
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fizDiplomaDateInputValue)) {
    [$y2, $m2, $d2] = explode('-', $fizDiplomaDateInputValue);
    $fizDiplomaDateInputValue = $d2 . '.' . $m2 . '.' . $y2;
}
?>
<main>
<section class="join join--registration">
<div class="container">
<style>
.po-date-input {
    color: #4e4e4e;
}
.po-date-field {
    position: relative;
}
.po-date-field .po-date-input {
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
    padding: 0;
    margin: 0;
    cursor: pointer;
}
</style>

    <!-- Вкладки физ/юр лицо -->
    <div style="display:flex;gap:12px;margin-bottom:32px;padding-top:8px">
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
                           autocomplete="email"
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

                    <input type="text" name="fiz_last_name"   placeholder="Фамилия *" required
                           value="<?= htmlspecialchars($_POST['fiz_last_name'] ?? '') ?>">

                    <!-- Подтверждение пароля -->
                    <div style="position:relative">
                        <input type="password" name="fiz_password_confirm" id="fiz-pass-confirm" placeholder="Повторите пароль *" required
                               style="width:100%;box-sizing:border-box;padding-right:44px">
                        <button type="button" class="toggle-pass" data-target="fiz-pass-confirm"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#999">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <input type="text" name="fiz_first_name"  placeholder="Имя *" required
                           value="<?= htmlspecialchars($_POST['fiz_first_name'] ?? '') ?>">
                    <div class="po-date-field">
                        <input type="text" name="fiz_dob" id="fiz-dob"
                               placeholder="Дата рождения (ДД.ММ.ГГГГ) *"
                               title="Дата рождения (ДД.ММ.ГГГГ)"
                               autocomplete="bday" required
                               inputmode="numeric" maxlength="10"
                               value="<?= htmlspecialchars($fizDobInputValue) ?>"
                               class="po-date-input">
                        <button type="button" class="po-date-field__btn" data-picker-target="fiz-dob-picker" aria-label="Открыть календарь">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M8 3v4M16 3v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <input type="date" id="fiz-dob-picker" class="po-date-field__native" tabindex="-1" aria-hidden="true">
                    </div>
                    <input type="text" name="fiz_second_name" placeholder="Отчество"
                           value="<?= htmlspecialchars($_POST['fiz_second_name'] ?? '') ?>">
                    <input type="tel" name="fiz_phone" placeholder="Телефон *"
                           autocomplete="tel"
                           value="<?= htmlspecialchars($_POST['fiz_phone'] ?? '') ?>">
                    <input type="hidden" name="fiz_dob_hidden" id="fiz-dob-hidden">
                </div>
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

            <!-- Вступали ли ранее? -->
            <div class="account__graduate" style="margin-top:16px" id="fiz-was-member-block">
                <div class="account__chapter"><h3 class="account__subtitle">Вступали ли вы ранее в Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана?</h3></div>
                <div class="account__graduate-choice">
                    <label class="account__graduate-item">
                        <input type="radio" name="fiz_was_member" value="yes" id="fiz-was-member-yes"
                               class="account__graduate-input"
                               <?= ($_POST['fiz_was_member'] ?? '') === 'yes' ? 'checked' : '' ?>>
                        <span class="account__graduate-box"></span>Да
                    </label>
                    <label class="account__graduate-item">
                        <input type="radio" name="fiz_was_member" value="no" id="fiz-was-member-no"
                               class="account__graduate-input"
                               <?= ($_POST['fiz_was_member'] ?? '') === 'no' ? 'checked' : '' ?>>
                        <span class="account__graduate-box"></span>Нет
                    </label>
                </div>
            </div>
            <!-- Скан удостоверения (если ранее был членом) -->
            <div id="fiz-membership-scan-block" style="display:<?= ($_POST['fiz_was_member'] ?? '') === 'yes' ? '' : 'none' ?>;margin-top:16px;background:#f0f7ff;border-radius:10px;padding:16px 20px">
                <p style="font-size:14px;color:#444;line-height:1.6;margin-bottom:12px">
                    Для подтверждения вашего членства прикрепите скан удостоверения члена Общества и продолжите регистрацию. После подтверждения вашего членства и оплаты членского взноса — ваше членство в Обществе будет продлено.
                </p>
                <label style="font-size:14px;color:#333;display:block;margin-bottom:6px">
                    Скан удостоверения <span style="color:#888;font-size:12px">(необязательно, pdf/jpg/png)</span>
                </label>
                <input type="file" name="fiz_membership_scan" id="fiz-membership-scan-input"
                       accept=".pdf,.jpg,.jpeg,.png" style="font-size:14px">
                <span id="fiz-membership-scan-name" style="display:block;font-size:12px;color:#555;margin-top:4px"></span>
                <span id="fiz-membership-scan-err" style="display:none;color:#e74c3c;font-size:12px;margin-top:4px"></span>
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
                        <select name="fiz_grad_year" id="fiz-grad-year">
                            <option value="">Год окончания</option>
                            <?php for ($y = date('Y'); $y >= 1950; $y--): ?>
                            <option value="<?=$y?>" <?= ($_POST['fiz_grad_year'] ?? '') == (string)$y ? 'selected' : '' ?>><?=$y?></option>
                            <?php endfor; ?>
                        </select>
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
                        <div class="po-date-field">
                            <input type="text" name="fiz_diploma_date" id="fiz-dip-date"
                                   placeholder="Дата выдачи (ДД.ММ.ГГГГ) *"
                                   title="Дата выдачи (ДД.ММ.ГГГГ)"
                                   inputmode="numeric" maxlength="10"
                                   value="<?= htmlspecialchars($fizDiplomaDateInputValue) ?>"
                                   class="po-date-input">
                            <button type="button" class="po-date-field__btn" data-picker-target="fiz-dip-date-picker" aria-label="Открыть календарь">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M8 3v4M16 3v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <input type="date" id="fiz-dip-date-picker" class="po-date-field__native" tabindex="-1" aria-hidden="true">
                        </div>
                        <input type="hidden" name="fiz_diploma_date_hidden" id="fiz-dip-date-hidden">
                    </div>
                    <!-- Скан диплома (обязателен если год ≤ 2020) -->
                    <div id="fiz-diploma-scan-block" style="margin-top:16px;display:none">
                        <label style="font-size:14px;color:#333;display:block;margin-bottom:8px">
                            Скан диплома * <span style="color:#888;font-size:12px">(pdf или jpg, обязательно для выпускников 2020 года и ранее)</span>
                        </label>
                        <input type="file" name="fiz_diploma_scan" id="fiz-diploma-scan-input"
                               accept=".pdf,.jpg,.jpeg" style="font-size:14px">
                        <span id="fiz-diploma-scan-name" style="display:block;font-size:12px;color:#555;margin-top:4px"></span>
                        <span id="fiz-diploma-scan-err" style="display:none;color:#e74c3c;font-size:12px;margin-top:4px"></span>
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
								<li class="membership-slider__item">Участие в активностях, выставках и мероприятиях Политехнического общества;</li>
								<li class="membership-slider__item">Доступ в закрытый карьерный канал с вакансиями от профильных компаний;</li>
								<li class="membership-slider__item">Возможность получить пластиковый пропуск члена Политехнического общества для посещения МГТУ им. Н.Э. Баумана;</li>
								<li class="membership-slider__item">Доступ в электронную библиотеку МГТУ (в разработке).</li>
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
								<li class="membership-slider__item">Участие в бизнес-мероприятиях Политехнического общества в онлайн и очном форматах;</li>
								<li class="membership-slider__item">Возможность предложить собственный проект для поиска спонсоров и поддержки Политехнического общества;</li>
								<li class="membership-slider__item">Возможность участвовать в референс-визитах, организуемых Политехническим обществом;</li>
								<li class="membership-slider__item">Доступ к базе резюме выпускников на карьерной платформе Политехнического общества;</li>
								<li class="membership-slider__item">Участие в закрытом чате членов общества уровня «Бизнес».</li>
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
                        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
                            <input type="checkbox" name="fiz_agree_charter" id="fiz_agree_charter" required
                                   style="width:18px;height:18px;flex-shrink:0;margin-top:2px"
                                   <?= !empty($_POST['fiz_agree_charter']) ? 'checked' : '' ?>>
                            <span class="join__politic-link">
                                Ознакомлен(а) и согласен(а) с <a href="<?= htmlspecialchars(defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#', ENT_QUOTES, 'UTF-8') ?>"<?= function_exists('po_document_link_attrs') ? po_document_link_attrs() : ' target="_blank"' ?> onclick="event.stopPropagation()">Уставом</a> и <a href="<?= htmlspecialchars(defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#', ENT_QUOTES, 'UTF-8') ?>"<?= function_exists('po_document_link_attrs') ? po_document_link_attrs() : ' target="_blank"' ?> onclick="event.stopPropagation()">политикой обработки ПДн</a> *
                            </span>
                        </label>
                        <span id="fiz-agree-err" style="display:none;color:#e74c3c;font-size:13px;margin-top:4px">Необходимо согласие с Уставом и политикой ПДн</span>
                    </div>
                </div>

                <button type="submit" class="btn authorization__btn" id="fiz-submit-btn" style="margin-top:24px">Вступить</button>

            </div><!-- /fiz-graduate-section -->

            <!-- Блок для юр. лиц — Индустриальное партнёрство -->
            <section class="partner" style="margin-top:40px">
                <div class="partner__wrapper">
                    <div class="partner__info">
                        <h2 class="main-title partner__title">Индустриальное партнерство</h2>
                        <p class="main-text partner__text">Для юридических лиц</p>
                        <button type="button" class="btn partner__btn desk-block" onclick="switchRegType('ur')">Стать партнером</button>
                    </div>
                    <div class="partner__discription">
                        <ul class="partner__list">
							<li class="partner__item">Все преимущества базового и бизнес членства;</li>
							<li class="partner__item">Возможность разместить свою компанию на витрине компетенций Политехнического общества (в разработке);</li>
							<li class="partner__item">Рекламные возможности площадок и мероприятий Политехнического общества.</li>
                        </ul>
                        <p class="partner__discription-text">Стоимость обсуждается индивидуально.</p>
                    </div>
                    <button type="button" class="btn partner__btn desk-none" onclick="switchRegType('ur')">Стать партнером</button>
                </div>
            </section>

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

        <?php
        po_render_industrial_partnership_form([
            'prefix'                 => 'ur',
            'action'                 => '/registration/?type=ur',
            'hidden_name'            => 'reg_ur_action',
            'post'                   => $_POST,
            'extra_after_consent'    => '<span id="ur-agree-err" style="display:none;color:#e74c3c;font-size:13px;margin-top:4px">Необходимо согласие с политикой обработки ПДн</span>',
        ]);
        ?>
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

// Конвертация YYYY-MM-DD → DD.MM.YYYY
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

setupDateField('fiz-dob', 'fiz-dob-picker');
setupDateField('fiz-dip-date', 'fiz-dip-date-picker');

document.querySelectorAll('[data-picker-target]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var picker = document.getElementById(this.getAttribute('data-picker-target'));
        if (!picker) return;
        if (typeof picker.showPicker === 'function') {
            picker.showPicker();
        } else {
            picker.focus();
            picker.click();
        }
    });
});

// Показ/скрытие пароля
document.querySelectorAll('.toggle-pass').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var inp = document.getElementById(this.getAttribute('data-target'));
        if (!inp) return;
        var isPass = inp.type === 'password';
        inp.type = isPass ? 'text' : 'password';
        var eyes = this.querySelectorAll('svg');
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
        var wasMemberBlock = document.getElementById('fiz-was-member-block');
        var wasMemberScanBlock = document.getElementById('fiz-membership-scan-block');
        if (section) section.style.display = isGrad ? '' : 'none';
        if (dont)    dont.style.display    = isGrad ? 'none' : 'block';
        // Показываем вопрос о прошлом членстве только выпускникам
        if (wasMemberBlock) wasMemberBlock.style.display = isGrad ? '' : 'none';
        if (!isGrad && wasMemberScanBlock) wasMemberScanBlock.style.display = 'none';
    });
});

// Переключатель "вступали ли ранее"
document.querySelectorAll('[name="fiz_was_member"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var block = document.getElementById('fiz-membership-scan-block');
        if (block) block.style.display = (this.value === 'yes') ? '' : 'none';
    });
});

// Показывать поле скана диплома если год ≤ 2020
function updateDiplomaScanRequirement() {
    var yearEl = document.getElementById('fiz-grad-year');
    var scanBlock = document.getElementById('fiz-diploma-scan-block');
    var scanInput = document.getElementById('fiz-diploma-scan-input');
    if (!yearEl || !scanBlock) return;
    var year = parseInt(yearEl.value, 10);
    var show = year > 0 && year <= 2020;
    scanBlock.style.display = show ? 'block' : 'none';
    if (scanInput) scanInput.required = show;
}
var gradYearSelect = document.getElementById('fiz-grad-year');
if (gradYearSelect) {
    gradYearSelect.addEventListener('change', updateDiplomaScanRequirement);
}
// Инициализация скана при загрузке (если год уже заполнен после ошибки)
(function() {
    updateDiplomaScanRequirement();
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

// Превью аватара + проверка расширения
(function() {
    var inp = document.getElementById('fiz-avatar-input');
    if (!inp) return;
    inp.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        var ext = file.name.split('.').pop().toLowerCase();
        if (['jpg','jpeg','png'].indexOf(ext) === -1) {
            alert('Аватар: допустимы только jpg, jpeg, png');
            inp.value = '';
            return;
        }
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

// Проверка расширения скана диплома
(function() {
    var inp = document.getElementById('fiz-diploma-scan-input');
    var nameEl = document.getElementById('fiz-diploma-scan-name');
    var errEl  = document.getElementById('fiz-diploma-scan-err');
    if (!inp) return;
    inp.addEventListener('change', function() {
        if (!inp.files || !inp.files[0]) { if(nameEl) nameEl.textContent=''; return; }
        var ext = inp.files[0].name.split('.').pop().toLowerCase();
        if (['pdf','jpg','jpeg'].indexOf(ext) === -1) {
            if (errEl) { errEl.textContent = 'Недопустимый формат. Разрешены: PDF, JPG'; errEl.style.display = 'block'; }
            if (nameEl) nameEl.textContent = '';
            inp.value = '';
        } else {
            if (errEl) errEl.style.display = 'none';
            if (nameEl) nameEl.textContent = '📎 ' + inp.files[0].name;
        }
    });
})();

// Проверка расширения скана удостоверения
(function() {
    var inp = document.getElementById('fiz-membership-scan-input');
    var nameEl = document.getElementById('fiz-membership-scan-name');
    var errEl  = document.getElementById('fiz-membership-scan-err');
    if (!inp) return;
    inp.addEventListener('change', function() {
        if (!inp.files || !inp.files[0]) { if(nameEl) nameEl.textContent=''; return; }
        var ext = inp.files[0].name.split('.').pop().toLowerCase();
        if (['pdf','jpg','jpeg','png'].indexOf(ext) === -1) {
            if (errEl) { errEl.textContent = 'Недопустимый формат. Разрешены: PDF, JPG, PNG'; errEl.style.display = 'block'; }
            if (nameEl) nameEl.textContent = '';
            inp.value = '';
        } else {
            if (errEl) errEl.style.display = 'none';
            if (nameEl) nameEl.textContent = '📎 ' + inp.files[0].name;
        }
    });
})();

// Клиентская валидация перед отправкой — форма физ. лица
(function() {
    var form = document.getElementById('form-fiz');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        var errors = [];
        var pass    = document.getElementById('fiz-pass');
        var confirm = document.getElementById('fiz-pass-confirm');
        var dobInp  = document.getElementById('fiz-dob');
        var lname   = form.querySelector('[name="fiz_last_name"]');
        var fname   = form.querySelector('[name="fiz_first_name"]');

        // Заполнить скрытые поля с датами в RU формате
        var dobHidden = document.getElementById('fiz-dob-hidden');
        if (dobHidden && dobInp) dobHidden.value = normalizeRuDate(dobInp.value);
        var dipDateInp = document.getElementById('fiz-dip-date');
        var dipDateHidden = document.getElementById('fiz-dip-date-hidden');
        if (dipDateHidden && dipDateInp) dipDateHidden.value = normalizeRuDate(dipDateInp.value);

        if (!fname || !fname.value.trim()) errors.push('Имя');
        if (!lname || !lname.value.trim()) errors.push('Фамилия');
        var phoneInp = document.getElementById('fiz-phone');
        if (!phoneInp || !phoneInp.value.trim()) errors.push('Телефон');
        if (!dobInp || !dobInp.value) errors.push('Дата рождения');

        if (!pass || !confirm) { /* skip if not visible */ }
        else {
            if (pass.value.length < 8) { errors.push('Пароль (мин. 8 символов)'); }
            else {
                var allowed = /^[A-Za-z0-9@$!%*?&_\-#.]+$/;
                if (!allowed.test(pass.value)) errors.push('Пароль (допустимые символы: латиница, цифры, @$!%*?&_-#.)');
                if (pass.value !== confirm.value) errors.push('Подтверждение пароля (пароли не совпадают)');
            }
        }

        // Проверка согласия (только если выпускник)
        var isGradYes = document.getElementById('fiz-grad-yes');
        if (isGradYes && isGradYes.checked) {
            var agree = document.getElementById('fiz_agree_charter');
            if (!agree || !agree.checked) {
                errors.push('Согласие с Уставом и политикой ПДн');
                var agreeErr = document.getElementById('fiz-agree-err');
                if (agreeErr) agreeErr.style.display = 'block';
            }
            // Проверка скана диплома
            var gradYearEl = document.getElementById('fiz-grad-year');
            if (gradYearEl && gradYearEl.value) {
                var yr = parseInt(gradYearEl.value, 10);
                if (yr > 0 && yr <= 2020) {
                    var scanInp = document.getElementById('fiz-diploma-scan-input');
                    if (!scanInp || !scanInp.files || !scanInp.files[0]) {
                        errors.push('Скан диплома (обязателен для выпускников 2020 и ранее)');
                    }
                }
            }
        }

        if (errors.length > 0) {
            e.preventDefault();
            var box = form.querySelector('.authorization__alert') || document.createElement('div');
            box.className = 'authorization__alert authorization__alert--error';
            box.style.marginBottom = '16px';
            box.innerHTML = errors.map(function(err) { return '<p>' + err + '</p>'; }).join('');
            var firstField = form.querySelector('input[name="fiz_email"]');
            if (firstField && !form.querySelector('.authorization__alert')) {
                firstField.parentNode.insertBefore(box, firstField);
            }
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
})();

// Клиентская валидация — форма юр. лица
(function() {
    var form = document.querySelector('[name="reg_ur_action"]');
    if (!form) return;
    var parentForm = form.closest('form');
    if (!parentForm) return;
    parentForm.addEventListener('submit', function(e) {
        var agree = document.getElementById('ur_agree_pd');
        var errEl = document.getElementById('ur-agree-err');
        if (!agree || !agree.checked) {
            e.preventDefault();
            if (errEl) errEl.style.display = 'block';
        } else {
            if (errEl) errEl.style.display = 'none';
        }
    });
})();
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>