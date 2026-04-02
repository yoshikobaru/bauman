<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Р’СЃС‚СѓРїРёС‚СЊ РІ РѕР±С‰РµСЃС‚РІРѕ");
$APPLICATION->SetPageProperty('description', 'Р’СЃС‚СѓРїРёС‚Рµ РІ РџРѕР»РёС‚РµС…РЅРёС‡РµСЃРєРѕРµ РѕР±С‰РµСЃС‚РІРѕ РІС‹РїСѓСЃРєРЅРёРєРѕРІ РњР“РўРЈ РёРј. Рќ.Р­. Р‘Р°СѓРјР°РЅР°. Р’С‹Р±РµСЂРёС‚Рµ С‚РёРї С‡Р»РµРЅСЃС‚РІР°: Р‘Р°Р·РѕРІРѕРµ, РџСЂРѕС„РµСЃСЃРёРѕРЅР°Р»СЊРЅРѕРµ, РџР°СЂС‚РЅС‘СЂСЃРєРѕРµ РёР»Рё РџРѕС‡С‘С‚РЅРѕРµ.');

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
$joinType    = 'basic'; // С‚РёРї РІС‹Р±СЂР°РЅРЅРѕРіРѕ С‡Р»РµРЅСЃС‚РІР° РґР»СЏ СЃРѕРѕР±С‰РµРЅРёСЏ

// РўРёРїС‹ С‡Р»РµРЅСЃС‚РІР° С‚СЂРµР±СѓСЋС‰РёРµ РјРѕРґРµСЂР°С†РёРё (Р±РµР· РїСЂСЏРјРѕР№ РѕРїР»Р°С‚С‹)
$moderationTypes = ['premium', 'partner', 'honorary'];

// Р’СЃРїРѕРјРѕРіР°С‚РµР»СЊРЅР°СЏ С„СѓРЅРєС†РёСЏ: СЃРѕС…СЂР°РЅРёС‚СЊ Р·Р°СЏРІРєСѓ РЅР° С‡Р»РµРЅСЃС‚РІРѕ РІ HL-Р±Р»РѕРє
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

// вЂ” РћР±СЂР°Р±РѕС‚С‡РёРє С„РѕСЂРјС‹ РІСЃС‚СѓРїР»РµРЅРёСЏ вЂ”
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
        $errors[] = 'РќРµРѕР±С…РѕРґРёРјРѕ СЃРѕРіР»Р°СЃРёРµ СЃ РЈСЃС‚Р°РІРѕРј Рё РїРѕР»РёС‚РёРєРѕР№ РџР”РЅ';
    }

    if (!$isAuthorized) {
        if (!$email)               $errors[] = 'Р’РІРµРґРёС‚Рµ email';
        if (strlen($password) < 6) $errors[] = 'РџР°СЂРѕР»СЊ РґРѕР»Р¶РµРЅ СЃРѕРґРµСЂР¶Р°С‚СЊ РЅРµ РјРµРЅРµРµ 6 СЃРёРјРІРѕР»РѕРІ';
        if (!$lastName)            $errors[] = 'Р’РІРµРґРёС‚Рµ С„Р°РјРёР»РёСЋ';
        if (!$firstName)           $errors[] = 'Р’РІРµРґРёС‚Рµ РёРјСЏ';

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
                // РЎРѕС…СЂР°РЅСЏРµРј Р·Р°СЏРІРєСѓ РІ HL-Р±Р»РѕРє
                if ($hlOk) {
                    po_saveMembershipApplication((int)$userId, $membershipType, [
                        'first_name' => $firstName, 'last_name' => $lastName,
                        'email'      => $email,
                    ]);
                }
                // Email РјРѕРґРµСЂР°С‚РѕСЂСѓ РµСЃР»Рё С‚СЂРµР±СѓРµС‚СЃСЏ РјРѕРґРµСЂР°С†РёСЏ
                if (in_array($membershipType, $moderationTypes)) {
                    po_sendAdminEmail('membership', [
                        'membership_type' => $membershipType,
                        'first_name' => $firstName, 'last_name' => $lastName,
                        'email' => $email,
                    ]);
                }
                $joinDone = true;
                po_logAction('form_submit', 'application', 0, 'Заявка на вступление');
            } else {
                $errors[] = $oUser->LAST_ERROR ?: 'РћС€РёР±РєР° РїСЂРё СЃРѕР·РґР°РЅРёРё Р°РєРєР°СѓРЅС‚Р°';
            }
        }
    } else {
        // РђРІС‚РѕСЂРёР·РѕРІР°РЅРЅС‹Р№ РЅРµ-С‡Р»РµРЅ вЂ” РѕР±РЅРѕРІР»СЏРµРј UF_ РїРѕР»СЏ
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
                po_logAction('form_submit', 'application', 0, 'Заявка на вступление');
        } else {
            $errors[] = $oUser->LAST_ERROR ?: 'РћС€РёР±РєР° СЃРѕС…СЂР°РЅРµРЅРёСЏ РґР°РЅРЅС‹С…';
        }
    }
}

// Р”Р°РЅРЅС‹Рµ С‚РµРєСѓС‰РµРіРѕ РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ РґР»СЏ РїСЂРµРґР·Р°РїРѕР»РЅРµРЅРёСЏ
$arCurrentUser = [];
if ($isAuthorized) {
    $dbUser = CUser::GetByID($USER->GetID());
    $arCurrentUser = $dbUser->Fetch() ?: [];
}

// вЂ”вЂ”вЂ” D7: РРЅРґСѓСЃС‚СЂРёР°Р»СЊРЅРѕРµ РїР°СЂС‚РЅС‘СЂСЃС‚РІРѕ (СЋСЂ. Р»РёС†Рѕ) вЂ”вЂ”вЂ”
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
        $d7Error = 'Р—Р°РїРѕР»РЅРёС‚Рµ РѕР±СЏР·Р°С‚РµР»СЊРЅС‹Рµ РїРѕР»СЏ: РљРѕРјРїР°РЅРёСЏ, Р¤РРћ, Email.';
    } elseif (!$d7AgreePd) {
        $d7Error = 'РќРµРѕР±С…РѕРґРёРјРѕ СЃРѕРіР»Р°СЃРёРµ СЃ РїРѕР»РёС‚РёРєРѕР№ РџР”РЅ.';
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
                if (!$saved) $d7Error = 'РћС€РёР±РєР° СЃРѕС…СЂР°РЅРµРЅРёСЏ. РџРѕРїСЂРѕР±СѓР№С‚Рµ РїРѕР·Р¶Рµ.';
            }
        } else {
            $saved = true;
        }
        if ($saved) {
            po_logAction('form_submit', 'application', 0, 'D7 индустриальное партнёрство');
            $d7Done = true;
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
    <!-- РџРµСЂРµРєР»СЋС‡Р°С‚РµР»СЊ Р¤РёР·. / Р®СЂ. Р»РёС†Рѕ -->
    <section style="background:#f5f5f5;padding:24px 0;border-bottom:1px solid #e0e0e0">
        <div class="container">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <span style="font-weight:600;color:#333">Р’СЃС‚СѓРїРёС‚СЊ РєР°Рє:</span>
                <button id="btn-fiz" onclick="po_switchJoinType('fiz')" class="btn"
                        style="padding:10px 24px">Р¤РёР·РёС‡РµСЃРєРѕРµ Р»РёС†Рѕ</button>
                <button id="btn-ur"  onclick="po_switchJoinType('ur')"  class="btn btn-empty"
                        style="padding:10px 24px">Р®СЂРёРґРёС‡РµСЃРєРѕРµ Р»РёС†Рѕ (РїР°СЂС‚РЅС‘СЂСЃС‚РІРѕ)</button>
            </div>
        </div>
    </section>

    <!-- D7: Р‘Р»РѕРє РґР»СЏ СЋСЂРёРґРёС‡РµСЃРєРёС… Р»РёС† -->
    <section id="join-ur-block" style="display:none">
        <div class="container" style="padding-top:40px;padding-bottom:40px">
            <div class="join__wrapper">
                <?php if ($d7Done): ?>
                <div style="text-align:center;padding:40px 0">
                    <div style="font-size:48px;margin-bottom:12px">рџ“‹</div>
                    <h2 class="account__title main-title">Р—Р°СЏРІРєР° РЅР° РїР°СЂС‚РЅС‘СЂСЃС‚РІРѕ РѕС‚РїСЂР°РІР»РµРЅР°!</h2>
                    <p style="margin-top:12px;color:#666;max-width:480px;margin-left:auto;margin-right:auto">
                        РњС‹ СЃРІСЏР¶РµРјСЃСЏ СЃ РІР°РјРё РІ С‚РµС‡РµРЅРёРµ 5 СЂР°Р±РѕС‡РёС… РґРЅРµР№ РґР»СЏ РѕР±СЃСѓР¶РґРµРЅРёСЏ СѓСЃР»РѕРІРёР№ РїР°СЂС‚РЅС‘СЂСЃС‚РІР°.
                    </p>
                    <a href="/" class="btn" style="margin-top:20px">РќР° РіР»Р°РІРЅСѓСЋ</a>
                </div>
                <?php else: ?>
                <h2 class="account__title main-title">РРЅРґСѓСЃС‚СЂРёР°Р»СЊРЅРѕРµ РїР°СЂС‚РЅС‘СЂСЃС‚РІРѕ</h2>
                <p style="margin-bottom:24px;color:#666">
                    Р”Р»СЏ РєРѕРјРїР°РЅРёР№, РќРР Рё РѕСЂРіР°РЅРёР·Р°С†РёР№. РџРѕСЃР»Рµ РѕС‚РїСЂР°РІРєРё Р·Р°СЏРІРєРё РјС‹ СЃРІСЏР¶РµРјСЃСЏ СЃ РІР°РјРё РІ С‚РµС‡РµРЅРёРµ 5 СЂР°Р±РѕС‡РёС… РґРЅРµР№.
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
                            <h3 class="account__subtitle">Р”Р°РЅРЅС‹Рµ РєРѕРјРїР°РЅРёРё</h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="text"  name="d7_company" placeholder="РќР°Р·РІР°РЅРёРµ РєРѕРјРїР°РЅРёРё *" required
                                   value="<?= htmlspecialchars($_POST['d7_company'] ?? '') ?>">
                            <input type="url"   name="d7_site"    placeholder="РЎР°Р№С‚ РєРѕРјРїР°РЅРёРё"
                                   value="<?= htmlspecialchars($_POST['d7_site'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">РљРѕРЅС‚Р°РєС‚С‹ РїСЂРµРґСЃС‚Р°РІРёС‚РµР»СЏ</h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="text"  name="d7_contact" placeholder="Р¤РРћ РїСЂРµРґСЃС‚Р°РІРёС‚РµР»СЏ *" required
                                   value="<?= htmlspecialchars($_POST['d7_contact'] ?? '') ?>">
                            <input type="email" name="d7_email"   placeholder="Email *" required
                                   value="<?= htmlspecialchars($_POST['d7_email'] ?? ($arCurrentUser['EMAIL'] ?? '')) ?>">
                            <input type="tel"   name="d7_phone"   placeholder="РўРµР»РµС„РѕРЅ"
                                   value="<?= htmlspecialchars($_POST['d7_phone'] ?? '') ?>">
                            <input type="number" name="d7_count" placeholder="РџР»Р°РЅРёСЂСѓРµРјРѕРµ РєРѕР»-РІРѕ РїСЂРµРґСЃС‚Р°РІРёС‚РµР»РµР№ *" min="1" required
                                   value="<?= htmlspecialchars($_POST['d7_count'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <p class="join__politic-link">РЎРѕРіР»Р°СЃРµРЅ СЃ <a href="#">РїРѕР»РёС‚РёРєРѕР№ РѕР±СЂР°Р±РѕС‚РєРё РџР”РЅ</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="d7_agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Р”Р°
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="d7_agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>РќРµС‚
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">РћС‚РїСЂР°РІРёС‚СЊ Р·Р°СЏРІРєСѓ РЅР° РїР°СЂС‚РЅС‘СЂСЃС‚РІРѕ</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Р‘Р»РѕРє РґР»СЏ С„РёР·РёС‡РµСЃРєРёС… Р»РёС† -->
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
                        <div style="font-size:48px;margin-bottom:16px">вњ…</div>
                        <h2 class="account__title main-title" style="margin-bottom:12px">Р—Р°СЏРІРєР° РїСЂРёРЅСЏС‚Р°!</h2>
                        <p style="font-size:16px;color:#555;max-width:480px;margin:0 auto 24px">
                            Р’Р°С€Р° Р·Р°СЏРІРєР° РЅР° <strong>Р‘Р°Р·РѕРІРѕРµ С‡Р»РµРЅСЃС‚РІРѕ</strong> Р·Р°СЂРµРіРёСЃС‚СЂРёСЂРѕРІР°РЅР°.
                            Р’ С‚РµС‡РµРЅРёРµ 1вЂ“2 СЂР°Р±РѕС‡РёС… РґРЅРµР№ РЅР° РІР°С€ email РїСЂРёРґС‘С‚ РїРёСЃСЊРјРѕ СЃ СЂРµРєРІРёР·РёС‚Р°РјРё РґР»СЏ РѕРїР»Р°С‚С‹ РІР·РЅРѕСЃР° (5 000 в‚Ѕ/РіРѕРґ).
                        </p>
                        <a href="/profile/" class="btn">РџРµСЂРµР№С‚Рё РІ Р»РёС‡РЅС‹Р№ РєР°Р±РёРЅРµС‚</a>
                    <?php else:
                        $typeLabelsD = ['premium'=>'РџСЂРѕС„РµСЃСЃРёРѕРЅР°Р»СЊРЅРѕРµ','partner'=>'РџР°СЂС‚РЅС‘СЂСЃРєРѕРµ','honorary'=>'РџРѕС‡С‘С‚РЅРѕРµ'];
                        $tLabel = $typeLabelsD[$joinType] ?? $joinType;
                    ?>
                        <div style="font-size:48px;margin-bottom:16px">рџ“‹</div>
                        <h2 class="account__title main-title" style="margin-bottom:12px">Р—Р°СЏРІРєР° РїРµСЂРµРґР°РЅР° РЅР° СЂР°СЃСЃРјРѕС‚СЂРµРЅРёРµ</h2>
                        <p style="font-size:16px;color:#555;max-width:480px;margin:0 auto 24px">
                            Р’Р°С€Р° Р·Р°СЏРІРєР° РЅР° <strong><?= htmlspecialchars($tLabel) ?> С‡Р»РµРЅСЃС‚РІРѕ</strong> РїСЂРёРЅСЏС‚Р° Рё РїРµСЂРµРґР°РЅР° РјРѕРґРµСЂР°С‚РѕСЂР°Рј.
                            РњС‹ СЃРІСЏР¶РµРјСЃСЏ СЃ РІР°РјРё РїРѕ email РІ С‚РµС‡РµРЅРёРµ 3вЂ“5 СЂР°Р±РѕС‡РёС… РґРЅРµР№.
                        </p>
                        <a href="/profile/" class="btn">РџРµСЂРµР№С‚Рё РІ Р»РёС‡РЅС‹Р№ РєР°Р±РёРЅРµС‚</a>
                    <?php endif; ?>
                </div>
            <?php elseif ($isMember): ?>
            <!-- РЎС†РµРЅР°СЂРёР№ 3: СѓР¶Рµ С‡Р»РµРЅ РѕР±С‰РµСЃС‚РІР° -->
            <div class="join__wrapper">
                <h2 class="account__title main-title">Р’С‹ СѓР¶Рµ С‡Р»РµРЅ РѕР±С‰РµСЃС‚РІР°</h2>
                <div class="account__chapter">
                    <h3 class="account__subtitle">Р’Р°С€ С‚Р°СЂРёС„</h3>
                </div>
                <div class="account__rate account__rate--proff">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/rate-conus.png" alt="" class="account__rate-conus">
                    <h4 class="account__rate-plan">
                        <?php
                        $typeLabels = [
                            'basic'   => 'Р‘Р°Р·РѕРІРѕРµ',
                            'premium' => 'РџСЂРѕС„РµСЃСЃРёРѕРЅР°Р»СЊРЅРѕРµ',
                            'partner' => 'РџР°СЂС‚РЅС‘СЂСЃРєРѕРµ',
                            'honorary'=> 'РџРѕС‡С‘С‚РЅРѕРµ',
                        ];
                        echo htmlspecialchars($typeLabels[$arCurrentUser['UF_MEMBERSHIP_TYPE'] ?? ''] ?? 'РђРєС‚РёРІРЅРѕРµ');
                        ?>
                    </h4>
                    <div class="account__rate-buttons">
                        <a href="/profile/" class="account__rate-btn btn">РџРµСЂРµР№С‚Рё РІ Р»РёС‡РЅС‹Р№ РєР°Р±РёРЅРµС‚</a>
                    </div>
                </div>
            </div>

            <?php elseif ($isAuthorized): ?>
            <!-- РЎС†РµРЅР°СЂРёР№ 2: Р°РІС‚РѕСЂРёР·РѕРІР°РЅ, РЅРµ С‡Р»РµРЅ вЂ” СЃРѕРєСЂР°С‰С‘РЅРЅР°СЏ С„РѕСЂРјР° -->
            <div class="join__wrapper">
                <h2 class="account__title main-title">Р’СЃС‚СѓРїРёС‚СЊ РІ РѕР±С‰РµСЃС‚РІРѕ</h2>
                <p style="margin-bottom:16px;color:#666">
                    Р’Р°С€Рё РґР°РЅРЅС‹Рµ РїСЂРµРґР·Р°РїРѕР»РЅРµРЅС‹ РёР· РїСЂРѕС„РёР»СЏ. Р’С‹Р±РµСЂРёС‚Рµ С‚Р°СЂРёС„ Рё РѕС‚РїСЂР°РІСЊС‚Рµ Р·Р°СЏРІРєСѓ.
                </p>
                <form method="POST" action="/join/">
                    <input type="hidden" name="join_action" value="1">
                    <input type="hidden" name="membership_type" value="basic" id="membership_type">
                    <div class="account__chapter">
                        <h3 class="account__subtitle">Р›РёС‡РЅС‹Рµ РґР°РЅРЅС‹Рµ</h3>
                    </div>
                    <div class="join__grid">
                        <input type="text" name="last_name"   placeholder="Р¤Р°РјРёР»РёСЏ"
                               value="<?= htmlspecialchars($arCurrentUser['LAST_NAME'] ?? '') ?>">
                        <input type="text" name="first_name"  placeholder="РРјСЏ"
                               value="<?= htmlspecialchars($arCurrentUser['NAME'] ?? '') ?>">
                        <input type="text" name="second_name" placeholder="РћС‚С‡РµСЃС‚РІРѕ"
                               value="<?= htmlspecialchars($arCurrentUser['SECOND_NAME'] ?? '') ?>">
                        <input type="text" name="telegram"    placeholder="Telegram"
                               value="<?= htmlspecialchars($arCurrentUser['UF_TELEGRAM'] ?? '') ?>">
                    </div>
                    <div class="account__graduate">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Р’С‹РїСѓСЃРєРЅРёРє РњР“РўРЈ?</h3>
                        </div>
                        <div class="account__graduate-choice">
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="yes" class="account__graduate-input"
                                       <?= !empty($arCurrentUser['UF_GRADUATE_YEAR']) ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>Р”Р°
                            </label>
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="no" class="account__graduate-input"
                                       <?= empty($arCurrentUser['UF_GRADUATE_YEAR']) ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>РќРµС‚
                            </label>
                        </div>
                    </div>
                    <!-- Р’С‹Р±РѕСЂ С‚Р°СЂРёС„Р° (Р°РІС‚РѕСЂРёР·РѕРІР°РЅРЅС‹Р№ РїРѕР»СЊР·РѕРІР°С‚РµР»СЊ) -->
                    <div class="account__chapter" style="margin-top:24px">
                        <h3 class="account__subtitle">Р’С‹Р±РµСЂРёС‚Рµ С‚Р°СЂРёС„</h3>
                    </div>
                    <div class="membership-slider swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide membership-slider__card">
                                <h3 class="membership-slider__title">Р‘Р°Р·РѕРІРѕРµ</h3>
                                <p class="membership-slider__name">5 000 Р </p>
                                <p class="membership-slider__time">РµР¶РµРіРѕРґРЅРѕ</p>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">Р’РѕР·РјРѕР¶РЅРѕСЃС‚СЊ СЂР°Р·РјРµС‰РµРЅРёСЏ СЂРµР·СЋРјРµ РЅР° РєР°СЂСЊРµСЂРЅРѕР№ РїР»Р°С‚С„РѕСЂРјРµ;</li>
                                    <li class="membership-slider__item">Р”РѕСЃС‚СѓРї РІ Р·Р°РєСЂС‹С‚С‹Р№ РєР°СЂСЊРµСЂРЅС‹Р№ РєР°РЅР°Р» СЃ РІР°РєР°РЅСЃРёСЏРјРё;</li>
                                    <li class="membership-slider__item">РЈС‡Р°СЃС‚РёРµ РІ Р°РєС‚РёРІРЅРѕСЃС‚СЏС… Рё РјРµСЂРѕРїСЂРёСЏС‚РёСЏС… РѕР±С‰РµСЃС‚РІР°;</li>
                                    <li class="membership-slider__item">Р”РѕСЃС‚СѓРї Рє РІРёС‚СЂРёРЅРµ РєРѕРјРїРµС‚РµРЅС†РёР№ РїР°СЂС‚РЅС‘СЂРѕРІ.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan btn--active" data-plan="basic">Р’С‹Р±СЂР°С‚СЊ</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
                                <h3 class="membership-slider__title">РџСЂРѕС„РµСЃСЃРёРѕРЅР°Р»СЊРЅРѕРµ</h3>
                                <p class="membership-slider__name">50 000 Р </p>
                                <p class="membership-slider__time">РµР¶РµРіРѕРґРЅРѕ</p>
                                <button class="membership-slider__advantages">+ Р’РѕР·РјРѕР¶РЅРѕСЃС‚Рё Р‘Р°Р·РѕРІРѕРіРѕ</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">РЈС‡Р°СЃС‚РёРµ РІ Р·Р°РєСЂС‹С‚РѕРј С‡Р°С‚Рµ С‡Р»РµРЅРѕРІ СѓСЂРѕРІРЅСЏ В«Р‘РёР·РЅРµСЃВ»;</li>
                                    <li class="membership-slider__item">Р Р°Р·РјРµС‰РµРЅРёРµ РёРЅС„РѕСЂРјР°С†РёРё Рѕ РєРѕРјРїР°РЅРёРё РЅР° РїР»РѕС‰Р°РґРєР°С… РѕР±С‰РµСЃС‚РІР°;</li>
                                    <li class="membership-slider__item">Р”РѕСЃС‚СѓРї Рє Р±Р°Р·Рµ СЂРµР·СЋРјРµ РІС‹РїСѓСЃРєРЅРёРєРѕРІ.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="premium">Р’С‹Р±СЂР°С‚СЊ</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--honorary">
                                <h3 class="membership-slider__title">РџР°СЂС‚РЅС‘СЂСЃРєРѕРµ</h3>
                                <p class="membership-slider__name membership-slider__name--small">РРЅРґРёРІРёРґСѓР°Р»СЊРЅС‹Рµ СѓСЃР»РѕРІРёСЏ</p>
                                <p class="membership-slider__time">РѕР±СЃСѓР¶РґР°РµС‚СЃСЏ РёРЅРґРёРІРёРґСѓР°Р»СЊРЅРѕ</p>
                                <button class="membership-slider__advantages">+ Р’РѕР·РјРѕР¶РЅРѕСЃС‚Рё РїСЂРѕС„РµСЃСЃРёРѕРЅР°Р»СЊРЅРѕРіРѕ</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">РЈС‡Р°СЃС‚РёРµ РІ Р·Р°РєСЂС‹С‚С‹С… РјРµСЂРѕРїСЂРёСЏС‚РёСЏС…;</li>
                                    <li class="membership-slider__item">РџСЂР°РІРѕ СЃС‚Р°С‚СЊ С‡Р»РµРЅРѕРј РїСЂР°РІР»РµРЅРёСЏ.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="partner">Р’С‹Р±СЂР°С‚СЊ</button>
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <div class="join__politic">
                        <div class="join__politic-question">
                            <p class="join__politic-link">
                                РћР·РЅР°РєРѕРјР»РµРЅ(Р°) Рё СЃРѕРіР»Р°СЃРµРЅ(Р°) СЃ <a href="#">РЈСЃС‚Р°РІРѕРј</a> Рё <a href="#">РџРѕР»РѕР¶РµРЅРёРµРј Рѕ С‡Р»РµРЅСЃРєРёС… РІР·РЅРѕСЃР°С…</a>
                            </p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Р”Р°
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>РќРµС‚
                                </label>
                            </div>
                        </div>
                        <div class="join__politic-question">
                            <p class="join__politic-link">РЎРѕРіР»Р°СЃРµРЅ СЃ <a href="#">РїРѕР»РёС‚РёРєРѕР№ РѕР±СЂР°Р±РѕС‚РєРё РџР”РЅ</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Р”Р°
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>РќРµС‚
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn">РџРѕРґР°С‚СЊ Р·Р°СЏРІРєСѓ</button>
                </form>
            </div>

            <?php else: ?>
            <!-- РЎС†РµРЅР°СЂРёР№ 1: РіРѕСЃС‚СЊ вЂ” РїРѕР»РЅР°СЏ С„РѕСЂРјР° СЂРµРіРёСЃС‚СЂР°С†РёРё -->
            <div class="join__wrapper">
                <h2 class="account__title main-title">Р’СЃС‚СѓРїРёС‚СЊ РІ РѕР±С‰РµСЃС‚РІРѕ</h2>
                <form method="POST" action="/join/" enctype="multipart/form-data">
                    <input type="hidden" name="join_action" value="1">
                    <input type="hidden" name="membership_type" value="basic" id="membership_type">
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Р›РёС‡РЅС‹Рµ РґР°РЅРЅС‹Рµ</h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="email" name="email"       placeholder="Р­Р»РµРєС‚СЂРѕРїРѕС‡С‚Р°" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <input type="password" name="password" placeholder="РџР°СЂРѕР»СЊ (РјРёРЅ. 6 СЃРёРјРІРѕР»РѕРІ)" required>
                            <input type="text" name="last_name"    placeholder="Р¤Р°РјРёР»РёСЏ" required
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                            <input type="text" name="first_name"   placeholder="РРјСЏ" required
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                            <input type="text" name="second_name"  placeholder="РћС‚С‡РµСЃС‚РІРѕ"
                                   value="<?= htmlspecialchars($_POST['second_name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__graduate">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Р’С‹РїСѓСЃРєРЅРёРє РњР“РўРЈ?</h3>
                        </div>
                        <div class="account__graduate-choice">
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="yes" class="account__graduate-input" id="grad-yes"
                                       <?= ($_POST['is_graduate'] ?? '') === 'yes' ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>Р”Р°
                            </label>
                            <label class="account__graduate-item">
                                <input type="radio" name="is_graduate" value="no"  class="account__graduate-input" id="grad-no"
                                       <?= ($_POST['is_graduate'] ?? 'no') !== 'yes' ? 'checked' : '' ?>>
                                <span class="account__graduate-box"></span>РќРµС‚
                            </label>
                        </div>
                    </div>
                    <div class="account__personal" id="graduate-data"
                         style="<?= ($_POST['is_graduate'] ?? 'no') !== 'yes' ? 'display:none' : '' ?>">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Р”Р°РЅРЅС‹Рµ РІС‹РїСѓСЃРєРЅРёРєР°</h3>
                        </div>
                        <div class="account__personal-list account__personal-list--short account__grid">
                            <input type="number" name="grad_year" placeholder="Р“РѕРґ РѕРєРѕРЅС‡Р°РЅРёСЏ" min="1900" max="2099"
                                   value="<?= (int)($_POST['grad_year'] ?? 0) ?: '' ?>">
                            <input type="text"   name="grad_dept" placeholder="Р’С‹РїСѓСЃРєР°СЋС‰Р°СЏ РєР°С„РµРґСЂР°"
                                   value="<?= htmlspecialchars($_POST['grad_dept'] ?? '') ?>">
                            <input type="text"   name="telegram"  placeholder="Telegram"
                                   value="<?= htmlspecialchars($_POST['telegram'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__personal" id="diploma-data"
                         style="<?= ($_POST['is_graduate'] ?? 'no') !== 'yes' ? 'display:none' : '' ?>">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">РЎРІРµРґРµРЅРёСЏ Рѕ РґРёРїР»РѕРјРµ</h3>
                        </div>
                        <div class="account__personal-list account__personal-list--short account__grid">
                            <input type="text" name="diploma_series" placeholder="РЎРµСЂРёСЏ Р±Р»Р°РЅРєР°"
                                   value="<?= htmlspecialchars($_POST['diploma_series'] ?? '') ?>">
                            <input type="text" name="diploma_number" placeholder="РќРѕРјРµСЂ Р±Р»Р°РЅРєР°"
                                   value="<?= htmlspecialchars($_POST['diploma_number'] ?? '') ?>">
                            <input type="text" name="diploma_date"   placeholder="Р”Р°С‚Р° РІС‹РґР°С‡Рё"
                                   value="<?= htmlspecialchars($_POST['diploma_date'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Р’С‹Р±РѕСЂ С‚Р°СЂРёС„Р° -->
                    <div class="membership-slider swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide membership-slider__card">
                                <h3 class="membership-slider__title">Р‘Р°Р·РѕРІРѕРµ</h3>
                                <p class="membership-slider__name">5 000 Р </p>
                                <p class="membership-slider__time">РµР¶РµРіРѕРґРЅРѕ</p>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">Р’РѕР·РјРѕР¶РЅРѕСЃС‚СЊ СЂР°Р·РјРµС‰РµРЅРёСЏ СЂРµР·СЋРјРµ РЅР° РєР°СЂСЊРµСЂРЅРѕР№ РїР»Р°С‚С„РѕСЂРјРµ;</li>
                                    <li class="membership-slider__item">Р”РѕСЃС‚СѓРї РІ Р·Р°РєСЂС‹С‚С‹Р№ РєР°СЂСЊРµСЂРЅС‹Р№ РєР°РЅР°Р» СЃ РІР°РєР°РЅСЃРёСЏРјРё;</li>
                                    <li class="membership-slider__item">РЈС‡Р°СЃС‚РёРµ РІ Р°РєС‚РёРІРЅРѕСЃС‚СЏС… Рё РјРµСЂРѕРїСЂРёСЏС‚РёСЏС… РѕР±С‰РµСЃС‚РІР°;</li>
                                    <li class="membership-slider__item">Р”РѕСЃС‚СѓРї Рє РІРёС‚СЂРёРЅРµ РєРѕРјРїРµС‚РµРЅС†РёР№ РїР°СЂС‚РЅС‘СЂРѕРІ.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="basic">Р’С‹Р±СЂР°С‚СЊ</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
                                <h3 class="membership-slider__title">РџСЂРѕС„РµСЃСЃРёРѕРЅР°Р»СЊРЅРѕРµ</h3>
                                <p class="membership-slider__name">50 000 Р </p>
                                <p class="membership-slider__time">РµР¶РµРіРѕРґРЅРѕ</p>
                                <button class="membership-slider__advantages">+ Р’РѕР·РјРѕР¶РЅРѕСЃС‚Рё Р‘Р°Р·РѕРІРѕРіРѕ</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">РЈС‡Р°СЃС‚РёРµ РІ Р·Р°РєСЂС‹С‚РѕРј С‡Р°С‚Рµ С‡Р»РµРЅРѕРІ СѓСЂРѕРІРЅСЏ В«Р‘РёР·РЅРµСЃВ»;</li>
                                    <li class="membership-slider__item">Р Р°Р·РјРµС‰РµРЅРёРµ РёРЅС„РѕСЂРјР°С†РёРё Рѕ РєРѕРјРїР°РЅРёРё РЅР° РїР»РѕС‰Р°РґРєР°С… РѕР±С‰РµСЃС‚РІР°;</li>
                                    <li class="membership-slider__item">Р”РѕСЃС‚СѓРї Рє Р±Р°Р·Рµ СЂРµР·СЋРјРµ РІС‹РїСѓСЃРєРЅРёРєРѕРІ.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="premium">Р’С‹Р±СЂР°С‚СЊ</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--honorary">
                                <h3 class="membership-slider__title">РџР°СЂС‚РЅС‘СЂСЃРєРѕРµ</h3>
                                <p class="membership-slider__name membership-slider__name--small">РРЅРґРёРІРёРґСѓР°Р»СЊРЅС‹Рµ СѓСЃР»РѕРІРёСЏ</p>
                                <p class="membership-slider__time">РѕР±СЃСѓР¶РґР°РµС‚СЃСЏ РёРЅРґРёРІРёРґСѓР°Р»СЊРЅРѕ</p>
                                <button class="membership-slider__advantages">+ Р’РѕР·РјРѕР¶РЅРѕСЃС‚Рё РїСЂРѕС„РµСЃСЃРёРѕРЅР°Р»СЊРЅРѕРіРѕ</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">РЈС‡Р°СЃС‚РёРµ РІ Р·Р°РєСЂС‹С‚С‹С… РјРµСЂРѕРїСЂРёСЏС‚РёСЏС…;</li>
                                    <li class="membership-slider__item">РџСЂР°РІРѕ СЃС‚Р°С‚СЊ С‡Р»РµРЅРѕРј РїСЂР°РІР»РµРЅРёСЏ.</li>
                                </ul>
                                <button type="button" class="membership-slider__join btn btn-empty select-plan" data-plan="partner">Р’С‹Р±СЂР°С‚СЊ</button>
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>

                    <div class="join__politic">
                        <div class="join__politic-question">
                            <p class="join__politic-link">
                                РћР·РЅР°РєРѕРјР»РµРЅ(Р°) Рё СЃРѕРіР»Р°СЃРµРЅ(Р°) СЃ <a href="#">РЈСЃС‚Р°РІРѕРј</a> Рё <a href="#">РџРѕР»РѕР¶РµРЅРёРµРј Рѕ С‡Р»РµРЅСЃРєРёС… РІР·РЅРѕСЃР°С…</a>
                            </p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Р”Р°
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>РќРµС‚
                                </label>
                            </div>
                        </div>
                        <div class="join__politic-question">
                            <p class="join__politic-link">РЎРѕРіР»Р°СЃРµРЅ СЃ <a href="#">РїРѕР»РёС‚РёРєРѕР№ РѕР±СЂР°Р±РѕС‚РєРё РџР”РЅ</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Р”Р°
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>РќРµС‚
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn">Р’СЃС‚СѓРїРёС‚СЊ</button>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </section>
    </section><!-- /join-fiz-block -->
</main>

<script>
// РџРµСЂРµРєР»СЋС‡Р°С‚РµР»СЊ Р¤РёР·. / Р®СЂ. Р»РёС†Рѕ
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
// Р•СЃР»Рё POST РІРµСЂРЅСѓР» d7_action вЂ” РїРѕРєР°Р·С‹РІР°РµРј СЋСЂ. Р±Р»РѕРє
<?php if (!empty($_POST['d7_action']) || $d7Done): ?>
po_switchJoinType('ur');
<?php endif; ?>

// РџРѕРєР°Р·Р°С‚СЊ/СЃРєСЂС‹С‚СЊ РїРѕР»СЏ РІС‹РїСѓСЃРєРЅРёРєР°
document.querySelectorAll('[name="is_graduate"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var show = this.value === 'yes';
        ['graduate-data','diploma-data'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.style.display = show ? '' : 'none';
        });
    });
});
// Р’С‹Р±РѕСЂ С‚Р°СЂРёС„Р°
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

