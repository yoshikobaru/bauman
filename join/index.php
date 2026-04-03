<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("╨Т╤Б╤В╤Г╨┐╨╕╤В╤М ╨▓ ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨╛");
$APPLICATION->SetPageProperty('description', '╨Т╤Б╤В╤Г╨┐╨╕╤В╨╡ ╨▓ ╨Я╨╛╨╗╨╕╤В╨╡╤Е╨╜╨╕╤З╨╡╤Б╨║╨╛╨╡ ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨╛ ╨▓╤Л╨┐╤Г╤Б╨║╨╜╨╕╨║╨╛╨▓ ╨Ь╨У╨в╨г ╨╕╨╝. ╨Э.╨н. ╨С╨░╤Г╨╝╨░╨╜╨░. ╨Т╤Л╨▒╨╡╤А╨╕╤В╨╡ ╤В╨╕╨┐ ╤З╨╗╨╡╨╜╤Б╤В╨▓╨░: ╨С╨░╨╖╨╛╨▓╨╛╨╡, ╨Я╤А╨╛╤Д╨╡╤Б╤Б╨╕╨╛╨╜╨░╨╗╤М╨╜╨╛╨╡, ╨Я╨░╤А╤В╨╜╤С╤А╤Б╨║╨╛╨╡ ╨╕╨╗╨╕ ╨Я╨╛╤З╤С╤В╨╜╨╛╨╡.');

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$_ug      = $USER->IsAuthorized() ? $USER->GetUserGroupArray() : [];
$isMember = defined('PO_MEMBER_BASIC_ID') && (
    in_array(PO_MEMBER_BASIC_ID,   $_ug) ||
    in_array(PO_MEMBER_PREMIUM_ID, $_ug) ||
    in_array(PO_PARTNER_ID,        $_ug)
);
$isAuthorized = $USER->IsAuthorized();

$errors      = [];
$joinDone    = false;
$joinType    = 'basic'; // ╤В╨╕╨┐ ╨▓╤Л╨▒╤А╨░╨╜╨╜╨╛╨│╨╛ ╤З╨╗╨╡╨╜╤Б╤В╨▓╨░ ╨┤╨╗╤П ╤Б╨╛╨╛╨▒╤Й╨╡╨╜╨╕╤П

// ╨в╨╕╨┐╤Л ╤З╨╗╨╡╨╜╤Б╤В╨▓╨░ ╤В╤А╨╡╨▒╤Г╤О╤Й╨╕╨╡ ╨╝╨╛╨┤╨╡╤А╨░╤Ж╨╕╨╕ (╨▒╨╡╨╖ ╨┐╤А╤П╨╝╨╛╨╣ ╨╛╨┐╨╗╨░╤В╤Л)
$moderationTypes = ['premium', 'partner', 'honorary'];

// ╨Т╤Б╨┐╨╛╨╝╨╛╨│╨░╤В╨╡╨╗╤М╨╜╨░╤П ╤Д╤Г╨╜╨║╤Ж╨╕╤П: ╤Б╨╛╤Е╤А╨░╨╜╨╕╤В╤М ╨╖╨░╤П╨▓╨║╤Г ╨╜╨░ ╤З╨╗╨╡╨╜╤Б╤В╨▓╨╛ ╨▓ HL-╨▒╨╗╨╛╨║
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

// тАФ ╨Ю╨▒╤А╨░╨▒╨╛╤В╤З╨╕╨║ ╤Д╨╛╤А╨╝╤Л ╨▓╤Б╤В╤Г╨┐╨╗╨╡╨╜╨╕╤П тАФ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['join_action'])) {
    $lastName       = trim($_POST['last_name'] ?? '');
    $firstName      = trim($_POST['first_name'] ?? '');
    $secondName     = trim($_POST['second_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $password       = $_POST['password'] ?? '';
    $isGraduate     = ($_POST['is_graduate'] ?? '') === 'yes';
    $gradYear       = (int)($_POST['grad_year'] ?? 0);
    $gradDept       = trim($_POST['grad_dept'] ?? '');
    $telegram       = trim($_POST['telegram'] ?? '');
    $diplomaSeries  = trim($_POST['diploma_series'] ?? '');
    $diplomaNumber  = trim($_POST['diploma_number'] ?? '');
    $diplomaDate    = trim($_POST['diploma_date'] ?? '');
    $membershipType = trim($_POST['membership_type'] ?? 'basic');
    if (!in_array($membershipType, ['basic', 'premium', 'partner', 'honorary'])) {
        $membershipType = 'basic';
    }
    $joinType     = $membershipType;
    $agreeCharter = ($_POST['agree_charter'] ?? '') === 'yes';
    $agreePd      = ($_POST['agree_pd']      ?? '') === 'yes';

    if (!$agreeCharter || !$agreePd) {
        $errors[] = '╨Э╨╡╨╛╨▒╤Е╨╛╨┤╨╕╨╝╨╛ ╤Б╨╛╨│╨╗╨░╤Б╨╕╨╡ ╤Б ╨г╤Б╤В╨░╨▓╨╛╨╝ ╨╕ ╨┐╨╛╨╗╨╕╤В╨╕╨║╨╛╨╣ ╨Я╨Ф╨╜';
    }

    if (!$isAuthorized) {
        if (!$email)               $errors[] = '╨Т╨▓╨╡╨┤╨╕╤В╨╡ email';
        if (strlen($password) < 6) $errors[] = '╨Я╨░╤А╨╛╨╗╤М ╨┤╨╛╨╗╨╢╨╡╨╜ ╤Б╨╛╨┤╨╡╤А╨╢╨░╤В╤М ╨╜╨╡ ╨╝╨╡╨╜╨╡╨╡ 6 ╤Б╨╕╨╝╨▓╨╛╨╗╨╛╨▓';
        if (!$lastName)            $errors[] = '╨Т╨▓╨╡╨┤╨╕╤В╨╡ ╤Д╨░╨╝╨╕╨╗╨╕╤О';
        if (!$firstName)           $errors[] = '╨Т╨▓╨╡╨┤╨╕╤В╨╡ ╨╕╨╝╤П';

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
                // ╨б╨╛╤Е╤А╨░╨╜╤П╨╡╨╝ ╨╖╨░╤П╨▓╨║╤Г ╨▓ HL-╨▒╨╗╨╛╨║
                if ($hlOk) {
                    po_saveMembershipApplication((int)$userId, $membershipType, [
                        'first_name' => $firstName, 'last_name' => $lastName,
                        'email'      => $email,
                    ]);
                }
                // Email ╨╝╨╛╨┤╨╡╤А╨░╤В╨╛╤А╤Г ╨╡╤Б╨╗╨╕ ╤В╤А╨╡╨▒╤Г╨╡╤В╤Б╤П ╨╝╨╛╨┤╨╡╤А╨░╤Ж╨╕╤П
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
                $errors[] = $oUser->LAST_ERROR ?: '╨Ю╤И╨╕╨▒╨║╨░ ╨┐╤А╨╕ ╤Б╨╛╨╖╨┤╨░╨╜╨╕╨╕ ╨░╨║╨║╨░╤Г╨╜╤В╨░';
            }
        }
    } else {
        // ╨Р╨▓╤В╨╛╤А╨╕╨╖╨╛╨▓╨░╨╜╨╜╤Л╨╣ ╨╜╨╡-╤З╨╗╨╡╨╜ тАФ ╨╛╨▒╨╜╨╛╨▓╨╗╤П╨╡╨╝ UF_ ╨┐╨╛╨╗╤П
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
                    'first_name' => $arCurr['NAME'] ?? '',
                    'last_name'  => $arCurr['LAST_NAME'] ?? '',
                    'email'      => $arCurr['EMAIL'] ?? '',
                ]);
            }
            if (in_array($membershipType, $moderationTypes)) {
                po_sendAdminEmail('membership', [
                    'membership_type' => $membershipType,
                    'first_name' => $arCurr['NAME']      ?? '',
                    'last_name'  => $arCurr['LAST_NAME'] ?? '',
                    'email'      => $arCurr['EMAIL']      ?? '',
                ]);
            }
            $joinDone = true;
            po_logAction('form_submit', 'application', 0, 'D1 update profile join');
        } else {
            $errors[] = $oUser->LAST_ERROR ?: '╨Ю╤И╨╕╨▒╨║╨░ ╤Б╨╛╤Е╤А╨░╨╜╨╡╨╜╨╕╤П ╨┤╨░╨╜╨╜╤Л╤Е';
        }
    }
}

// ╨Ф╨░╨╜╨╜╤Л╨╡ ╤В╨╡╨║╤Г╤Й╨╡╨│╨╛ ╨┐╨╛╨╗╤М╨╖╨╛╨▓╨░╤В╨╡╨╗╤П ╨┤╨╗╤П ╨┐╤А╨╡╨┤╨╖╨░╨┐╨╛╨╗╨╜╨╡╨╜╨╕╤П
$arCurrentUser = [];
if ($isAuthorized) {
    $dbUser = CUser::GetByID($USER->GetID());
    $arCurrentUser = $dbUser->Fetch() ?: [];
}

// тАФтАФтАФ D7: ╨Ш╨╜╨┤╤Г╤Б╤В╤А╨╕╨░╨╗╤М╨╜╨╛╨╡ ╨┐╨░╤А╤В╨╜╤С╤А╤Б╤В╨▓╨╛ (╤О╤А. ╨╗╨╕╤Ж╨╛) тАФтАФтАФ
$d7Done  = false;
$d7Error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d7_action'])) {
    $d7Company  = trim($_POST['d7_company']   ?? '');
    $d7Contact  = trim($_POST['d7_contact']   ?? '');
    $d7Site     = trim($_POST['d7_site']      ?? '');
    $d7Email    = trim($_POST['d7_email']     ?? '');
    $d7Phone    = trim($_POST['d7_phone']     ?? '');
    $d7Count    = trim($_POST['d7_count']     ?? '');
    $d7AgreePd  = ($_POST['d7_agree_pd']      ?? '') === 'yes';

    if (!$d7Company || !$d7Contact || !$d7Email) {
        $d7Error = '╨Ч╨░╨┐╨╛╨╗╨╜╨╕╤В╨╡ ╨╛╨▒╤П╨╖╨░╤В╨╡╨╗╤М╨╜╤Л╨╡ ╨┐╨╛╨╗╤П: ╨Ъ╨╛╨╝╨┐╨░╨╜╨╕╤П, ╨д╨Ш╨Ю, Email.';
    } elseif (!$d7AgreePd) {
        $d7Error = '╨Э╨╡╨╛╨▒╤Е╨╛╨┤╨╕╨╝╨╛ ╤Б╨╛╨│╨╗╨░╤Б╨╕╨╡ ╤Б ╨┐╨╛╨╗╨╕╤В╨╕╨║╨╛╨╣ ╨Я╨Ф╨╜.';
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
                if (!$saved) $d7Error = '╨Ю╤И╨╕╨▒╨║╨░ ╤Б╨╛╤Е╤А╨░╨╜╨╡╨╜╨╕╤П. ╨Я╨╛╨┐╤А╨╛╨▒╤Г╨╣╤В╨╡ ╨┐╨╛╨╖╨╢╨╡.';
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
    <!-- ╨Я╨╡╤А╨╡╨║╨╗╤О╤З╨░╤В╨╡╨╗╤М ╨д╨╕╨╖. / ╨о╤А. ╨╗╨╕╤Ж╨╛ -->
    <section style="background:#f5f5f5;padding:24px 0;border-bottom:1px solid #e0e0e0">
        <div class="container">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <span style="font-weight:600;color:#333">╨Т╤Б╤В╤Г╨┐╨╕╤В╤М ╨║╨░╨║:</span>
                <button id="btn-fiz" onclick="po_switchJoinType('fiz')" class="btn"
                        style="padding:10px 24px">╨д╨╕╨╖╨╕╤З╨╡╤Б╨║╨╛╨╡ ╨╗╨╕╤Ж╨╛</button>
                <button id="btn-ur"  onclick="po_switchJoinType('ur')"  class="btn btn-empty"
                        style="padding:10px 24px">╨о╤А╨╕╨┤╨╕╤З╨╡╤Б╨║╨╛╨╡ ╨╗╨╕╤Ж╨╛ (╨┐╨░╤А╤В╨╜╤С╤А╤Б╤В╨▓╨╛)</button>
            </div>
        </div>
    </section>

    <!-- D7: ╨С╨╗╨╛╨║ ╨┤╨╗╤П ╤О╤А╨╕╨┤╨╕╤З╨╡╤Б╨║╨╕╤Е ╨╗╨╕╤Ж -->
    <section id="join-ur-block" style="display:none">
        <div class="container" style="padding-top:40px;padding-bottom:40px">
            <div class="join__wrapper">
                <?php if ($d7Done): ?>
                <div style="text-align:center;padding:40px 0">
                    <div style="font-size:48px;margin-bottom:12px">ЁЯУЛ</div>
                    <h2 class="account__title main-title">╨Ч╨░╤П╨▓╨║╨░ ╨╜╨░ ╨┐╨░╤А╤В╨╜╤С╤А╤Б╤В╨▓╨╛ ╨╛╤В╨┐╤А╨░╨▓╨╗╨╡╨╜╨░!</h2>
                    <p style="margin-top:12px;color:#666;max-width:480px;margin-left:auto;margin-right:auto">
                        ╨Ь╤Л ╤Б╨▓╤П╨╢╨╡╨╝╤Б╤П ╤Б ╨▓╨░╨╝╨╕ ╨▓ ╤В╨╡╤З╨╡╨╜╨╕╨╡ 5 ╤А╨░╨▒╨╛╤З╨╕╤Е ╨┤╨╜╨╡╨╣ ╨┤╨╗╤П ╨╛╨▒╤Б╤Г╨╢╨┤╨╡╨╜╨╕╤П ╤Г╤Б╨╗╨╛╨▓╨╕╨╣ ╨┐╨░╤А╤В╨╜╤С╤А╤Б╤В╨▓╨░.
                    </p>
                    <a href="/" class="btn" style="margin-top:20px">╨Э╨░ ╨│╨╗╨░╨▓╨╜╤Г╤О</a>
                </div>
                <?php else: ?>
                <h2 class="account__title main-title">╨Ш╨╜╨┤╤Г╤Б╤В╤А╨╕╨░╨╗╤М╨╜╨╛╨╡ ╨┐╨░╤А╤В╨╜╤С╤А╤Б╤В╨▓╨╛</h2>
                <p style="margin-bottom:24px;color:#666">
                    ╨Ф╨╗╤П ╨║╨╛╨╝╨┐╨░╨╜╨╕╨╣, ╨Э╨Ш╨Ш ╨╕ ╨╛╤А╨│╨░╨╜╨╕╨╖╨░╤Ж╨╕╨╣. ╨Я╨╛╤Б╨╗╨╡ ╨╛╤В╨┐╤А╨░╨▓╨║╨╕ ╨╖╨░╤П╨▓╨║╨╕ ╨╝╤Л ╤Б╨▓╤П╨╢╨╡╨╝╤Б╤П ╤Б ╨▓╨░╨╝╨╕ ╨▓ ╤В╨╡╤З╨╡╨╜╨╕╨╡ 5 ╤А╨░╨▒╨╛╤З╨╕╤Е ╨┤╨╜╨╡╨╣.
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
                            <h3 class="account__subtitle">╨Ф╨░╨╜╨╜╤Л╨╡ ╨║╨╛╨╝╨┐╨░╨╜╨╕╨╕</h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="text"  name="d7_company" placeholder="╨Э╨░╨╖╨▓╨░╨╜╨╕╨╡ ╨║╨╛╨╝╨┐╨░╨╜╨╕╨╕ *" required
                                   value="<?= htmlspecialchars($_POST['d7_company'] ?? '') ?>">
                            <input type="url"   name="d7_site"    placeholder="╨б╨░╨╣╤В ╨║╨╛╨╝╨┐╨░╨╜╨╕╨╕"
                                   value="<?= htmlspecialchars($_POST['d7_site'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">╨Ъ╨╛╨╜╤В╨░╨║╤В╤Л ╨┐╤А╨╡╨┤╤Б╤В╨░╨▓╨╕╤В╨╡╨╗╤П</h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="text"  name="d7_contact" placeholder="╨д╨Ш╨Ю ╨┐╤А╨╡╨┤╤Б╤В╨░╨▓╨╕╤В╨╡╨╗╤П *" required
                                   value="<?= htmlspecialchars($_POST['d7_contact'] ?? '') ?>">
                            <input type="email" name="d7_email"   placeholder="Email *" required
                                   value="<?= htmlspecialchars($_POST['d7_email'] ?? ($arCurrentUser['EMAIL'] ?? '')) ?>">
                            <input type="tel"   name="d7_phone"   placeholder="╨в╨╡╨╗╨╡╤Д╨╛╨╜"
                                   value="<?= htmlspecialchars($_POST['d7_phone'] ?? '') ?>">
                            <input type="number" name="d7_count" placeholder="╨Я╨╗╨░╨╜╨╕╤А╤Г╨╡╨╝╨╛╨╡ ╨║╨╛╨╗-╨▓╨╛ ╨┐╤А╨╡╨┤╤Б╤В╨░╨▓╨╕╤В╨╡╨╗╨╡╨╣ *" min="1" required
                                   value="<?= htmlspecialchars($_POST['d7_count'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <p class="join__politic-link">╨б╨╛╨│╨╗╨░╤Б╨╡╨╜ ╤Б <a href="#">╨┐╨╛╨╗╨╕╤В╨╕╨║╨╛╨╣ ╨╛╨▒╤А╨░╨▒╨╛╤В╨║╨╕ ╨Я╨Ф╨╜</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="d7_agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Ф╨░
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="d7_agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Э╨╡╤В
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">╨Ю╤В╨┐╤А╨░╨▓╨╕╤В╤М ╨╖╨░╤П╨▓╨║╤Г ╨╜╨░ ╨┐╨░╤А╤В╨╜╤С╤А╤Б╤В╨▓╨╛</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ╨С╨╗╨╛╨║ ╨┤╨╗╤П ╤Д╨╕╨╖╨╕╤З╨╡╤Б╨║╨╕╤Е ╨╗╨╕╤Ж -->
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
                        <div style="font-size:48px;margin-bottom:16px">тЬЕ</div>
                        <h2 class="account__title main-title" style="margin-bottom:12px">╨Ч╨░╤П╨▓╨║╨░ ╨┐╤А╨╕╨╜╤П╤В╨░!</h2>
                        <p style="font-size:16px;color:#555;max-width:480px;margin:0 auto 24px">
                            ╨Т╨░╤И╨░ ╨╖╨░╤П╨▓╨║╨░ ╨╜╨░ <strong>╨С╨░╨╖╨╛╨▓╨╛╨╡ ╤З╨╗╨╡╨╜╤Б╤В╨▓╨╛</strong> ╨╖╨░╤А╨╡╨│╨╕╤Б╤В╤А╨╕╤А╨╛╨▓╨░╨╜╨░.
                            ╨Т ╤В╨╡╤З╨╡╨╜╨╕╨╡ 1тАУ2 ╤А╨░╨▒╨╛╤З╨╕╤Е ╨┤╨╜╨╡╨╣ ╨╜╨░ ╨▓╨░╤И email ╨┐╤А╨╕╨┤╤С╤В ╨┐╨╕╤Б╤М╨╝╨╛ ╤Б ╤А╨╡╨║╨▓╨╕╨╖╨╕╤В╨░╨╝╨╕ ╨┤╨╗╤П ╨╛╨┐╨╗╨░╤В╤Л ╨▓╨╖╨╜╨╛╤Б╨░ (5 000 тВ╜/╨│╨╛╨┤).
                        </p>
                        <a href="/profile/" class="btn">╨Я╨╡╤А╨╡╨╣╤В╨╕ ╨▓ ╨╗╨╕╤З╨╜╤Л╨╣ ╨║╨░╨▒╨╕╨╜╨╡╤В</a>
                    <?php else:
                        $typeLabelsD = ['premium'=>'╨Я╤А╨╛╤Д╨╡╤Б╤Б╨╕╨╛╨╜╨░╨╗╤М╨╜╨╛╨╡','partner'=>'╨Я╨░╤А╤В╨╜╤С╤А╤Б╨║╨╛╨╡','honorary'=>'╨Я╨╛╤З╤С╤В╨╜╨╛╨╡'];
                        $tLabel = $typeLabelsD[$joinType] ?? $joinType;
                    ?>
                        <div style="font-size:48px;margin-bottom:16px">ЁЯУЛ</div>
                        <h2 class="account__title main-title" style="margin-bottom:12px">╨Ч╨░╤П╨▓╨║╨░ ╨┐╨╡╤А╨╡╨┤╨░╨╜╨░ ╨╜╨░ ╤А╨░╤Б╤Б╨╝╨╛╤В╤А╨╡╨╜╨╕╨╡</h2>
                        <p style="font-size:16px;color:#555;max-width:480px;margin:0 auto 24px">
                            ╨Т╨░╤И╨░ ╨╖╨░╤П╨▓╨║╨░ ╨╜╨░ <strong><?= htmlspecialchars($tLabel) ?> ╤З╨╗╨╡╨╜╤Б╤В╨▓╨╛</strong> ╨┐╤А╨╕╨╜╤П╤В╨░ ╨╕ ╨┐╨╡╤А╨╡╨┤╨░╨╜╨░ ╨╝╨╛╨┤╨╡╤А╨░╤В╨╛╤А╨░╨╝.
                            ╨Ь╤Л ╤Б╨▓╤П╨╢╨╡╨╝╤Б╤П ╤Б ╨▓╨░╨╝╨╕ ╨┐╨╛ email ╨▓ ╤В╨╡╤З╨╡╨╜╨╕╨╡ 3тАУ5 ╤А╨░╨▒╨╛╤З╨╕╤Е ╨┤╨╜╨╡╨╣.
                        </p>
                        <a href="/profile/" class="btn">╨Я╨╡╤А╨╡╨╣╤В╨╕ ╨▓ ╨╗╨╕╤З╨╜╤Л╨╣ ╨║╨░╨▒╨╕╨╜╨╡╤В</a>
                    <?php endif; ?>
                </div>
            <?php elseif ($isMember): ?>
            <!-- ╨б╤Ж╨╡╨╜╨░╤А╨╕╨╣ 3: ╤Г╨╢╨╡ ╤З╨╗╨╡╨╜ ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨░ -->
            <div class="join__wrapper">
                <h2 class="account__title main-title">╨Т╤Л ╤Г╨╢╨╡ ╤З╨╗╨╡╨╜ ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨░</h2>
                <div class="account__chapter">
                    <h3 class="account__subtitle">╨Т╨░╤И ╤В╨░╤А╨╕╤Д</h3>
                </div>
                <div class="account__rate account__rate--proff">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/rate-conus.png" alt="" class="account__rate-conus">
                    <h4 class="account__rate-plan">
                        <?php
                        $typeLabels = [
                            'basic'   => '╨С╨░╨╖╨╛╨▓╨╛╨╡',
                            'premium' => '╨Я╤А╨╛╤Д╨╡╤Б╤Б╨╕╨╛╨╜╨░╨╗╤М╨╜╨╛╨╡',
                            'partner' => '╨Я╨░╤А╤В╨╜╤С╤А╤Б╨║╨╛╨╡',
                            'honorary'=> '╨Я╨╛╤З╤С╤В╨╜╨╛╨╡',
                        ];
                        echo htmlspecialchars($typeLabels[$arCurrentUser['UF_MEMBERSHIP_TYPE'] ?? ''] ?? '╨Р╨║╤В╨╕╨▓╨╜╨╛╨╡');
                        ?>
                    </h4>
                    <div class="account__rate-buttons">
                        <a href="/profile/" class="account__rate-btn btn">╨Я╨╡╤А╨╡╨╣╤В╨╕ ╨▓ ╨╗╨╕╤З╨╜╤Л╨╣ ╨║╨░╨▒╨╕╨╜╨╡╤В</a>
                    </div>
                </div>
            </div>

            <?php elseif ($isAuthorized): ?>
            <!-- ╨б╤Ж╨╡╨╜╨░╤А╨╕╨╣ 2: ╨░╨▓╤В╨╛╤А╨╕╨╖╨╛╨▓╨░╨╜, ╨╜╨╡ ╤З╨╗╨╡╨╜ тАФ ╤Б╨╛╨║╤А╨░╤Й╤С╨╜╨╜╨░╤П ╤Д╨╛╤А╨╝╨░ -->
            <div class="join__wrapper">
                <h2 class="account__title main-title">╨Т╤Б╤В╤Г╨┐╨╕╤В╤М ╨▓ ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨╛</h2>
                <p style="margin-bottom:16px;color:#666">
                    ╨Т╨░╤И╨╕ ╨┤╨░╨╜╨╜╤Л╨╡ ╨┐╤А╨╡╨┤╨╖╨░╨┐╨╛╨╗╨╜╨╡╨╜╤Л ╨╕╨╖ ╨┐╤А╨╛╤Д╨╕╨╗╤П. ╨Т╤Л╨▒╨╡╤А╨╕╤В╨╡ ╤В╨░╤А╨╕╤Д ╨╕ ╨╛╤В╨┐╤А╨░╨▓╤М╤В╨╡ ╨╖╨░╤П╨▓╨║╤Г.
                </p>
                <form method="POST" action="/join/">
                    <input type="hidden" name="join_action" value="1">
                    <input type="hidden" name="membership_type" value="basic" id="membership_type">
                    <div class="account__chapter">
                        <h3 class="account__subtitle">╨Ы╨╕╤З╨╜╤Л╨╡ ╨┤╨░╨╜╨╜╤Л╨╡</h3>
                    </div>
                    <div class="join__grid">
                        <input type="text" name="last_name"   placeholder="╨д╨░╨╝╨╕╨╗╨╕╤П"
                               value="<?= htmlspecialchars($arCurrentUser['LAST_NAME'] ?? '') ?>">
                        <input type="text" name="first_name"  placeholder="╨Ш╨╝╤П"
                               value="<?= htmlspecialchars($arCurrentUser['NAME'] ?? '') ?>">
                        <input type="text" name="second_name" placeholder="╨Ю╤В╤З╨╡╤Б╤В╨▓╨╛"
                               value="<?= htmlspecialchars($arCurrentUser['SECOND_NAME'] ?? '') ?>">
                        <input type="text" name="telegram"    placeholder="Telegram"
                               value="<?= htmlspecialchars($arCurrentUser['UF_TELEGRAM'] ?? '') ?>">
                    </div>
                    <div class="account__graduate">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">╨Т╤Л╨┐╤Г╤Б╨║╨╜╨╕╨║ ╨Ь╨У╨в╨г?</h3>
                        </div>
                        <div class="account__graduate-choice">
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="yes" class="account__graduate-input"
                                       <?= !empty($arCurrentUser['UF_GRADUATE_YEAR']) ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>╨Ф╨░
                            </label>
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="no" class="account__graduate-input"
                                       <?= empty($arCurrentUser['UF_GRADUATE_YEAR']) ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>╨Э╨╡╤В
                            </label>
                        </div>
                    </div>
                    <!-- ╨Т╤Л╨▒╨╛╤А ╤В╨░╤А╨╕╤Д╨░ (╨░╨▓╤В╨╛╤А╨╕╨╖╨╛╨▓╨░╨╜╨╜╤Л╨╣ ╨┐╨╛╨╗╤М╨╖╨╛╨▓╨░╤В╨╡╨╗╤М) -->
                    <div class="account__chapter" style="margin-top:24px">
                        <h3 class="account__subtitle">╨Т╤Л╨▒╨╡╤А╨╕╤В╨╡ ╤В╨░╤А╨╕╤Д</h3>
                    </div>
                    <div class="membership-slider swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide membership-slider__card">
                                <h3 class="membership-slider__title">╨С╨░╨╖╨╛╨▓╨╛╨╡</h3>
                                <p class="membership-slider__name">5 000 ╨а</p>
                                <p class="membership-slider__time">╨╡╨╢╨╡╨│╨╛╨┤╨╜╨╛</p>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">╨Т╨╛╨╖╨╝╨╛╨╢╨╜╨╛╤Б╤В╤М ╤А╨░╨╖╨╝╨╡╤Й╨╡╨╜╨╕╤П ╤А╨╡╨╖╤О╨╝╨╡ ╨╜╨░ ╨║╨░╤А╤М╨╡╤А╨╜╨╛╨╣ ╨┐╨╗╨░╤В╤Д╨╛╤А╨╝╨╡;</li>
                                    <li class="membership-slider__item">╨Ф╨╛╤Б╤В╤Г╨┐ ╨▓ ╨╖╨░╨║╤А╤Л╤В╤Л╨╣ ╨║╨░╤А╤М╨╡╤А╨╜╤Л╨╣ ╨║╨░╨╜╨░╨╗ ╤Б ╨▓╨░╨║╨░╨╜╤Б╨╕╤П╨╝╨╕;</li>
                                    <li class="membership-slider__item">╨г╤З╨░╤Б╤В╨╕╨╡ ╨▓ ╨░╨║╤В╨╕╨▓╨╜╨╛╤Б╤В╤П╤Е ╨╕ ╨╝╨╡╤А╨╛╨┐╤А╨╕╤П╤В╨╕╤П╤Е ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨░;</li>
                                    <li class="membership-slider__item">╨Ф╨╛╤Б╤В╤Г╨┐ ╨║ ╨▓╨╕╤В╤А╨╕╨╜╨╡ ╨║╨╛╨╝╨┐╨╡╤В╨╡╨╜╤Ж╨╕╨╣ ╨┐╨░╤А╤В╨╜╤С╤А╨╛╨▓.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan btn--active" data-plan="basic">╨Т╤Л╨▒╤А╨░╤В╤М</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
                                <h3 class="membership-slider__title">╨Я╤А╨╛╤Д╨╡╤Б╤Б╨╕╨╛╨╜╨░╨╗╤М╨╜╨╛╨╡</h3>
                                <p class="membership-slider__name">50 000 ╨а</p>
                                <p class="membership-slider__time">╨╡╨╢╨╡╨│╨╛╨┤╨╜╨╛</p>
                                <button class="membership-slider__advantages">+ ╨Т╨╛╨╖╨╝╨╛╨╢╨╜╨╛╤Б╤В╨╕ ╨С╨░╨╖╨╛╨▓╨╛╨│╨╛</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">╨г╤З╨░╤Б╤В╨╕╨╡ ╨▓ ╨╖╨░╨║╤А╤Л╤В╨╛╨╝ ╤З╨░╤В╨╡ ╤З╨╗╨╡╨╜╨╛╨▓ ╤Г╤А╨╛╨▓╨╜╤П ┬л╨С╨╕╨╖╨╜╨╡╤Б┬╗;</li>
                                    <li class="membership-slider__item">╨а╨░╨╖╨╝╨╡╤Й╨╡╨╜╨╕╨╡ ╨╕╨╜╤Д╨╛╤А╨╝╨░╤Ж╨╕╨╕ ╨╛ ╨║╨╛╨╝╨┐╨░╨╜╨╕╨╕ ╨╜╨░ ╨┐╨╗╨╛╤Й╨░╨┤╨║╨░╤Е ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨░;</li>
                                    <li class="membership-slider__item">╨Ф╨╛╤Б╤В╤Г╨┐ ╨║ ╨▒╨░╨╖╨╡ ╤А╨╡╨╖╤О╨╝╨╡ ╨▓╤Л╨┐╤Г╤Б╨║╨╜╨╕╨║╨╛╨▓.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="premium">╨Т╤Л╨▒╤А╨░╤В╤М</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--honorary">
                                <h3 class="membership-slider__title">╨Я╨░╤А╤В╨╜╤С╤А╤Б╨║╨╛╨╡</h3>
                                <p class="membership-slider__name membership-slider__name--small">╨Ш╨╜╨┤╨╕╨▓╨╕╨┤╤Г╨░╨╗╤М╨╜╤Л╨╡ ╤Г╤Б╨╗╨╛╨▓╨╕╤П</p>
                                <p class="membership-slider__time">╨╛╨▒╤Б╤Г╨╢╨┤╨░╨╡╤В╤Б╤П ╨╕╨╜╨┤╨╕╨▓╨╕╨┤╤Г╨░╨╗╤М╨╜╨╛</p>
                                <button class="membership-slider__advantages">+ ╨Т╨╛╨╖╨╝╨╛╨╢╨╜╨╛╤Б╤В╨╕ ╨┐╤А╨╛╤Д╨╡╤Б╤Б╨╕╨╛╨╜╨░╨╗╤М╨╜╨╛╨│╨╛</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">╨г╤З╨░╤Б╤В╨╕╨╡ ╨▓ ╨╖╨░╨║╤А╤Л╤В╤Л╤Е ╨╝╨╡╤А╨╛╨┐╤А╨╕╤П╤В╨╕╤П╤Е;</li>
                                    <li class="membership-slider__item">╨Я╤А╨░╨▓╨╛ ╤Б╤В╨░╤В╤М ╤З╨╗╨╡╨╜╨╛╨╝ ╨┐╤А╨░╨▓╨╗╨╡╨╜╨╕╤П.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="partner">╨Т╤Л╨▒╤А╨░╤В╤М</button>
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <div class="join__politic">
                        <div class="join__politic-question">
                            <p class="join__politic-link">
                                ╨Ю╨╖╨╜╨░╨║╨╛╨╝╨╗╨╡╨╜(╨░) ╨╕ ╤Б╨╛╨│╨╗╨░╤Б╨╡╨╜(╨░) ╤Б <a href="#">╨г╤Б╤В╨░╨▓╨╛╨╝</a> ╨╕ <a href="#">╨Я╨╛╨╗╨╛╨╢╨╡╨╜╨╕╨╡╨╝ ╨╛ ╤З╨╗╨╡╨╜╤Б╨║╨╕╤Е ╨▓╨╖╨╜╨╛╤Б╨░╤Е</a>
                            </p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Ф╨░
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Э╨╡╤В
                                </label>
                            </div>
                        </div>
                        <div class="join__politic-question">
                            <p class="join__politic-link">╨б╨╛╨│╨╗╨░╤Б╨╡╨╜ ╤Б <a href="#">╨┐╨╛╨╗╨╕╤В╨╕╨║╨╛╨╣ ╨╛╨▒╤А╨░╨▒╨╛╤В╨║╨╕ ╨Я╨Ф╨╜</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Ф╨░
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Э╨╡╤В
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn">╨Я╨╛╨┤╨░╤В╤М ╨╖╨░╤П╨▓╨║╤Г</button>
                </form>
            </div>

            <?php else: ?>
            <!-- ╨б╤Ж╨╡╨╜╨░╤А╨╕╨╣ 1: ╨│╨╛╤Б╤В╤М тАФ ╨┐╨╛╨╗╨╜╨░╤П ╤Д╨╛╤А╨╝╨░ ╤А╨╡╨│╨╕╤Б╤В╤А╨░╤Ж╨╕╨╕ -->
            <div class="join__wrapper">
                <h2 class="account__title main-title">╨Т╤Б╤В╤Г╨┐╨╕╤В╤М ╨▓ ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨╛</h2>
                <form method="POST" action="/join/" enctype="multipart/form-data">
                    <input type="hidden" name="join_action" value="1">
                    <input type="hidden" name="membership_type" value="basic" id="membership_type">
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">╨Ы╨╕╤З╨╜╤Л╨╡ ╨┤╨░╨╜╨╜╤Л╨╡</h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="email" name="email"       placeholder="╨н╨╗╨╡╨║╤В╤А╨╛╨┐╨╛╤З╤В╨░" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <input type="password" name="password" placeholder="╨Я╨░╤А╨╛╨╗╤М (╨╝╨╕╨╜. 6 ╤Б╨╕╨╝╨▓╨╛╨╗╨╛╨▓)" required>
                            <input type="text" name="last_name"    placeholder="╨д╨░╨╝╨╕╨╗╨╕╤П" required
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                            <input type="text" name="first_name"   placeholder="╨Ш╨╝╤П" required
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                            <input type="text" name="second_name"  placeholder="╨Ю╤В╤З╨╡╤Б╤В╨▓╨╛"
                                   value="<?= htmlspecialchars($_POST['second_name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__graduate">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">╨Т╤Л╨┐╤Г╤Б╨║╨╜╨╕╨║ ╨Ь╨У╨в╨г?</h3>
                        </div>
                        <div class="account__graduate-choice">
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="yes" class="account__graduate-input" id="grad-yes"
                                       <?= ($_POST['is_graduate'] ?? '') === 'yes' ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>╨Ф╨░
                            </label>
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="no"  class="account__graduate-input" id="grad-no"
                                       <?= ($_POST['is_graduate'] ?? 'no') !== 'yes' ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>╨Э╨╡╤В
                            </label>
                        </div>
                    </div>
                    <div class="account__personal" id="graduate-data"
                         style="<?= ($_POST['is_graduate'] ?? 'no') !== 'yes' ? 'display:none' : '' ?>">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">╨Ф╨░╨╜╨╜╤Л╨╡ ╨▓╤Л╨┐╤Г╤Б╨║╨╜╨╕╨║╨░</h3>
                        </div>
                        <div class="account__personal-list account__personal-list--short account__grid">
                            <input type="number" name="grad_year" placeholder="╨У╨╛╨┤ ╨╛╨║╨╛╨╜╤З╨░╨╜╨╕╤П" min="1900" max="2099"
                                   value="<?= (int)($_POST['grad_year'] ?? 0) ?: '' ?>">
                            <input type="text"   name="grad_dept" placeholder="╨Т╤Л╨┐╤Г╤Б╨║╨░╤О╤Й╨░╤П ╨║╨░╤Д╨╡╨┤╤А╨░"
                                   value="<?= htmlspecialchars($_POST['grad_dept'] ?? '') ?>">
                            <input type="text"   name="telegram"  placeholder="Telegram"
                                   value="<?= htmlspecialchars($_POST['telegram'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__personal" id="diploma-data"
                         style="<?= ($_POST['is_graduate'] ?? 'no') !== 'yes' ? 'display:none' : '' ?>">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">╨б╨▓╨╡╨┤╨╡╨╜╨╕╤П ╨╛ ╨┤╨╕╨┐╨╗╨╛╨╝╨╡</h3>
                        </div>
                        <div class="account__personal-list account__personal-list--short account__grid">
                            <input type="text" name="diploma_series" placeholder="╨б╨╡╤А╨╕╤П ╨▒╨╗╨░╨╜╨║╨░"
                                   value="<?= htmlspecialchars($_POST['diploma_series'] ?? '') ?>">
                            <input type="text" name="diploma_number" placeholder="╨Э╨╛╨╝╨╡╤А ╨▒╨╗╨░╨╜╨║╨░"
                                   value="<?= htmlspecialchars($_POST['diploma_number'] ?? '') ?>">
                            <input type="text" name="diploma_date"   placeholder="╨Ф╨░╤В╨░ ╨▓╤Л╨┤╨░╤З╨╕"
                                   value="<?= htmlspecialchars($_POST['diploma_date'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- ╨Т╤Л╨▒╨╛╤А ╤В╨░╤А╨╕╤Д╨░ -->
                    <div class="membership-slider swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide membership-slider__card">
                                <h3 class="membership-slider__title">╨С╨░╨╖╨╛╨▓╨╛╨╡</h3>
                                <p class="membership-slider__name">5 000 ╨а</p>
                                <p class="membership-slider__time">╨╡╨╢╨╡╨│╨╛╨┤╨╜╨╛</p>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">╨Т╨╛╨╖╨╝╨╛╨╢╨╜╨╛╤Б╤В╤М ╤А╨░╨╖╨╝╨╡╤Й╨╡╨╜╨╕╤П ╤А╨╡╨╖╤О╨╝╨╡ ╨╜╨░ ╨║╨░╤А╤М╨╡╤А╨╜╨╛╨╣ ╨┐╨╗╨░╤В╤Д╨╛╤А╨╝╨╡;</li>
                                    <li class="membership-slider__item">╨Ф╨╛╤Б╤В╤Г╨┐ ╨▓ ╨╖╨░╨║╤А╤Л╤В╤Л╨╣ ╨║╨░╤А╤М╨╡╤А╨╜╤Л╨╣ ╨║╨░╨╜╨░╨╗ ╤Б ╨▓╨░╨║╨░╨╜╤Б╨╕╤П╨╝╨╕;</li>
                                    <li class="membership-slider__item">╨г╤З╨░╤Б╤В╨╕╨╡ ╨▓ ╨░╨║╤В╨╕╨▓╨╜╨╛╤Б╤В╤П╤Е ╨╕ ╨╝╨╡╤А╨╛╨┐╤А╨╕╤П╤В╨╕╤П╤Е ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨░;</li>
                                    <li class="membership-slider__item">╨Ф╨╛╤Б╤В╤Г╨┐ ╨║ ╨▓╨╕╤В╤А╨╕╨╜╨╡ ╨║╨╛╨╝╨┐╨╡╤В╨╡╨╜╤Ж╨╕╨╣ ╨┐╨░╤А╤В╨╜╤С╤А╨╛╨▓.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="basic">╨Т╤Л╨▒╤А╨░╤В╤М</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
                                <h3 class="membership-slider__title">╨Я╤А╨╛╤Д╨╡╤Б╤Б╨╕╨╛╨╜╨░╨╗╤М╨╜╨╛╨╡</h3>
                                <p class="membership-slider__name">50 000 ╨а</p>
                                <p class="membership-slider__time">╨╡╨╢╨╡╨│╨╛╨┤╨╜╨╛</p>
                                <button class="membership-slider__advantages">+ ╨Т╨╛╨╖╨╝╨╛╨╢╨╜╨╛╤Б╤В╨╕ ╨С╨░╨╖╨╛╨▓╨╛╨│╨╛</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">╨г╤З╨░╤Б╤В╨╕╨╡ ╨▓ ╨╖╨░╨║╤А╤Л╤В╨╛╨╝ ╤З╨░╤В╨╡ ╤З╨╗╨╡╨╜╨╛╨▓ ╤Г╤А╨╛╨▓╨╜╤П ┬л╨С╨╕╨╖╨╜╨╡╤Б┬╗;</li>
                                    <li class="membership-slider__item">╨а╨░╨╖╨╝╨╡╤Й╨╡╨╜╨╕╨╡ ╨╕╨╜╤Д╨╛╤А╨╝╨░╤Ж╨╕╨╕ ╨╛ ╨║╨╛╨╝╨┐╨░╨╜╨╕╨╕ ╨╜╨░ ╨┐╨╗╨╛╤Й╨░╨┤╨║╨░╤Е ╨╛╨▒╤Й╨╡╤Б╤В╨▓╨░;</li>
                                    <li class="membership-slider__item">╨Ф╨╛╤Б╤В╤Г╨┐ ╨║ ╨▒╨░╨╖╨╡ ╤А╨╡╨╖╤О╨╝╨╡ ╨▓╤Л╨┐╤Г╤Б╨║╨╜╨╕╨║╨╛╨▓.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="premium">╨Т╤Л╨▒╤А╨░╤В╤М</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--honorary">
                                <h3 class="membership-slider__title">╨Я╨░╤А╤В╨╜╤С╤А╤Б╨║╨╛╨╡</h3>
                                <p class="membership-slider__name membership-slider__name--small">╨Ш╨╜╨┤╨╕╨▓╨╕╨┤╤Г╨░╨╗╤М╨╜╤Л╨╡ ╤Г╤Б╨╗╨╛╨▓╨╕╤П</p>
                                <p class="membership-slider__time">╨╛╨▒╤Б╤Г╨╢╨┤╨░╨╡╤В╤Б╤П ╨╕╨╜╨┤╨╕╨▓╨╕╨┤╤Г╨░╨╗╤М╨╜╨╛</p>
                                <button class="membership-slider__advantages">+ ╨Т╨╛╨╖╨╝╨╛╨╢╨╜╨╛╤Б╤В╨╕ ╨┐╤А╨╛╤Д╨╡╤Б╤Б╨╕╨╛╨╜╨░╨╗╤М╨╜╨╛╨│╨╛</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">╨г╤З╨░╤Б╤В╨╕╨╡ ╨▓ ╨╖╨░╨║╤А╤Л╤В╤Л╤Е ╨╝╨╡╤А╨╛╨┐╤А╨╕╤П╤В╨╕╤П╤Е;</li>
                                    <li class="membership-slider__item">╨Я╤А╨░╨▓╨╛ ╤Б╤В╨░╤В╤М ╤З╨╗╨╡╨╜╨╛╨╝ ╨┐╤А╨░╨▓╨╗╨╡╨╜╨╕╤П.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="partner">╨Т╤Л╨▒╤А╨░╤В╤М</button>
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>

                    <div class="join__politic">
                        <div class="join__politic-question">
                            <p class="join__politic-link">
                                ╨Ю╨╖╨╜╨░╨║╨╛╨╝╨╗╨╡╨╜(╨░) ╨╕ ╤Б╨╛╨│╨╗╨░╤Б╨╡╨╜(╨░) ╤Б <a href="#">╨г╤Б╤В╨░╨▓╨╛╨╝</a> ╨╕ <a href="#">╨Я╨╛╨╗╨╛╨╢╨╡╨╜╨╕╨╡╨╝ ╨╛ ╤З╨╗╨╡╨╜╤Б╨║╨╕╤Е ╨▓╨╖╨╜╨╛╤Б╨░╤Е</a>
                            </p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Ф╨░
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Э╨╡╤В
                                </label>
                            </div>
                        </div>
                        <div class="join__politic-question">
                            <p class="join__politic-link">╨б╨╛╨│╨╗╨░╤Б╨╡╨╜ ╤Б <a href="#">╨┐╨╛╨╗╨╕╤В╨╕╨║╨╛╨╣ ╨╛╨▒╤А╨░╨▒╨╛╤В╨║╨╕ ╨Я╨Ф╨╜</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Ф╨░
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>╨Э╨╡╤В
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn">╨Т╤Б╤В╤Г╨┐╨╕╤В╤М</button>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </section>
    </section><!-- /join-fiz-block -->
</main>

<script>
// ╨Я╨╡╤А╨╡╨║╨╗╤О╤З╨░╤В╨╡╨╗╤М ╨д╨╕╨╖. / ╨о╤А. ╨╗╨╕╤Ж╨╛
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
// ╨Х╤Б╨╗╨╕ POST ╨▓╨╡╤А╨╜╤Г╨╗ d7_action тАФ ╨┐╨╛╨║╨░╨╖╤Л╨▓╨░╨╡╨╝ ╤О╤А. ╨▒╨╗╨╛╨║
<?php if (!empty($_POST['d7_action']) || $d7Done): ?>
po_switchJoinType('ur');
<?php endif; ?>

// ╨Я╨╛╨║╨░╨╖╨░╤В╤М/╤Б╨║╤А╤Л╤В╤М ╨┐╨╛╨╗╤П ╨▓╤Л╨┐╤Г╤Б╨║╨╜╨╕╨║╨░
document.querySelectorAll('[name="is_graduate"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var show = this.value === 'yes';
        ['graduate-data','diploma-data'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.style.display = show ? '' : 'none';
        });
    });
});
// ╨Т╤Л╨▒╨╛╤А ╤В╨░╤А╨╕╤Д╨░
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