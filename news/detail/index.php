<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
$iblockOk = Loader::includeModule('iblock');
$hlOk     = Loader::includeModule('highloadblock');

$elementId   = (int)($_GET['id'] ?? 0);
$arElement   = [];
$arProps     = [];
$isEvent     = false;
$regDone     = false;
$regError    = '';

// — Загрузка элемента инфоблока —
if ($iblockOk && $elementId > 0) {
    $dbEl = CIBlockElement::GetByID($elementId);
    if ($arElement = $dbEl->GetNext()) {
        $APPLICATION->SetTitle(htmlspecialchars($arElement['NAME']));
        // Определяем: это событие?
        $isEvent = defined('IBLOCK_EVENTS_ID') && IBLOCK_EVENTS_ID > 0
                   && (int)$arElement['IBLOCK_ID'] === IBLOCK_EVENTS_ID;
        // Свойства
        $dbProps = CIBlockElement::GetProperty($arElement['IBLOCK_ID'], $elementId);
        while ($prop = $dbProps->Fetch()) {
            $arProps[$prop['CODE']] = $prop;
        }
    }
} else {
    $APPLICATION->SetTitle('Новость');
}

// — D3: Обработка регистрации на событие —
$hlApplications = null;
if ($isEvent && $hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
    $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
    if ($hlEntity) {
        $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['event_reg_action'])) {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName  = trim($_POST['last_name']  ?? '');
            $email     = trim($_POST['email']      ?? '');
            $phone     = trim($_POST['phone']      ?? '');
            $telegram  = trim($_POST['telegram']   ?? '');

            if (!$firstName || !$lastName || !$email) {
                $regError = 'Заполните обязательные поля: Имя, Фамилия, Электропочта';
            } else {
                $data = json_encode([
                    'last_name'  => $lastName,
                    'first_name' => $firstName,
                    'email'      => $email,
                    'phone'      => $phone,
                    'telegram'   => $telegram,
                ], JSON_UNESCAPED_UNICODE);

                $res = $hlClass::add([
                    'UF_USER_ID'    => $USER->IsAuthorized() ? (int)$USER->GetID() : 0,
                    'UF_TYPE'       => 'event_reg',
                    'UF_STATUS'     => 'new',
                    'UF_DATE_CREATE'=> new \Bitrix\Main\Type\DateTime(),
                    'UF_DATA'       => $data,
                    'UF_ELEMENT_ID' => $elementId,
                ]);
                if ($res->isSuccess()) {
                    $regDone = true;
                    po_logAction('form_submit', 'application', $elementId, 'D3 регистрация на событие: ' . ($arElement['NAME'] ?? $elementId));
                    $d3Data = [
                        'first_name' => $firstName, 'last_name' => $lastName,
                        'email'      => $email,     'phone'     => $phone,
                        'telegram'   => $telegram,  'event'     => $arElement['NAME'] ?? $elementId,
                    ];
                    po_sendAdminEmail('event_reg', $d3Data);
                    po_createCrmLead('event_reg', $d3Data);
                } else {
                    $regError = implode('; ', $res->getErrorMessages());
                }
            }
        }
    }
}

$detailPicSrc = SITE_TEMPLATE_PATH . '/assets/img/single-news-page/img-news-main.png';
if (!empty($arElement['DETAIL_PICTURE'])) {
    $dp = CFile::GetPath($arElement['DETAIL_PICTURE']);
    if ($dp) $detailPicSrc = $dp;
} elseif (!empty($arElement['PREVIEW_PICTURE'])) {
    $dp = CFile::GetPath($arElement['PREVIEW_PICTURE']);
    if ($dp) $detailPicSrc = $dp;
}

$dateStr = '';
if (!empty($arElement['DATE_ACTIVE_FROM'])) {
    $dateStr = date('d.m.Y', strtotime($arElement['DATE_ACTIVE_FROM']));
}
?>
<main>
    <section class="breadcrumbs">
        <div class="container">
            <ul>
                <li><a href="/news/">Новости и события</a></li>
                <?php if ($arElement): ?>
                <li><a href="#"><?= htmlspecialchars($arElement['NAME']) ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </section>

    <section class="single-news">
        <div class="container">
            <?php if (empty($arElement)): ?>
                <p>Материал не найден. <a href="/news/">← Вернуться к списку</a></p>
            <?php else: ?>
            <img src="<?= htmlspecialchars($detailPicSrc) ?>" alt="">
            <div class="single-news__wrapper">
                <div class="single-news__left">
                    <?php if ($dateStr): ?>
                    <p class="single-news__discription"><?= $dateStr ?></p>
                    <?php endif; ?>
                    <h2 class="single-news__title main-title">
                        <?= htmlspecialchars($arElement['NAME']) ?>
                    </h2>

                    <?php if (!empty($arElement['PREVIEW_TEXT'])): ?>
                    <p style="margin-bottom:48px"><?= $arElement['PREVIEW_TEXT'] ?></p>
                    <?php endif; ?>

                    <?php if (!empty($arProps['EVENT_DATE']['VALUE'])): ?>
                    <p><strong>Дата события:</strong> <?= htmlspecialchars($arProps['EVENT_DATE']['VALUE']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($arProps['EVENT_LOCATION']['VALUE'])): ?>
                    <p><strong>Место:</strong> <?= htmlspecialchars($arProps['EVENT_LOCATION']['VALUE']) ?></p>
                    <?php endif; ?>

                    <div class="single-news__detail-text">
                        <?= $arElement['DETAIL_TEXT'] ?>
                    </div>
                </div>
            </div>

            <?php if ($isEvent): ?>
            <!-- D3: Форма регистрации на событие -->
            <div class="account__block" style="margin-top:60px">
                <h3 class="account__subtitle">Зарегистрироваться на событие</h3>
                <?php if ($regDone): ?>
                    <div class="authorization__alert authorization__alert--success" style="margin:16px 0">
                        <p>Вы успешно зарегистрированы на событие!</p>
                    </div>
                <?php elseif ($regError): ?>
                    <div class="authorization__alert authorization__alert--error" style="margin:16px 0">
                        <p><?= htmlspecialchars($regError) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!$regDone): ?>
                <form method="POST" action="">
                    <input type="hidden" name="event_reg_action" value="1">
                    <div class="account__personal-list account__grid" style="margin-top:16px">
                        <input type="text"  name="last_name"  placeholder="Фамилия *" required
                               value="<?= $USER->IsAuthorized() ? htmlspecialchars($USER->GetParam('LAST_NAME')) : '' ?>">
                        <input type="text"  name="first_name" placeholder="Имя *" required
                               value="<?= $USER->IsAuthorized() ? htmlspecialchars($USER->GetParam('NAME')) : '' ?>">
                        <input type="email" name="email"      placeholder="Электропочта *" required
                               value="<?= $USER->IsAuthorized() ? htmlspecialchars($USER->GetParam('EMAIL')) : '' ?>">
                        <input type="tel"   name="phone"      placeholder="Телефон"
                               value="">
                        <input type="text"  name="telegram"   placeholder="Telegram">
                    </div>
                    <button type="submit" class="btn authorization__btn" style="margin-top:16px">Зарегистрироваться</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </section>
</main>

<?php if ($arElement): ?>
<?php
$_schemaType   = $isEvent ? 'Event' : 'NewsArticle';
$_schemaName   = htmlspecialchars($arElement['NAME'], ENT_QUOTES);
$_schemaDesc   = htmlspecialchars(strip_tags($arElement['PREVIEW_TEXT'] ?? ''), ENT_QUOTES);
$_schemaDate   = !empty($arElement['DATE_ACTIVE_FROM']) ? date('c', strtotime($arElement['DATE_ACTIVE_FROM'])) : date('c', strtotime($arElement['TIMESTAMP_X']));
$_schemaUrl    = 'https://bauman-polytech.ru/news/detail/?id=' . $arElement['ID'];
if ($isEvent):
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Event",
  "name": "<?= $_schemaName ?>",
  "description": "<?= $_schemaDesc ?>",
  "startDate": "<?= $_schemaDate ?>",
  "url": "<?= $_schemaUrl ?>",
  "organizer": {
    "@type": "Organization",
    "name": "Политехническое общество выпускников МГТУ им. Н.Э. Баумана",
    "url": "https://bauman-polytech.ru"
  },
  "location": {
    "@type": "Place",
    "name": "Москва",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "Москва",
      "addressCountry": "RU"
    }
  }
}
</script>
<?php else: ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "<?= $_schemaName ?>",
  "description": "<?= $_schemaDesc ?>",
  "datePublished": "<?= $_schemaDate ?>",
  "url": "<?= $_schemaUrl ?>",
  "publisher": {
    "@type": "Organization",
    "name": "Политехническое общество выпускников МГТУ им. Н.Э. Баумана",
    "url": "https://bauman-polytech.ru"
  }
}
</script>
<?php endif; ?>
<?php endif; ?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
