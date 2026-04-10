<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');
Loader::includeModule('iblock');

$_userGroups = $USER->IsAuthorized() ? $USER->GetUserGroupArray() : [];
$_isMember   = defined('PO_MEMBER_BASIC_ID') && (
    in_array(PO_MEMBER_BASIC_ID,   $_userGroups) ||
    in_array(PO_MEMBER_PREMIUM_ID, $_userGroups) ||
    in_array(PO_PARTNER_ID,        $_userGroups)
);

// D4: Заявка на участие в референс-визите (только члены)
$d4Done = false; $d4Error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d4_action'])) {
    if (!$_isMember) {
        $d4Error = 'Участие в референс-визитах доступно только членам общества.';
    } else {
        $fn        = trim($_POST['first_name'] ?? '');
        $ln        = trim($_POST['last_name']  ?? '');
        $em        = trim($_POST['email']      ?? '');
        $ph        = trim($_POST['phone']      ?? '');
        $visitName = trim($_POST['visit_name'] ?? '');
        if (!$fn || !$ln || !$em) {
            $d4Error = 'Заполните обязательные поля: Имя, Фамилия, e-mail.';
        } else {
            $saved = false;
            if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
                $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
                if ($hlEntity) {
                    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                    $res = $hlClass::add([
                        'UF_USER_ID'     => (int)($USER->IsAuthorized() ? $USER->GetID() : 0),
                        'UF_TYPE'        => 'reference_visit',
                        'UF_STATUS'      => 'new',
                        'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                        'UF_DATA'        => json_encode([
                            'last_name'  => $ln,  'first_name' => $fn,
                            'email'      => $em,  'phone'      => $ph,
                            'telegram'   => trim($_POST['telegram'] ?? ''),
                            'visit_name' => $visitName,
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
                    $saved = $res->isSuccess();
                }
            }
            if (!$hlOk || $saved) {
                $d4Done = true;
                if (function_exists('po_logAction'))
                    po_logAction('form_submit', 'application', 0, 'D4 участие в референс-визите: ' . $visitName);
                $d4Data = [
                    'first_name'  => $fn, 'last_name'  => $ln,
                    'email'       => $em, 'phone'      => $ph,
                    'telegram'    => trim($_POST['telegram'] ?? ''),
                    'visit_name'  => $visitName,
                ];
                if (function_exists('po_sendAdminEmail'))  po_sendAdminEmail('reference_visit', $d4Data);
                if (function_exists('po_createCrmLead'))   po_createCrmLead('reference_visit', $d4Data);
            } else {
                $d4Error = 'Ошибка сохранения. Попробуйте позже.';
            }
        }
    }
}

// Получить элемент по символьному коду (ChPU) или ID
$arElement = null;
$elementCode = trim($_GET['code'] ?? '');
$elementId   = (int)($_GET['id'] ?? 0);

if (defined('IBLOCK_REFERENCE_ID') && IBLOCK_REFERENCE_ID > 0) {
    $filter = ['IBLOCK_ID' => IBLOCK_REFERENCE_ID, 'ACTIVE' => 'Y'];
    if ($elementCode !== '')   $filter['CODE'] = $elementCode;
    elseif ($elementId > 0)    $filter['ID']   = $elementId;
    else                       LocalRedirect('/reference/');

    $db = CIBlockElement::GetList(
        [], $filter, false, false,
        ['ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE']
    );
    $arElement = $db->GetNext();
    if (!$arElement) LocalRedirect('/reference/');

    $dbProps = CIBlockElement::GetProperty(
        IBLOCK_REFERENCE_ID, $arElement['ID'], 'sort', 'asc', []
    );
    $props = [];
    $detailCodes = ['REF_STATUS', 'REF_DATE', 'REF_LOCATION', 'REF_DURATION', 'REF_SUBTITLE'];
    while ($p = $dbProps->Fetch()) {
        if (!in_array($p['CODE'], $detailCodes)) continue;
        $props[$p['CODE']] = $p['VALUE_ENUM'] ?: $p['VALUE'];
    }
} else {
    LocalRedirect('/reference/');
}

$APPLICATION->SetTitle(htmlspecialchars($arElement['NAME']));

$refStatus   = htmlspecialchars($props['REF_STATUS']   ?? '');
$refDate     = htmlspecialchars($props['REF_DATE']     ?? '');
$refLocation = htmlspecialchars($props['REF_LOCATION'] ?? '');
$refDuration = htmlspecialchars($props['REF_DURATION'] ?? '');
$refSubtitle = trim($props['REF_SUBTITLE'] ?? '');

$bannerImg = '';
if (!empty($arElement['DETAIL_PICTURE']))       $bannerImg = CFile::GetPath($arElement['DETAIL_PICTURE']);
elseif (!empty($arElement['PREVIEW_PICTURE']))  $bannerImg = CFile::GetPath($arElement['PREVIEW_PICTURE']);
if (!$bannerImg) $bannerImg = SITE_TEMPLATE_PATH . '/assets/img/reference-page/reference-cemat-mash.png';

$detailHtml = (string)($arElement['DETAIL_TEXT'] ?? '');

// Автозаполнение из профиля
$userFirstName = $USER->IsAuthorized() ? htmlspecialchars($USER->GetParam('NAME'))       : '';
$userLastName  = $USER->IsAuthorized() ? htmlspecialchars($USER->GetParam('LAST_NAME'))  : '';
$userEmail     = $USER->IsAuthorized() ? htmlspecialchars($USER->GetParam('EMAIL'))      : '';
$userPhone     = $USER->IsAuthorized() ? htmlspecialchars($USER->GetParam('PERSONAL_PHONE')) : '';
?>

<main>
    <!-- banner-other -->
    <section class="banner-other">
        <div class="container">
            <div class="banner-other__wrapper">
                <div class="banner-other__content">
                    <div class="banner-other__info">
                        <div class="banner-other__date banner-other__date--column">
                            <?php if ($refStatus): ?>
                            <p class="banner-other__status"><?= $refStatus ?></p>
                            <?php endif; ?>
                            <?php if ($refDate): ?>
                            <p class="banner-other__time"><?= $refDate ?></p>
                            <?php endif; ?>
                            <?php if ($refLocation): ?>
                            <p class="banner-other__time"><?= $refLocation ?></p>
                            <?php endif; ?>
                            <?php if ($refDuration): ?>
                            <p class="banner-other__time"><?= $refDuration ?></p>
                            <?php endif; ?>
                        </div>
                        <h1 class="banner-other__title main-title">
                            <?= htmlspecialchars($arElement['NAME']) ?>
                        </h1>
                        <?php if ($refSubtitle !== ''): ?>
                        <p class="banner-other__text main-text" style="white-space:pre-line;">
                            <?= htmlspecialchars($refSubtitle) ?>
                        </p>
                        <?php elseif (!empty($arElement['PREVIEW_TEXT'])): ?>
                        <p class="banner-other__text main-text">
                            <?= nl2br(htmlspecialchars($arElement['PREVIEW_TEXT'])) ?>
                        </p>
                        <?php endif; ?>
                        <?php if ($_isMember): ?>
                        <a href="#" class="banner-other__btn btn" data-fancybox data-src="#form-d4-visit">Зарегистрироваться</a>
                        <?php else: ?>
                        <a href="/join/?back=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="banner-other__btn btn">Зарегистрироваться</a>
                        <?php endif; ?>
                    </div>
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                </div>
                <img src="<?= htmlspecialchars($bannerImg) ?>" alt="" class="banner-other__image">
            </div>
        </div>
    </section>
    <!-- /.banner-other -->

    <?php if ($detailHtml): ?>
    <?= $detailHtml ?>
    <?php endif; ?>

    <!-- Нижняя навигация -->
    <div class="button-help" style="background:transparent">
        <a href="/reference/" class="btn btn-empty">← Вернуться к визитам</a>
    </div>

</main>

<!-- D4: Форма заявки на участие в референс-визите -->
<div class="form-d4-visit" id="form-d4-visit" style="display:none;">
    <div class="join__wrapper">
        <?php if ($d4Done): ?>
            <h2 class="account__title main-title">Заявка принята!</h2>
            <p style="margin-top:16px">Мы свяжемся с вами для подтверждения участия.</p>
        <?php else: ?>
        <h2 class="account__title main-title">Заявка на участие в референс-визите</h2>
        <p class="main-text" style="margin-top:8px;margin-bottom:20px;opacity:.7">
            <?= htmlspecialchars($arElement['NAME']) ?>
        </p>
        <?php if ($d4Error): ?>
        <div class="authorization__alert authorization__alert--error" style="margin:12px 0">
            <p><?= htmlspecialchars($d4Error) ?></p>
        </div>
        <?php endif; ?>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>#form-d4-visit" id="form-d4-visit-form">
            <input type="hidden" name="d4_action" value="1">
            <input type="hidden" name="visit_name" value="<?= htmlspecialchars($arElement['NAME']) ?>">
            <div class="account__personal">
                <div class="account__chapter">
                    <h3 class="account__subtitle">Данные об участнике</h3>
                </div>
                <div class="account__personal-list account__grid" style="margin-top:16px">
                    <input type="text"  name="last_name"  placeholder="Фамилия *" required
                           value="<?= $userLastName ?>"  <?= $USER->IsAuthorized() ? 'readonly' : '' ?>>
                    <input type="text"  name="first_name" placeholder="Имя *" required
                           value="<?= $userFirstName ?>" <?= $USER->IsAuthorized() ? 'readonly' : '' ?>>
                    <input type="email" name="email"      placeholder="e-mail *" required
                           value="<?= $userEmail ?>"     <?= $USER->IsAuthorized() ? 'readonly' : '' ?>>
                    <input type="tel"   name="phone"      placeholder="Телефон *" required
                           value="<?= $userPhone ?>"     <?= ($USER->IsAuthorized() && $userPhone) ? 'readonly' : '' ?>>
                    <input type="text"  name="telegram"   placeholder="Telegram (необязательно)">
                </div>
            </div>
            <div class="join__politic" style="margin-top:16px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="d4_agree" value="1" required>
                    <span>Ознакомлен с политикой обработки ПДн</span>
                </label>
            </div>
            <p class="form-required-note">* Обязательные поля</p>
            <button type="submit" class="btn authorization__btn" style="margin-top:16px">Отправить заявку</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($d4Done): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.Fancybox) Fancybox.show([{src: '#form-d4-visit', type: 'inline'}]);
});
</script>
<?php endif; ?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
