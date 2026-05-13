<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
$iblockOk = Loader::includeModule('iblock');

$formatRuProjectDate = static function (?string $rawDate): string {
    if (!$rawDate) {
        return '';
    }
    $ts = strtotime($rawDate);
    if (!$ts) {
        return '';
    }
    $months = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря',
    ];
    return date('d', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
};

$elementId = (int)($_GET['id'] ?? 0);
$elementCode = trim((string)($_REQUEST['ELEMENT_CODE'] ?? $_GET['code'] ?? ''));
$arElement = [];
$arProps   = [];

if ($iblockOk && ($elementId > 0 || ($elementCode !== '' && defined('IBLOCK_PROJECTS_ID') && IBLOCK_PROJECTS_ID > 0))) {
    if ($elementId > 0) {
        $dbEl = CIBlockElement::GetByID($elementId);
    } else {
        $dbEl = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => IBLOCK_PROJECTS_ID, '=CODE' => $elementCode, 'ACTIVE' => 'Y'],
            false,
            ['nTopCount' => 1],
            ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'DETAIL_PICTURE', 'PREVIEW_PICTURE', 'DATE_ACTIVE_FROM', 'TIMESTAMP_X', 'CODE']
        );
    }
    if ($arElement = $dbEl->GetNext()) {
        $elementId = (int)$arElement['ID'];
        $APPLICATION->SetTitle(htmlspecialchars($arElement['NAME']));
        $dbProps = CIBlockElement::GetProperty($arElement['IBLOCK_ID'], $elementId);
        while ($prop = $dbProps->Fetch()) {
            $arProps[$prop['CODE']] = $prop;
        }
    }
} else {
    $APPLICATION->SetTitle("Проект");
}

$detailPicSrc = SITE_TEMPLATE_PATH . '/assets/img/reference-page/banner-other-img.png';
if (!empty($arElement['DETAIL_PICTURE'])) {
    $dp = CFile::GetPath($arElement['DETAIL_PICTURE']);
    if ($dp) $detailPicSrc = $dp;
} elseif (!empty($arElement['PREVIEW_PICTURE'])) {
    $dp = CFile::GetPath($arElement['PREVIEW_PICTURE']);
    if ($dp) $detailPicSrc = $dp;
}
?>
<main>
    <!-- banner-other -->
    <section class="banner-other banner-project-current">
        <div class="container">
            <div class="banner-other__wrapper banner-other__wrapper--current">
                <div class="banner-other__content">
                    <div class="banner-other__info banner-other__info--current">
                        <?php
                        $projectStatusRaw = (string)($arProps['PROJECT_STATUS']['VALUE_ENUM'] ?? $arProps['PROJECT_STATUS']['VALUE'] ?? '');
                        $projectStatusNorm = mb_strtolower(trim($projectStatusRaw));
                        $statusMap = [
                            'active' => 'Активный',
                            'активный' => 'Активный',
                            'в работе' => 'Активный',
                            'активен' => 'Активный',
                            'completed' => 'Завершённый',
                            'завершённый' => 'Завершённый',
                            'завершенный' => 'Завершённый',
                            'archived' => 'Архивный',
                            'archive' => 'Архивный',
                            'архивный' => 'Архивный',
                        ];
                        $projectStatusView = $statusMap[$projectStatusNorm] ?? $projectStatusRaw;
                        $projectDateView = $formatRuProjectDate($arElement['DATE_ACTIVE_FROM'] ?? '');
                        if (!empty($projectStatusView)):
                        ?>
                        <div class="banner-other__date">
                            <p class="banner-other__status"><?= htmlspecialchars($projectStatusView) ?></p>
                            <?php if ($projectDateView !== ''): ?>
                            <p class="banner-other__time"><span>Дата:</span> <?= htmlspecialchars($projectDateView) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <h1 class="banner-other__title main-title">
                            <?= $arElement ? htmlspecialchars($arElement['NAME']) : 'Проект не найден' ?>
                        </h1>
                        <?php
                        $projectSubtitle = trim((string)($arProps['PROJECT_SUBTITLE']['VALUE'] ?? ''));
                        if ($projectSubtitle === '' && !empty($arElement['PREVIEW_TEXT'])) {
                            $projectSubtitle = trim((string)$arElement['PREVIEW_TEXT']);
                        }
                        ?>
                        <?php if ($projectSubtitle !== ''): ?>
                        <p class="banner-other__text main-text" style="margin-top:16px;white-space:pre-line;line-height:1.25;"><?= htmlspecialchars($projectSubtitle) ?></p>
                        <?php endif; ?>
                        <?php if ($projectStatusNorm === '' || in_array($projectStatusNorm, ['active', 'активный', 'в работе', 'активен'])): ?>
                        <a href="/support/?project=<?= urlencode($arElement['NAME']) ?>" class="btn" style="margin-top:24px;">Поддержать</a>
                        <?php endif; ?>
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                </div>
                <img src="<?= htmlspecialchars($detailPicSrc) ?>" alt="" class="banner-other__image banner-other__image--current">
            </div>
        </div>
    </section>

    <?php if ($arElement): ?>
    <section class="project-help">
        <div class="container">
            <h2 class="main-title project-help__title">Проекту необходима финансовая поддержка</h2>
            <p class="project-help__text main-text">
                Мы рады любой помощи вне зависимости от её размера. Для компаний желающих стать спонсорами либо участниками данного проекта - просим связаться с нами.
</p>
            <button class="btn project-help__btn" data-fancybox data-src="#form-finance-help">Связаться с организаторами</button>
        </div>
    </section>

    <!-- Детальный текст -->
    <?php if (!empty($arProps['PROJECT_AMOUNT']['VALUE'])): ?>
    <section class="project-programm">
        <div class="container">
            <p style="margin-bottom:24px;font-size:20px;font-weight:600">
                Цель сбора: <?= htmlspecialchars($arProps['PROJECT_AMOUNT']['VALUE']) ?>
            </p>
        </div>
    </section>
    <?php endif; ?>

    <?php
    $detailHtml = (string)($arElement['DETAIL_TEXT'] ?? '');
    if ($detailHtml !== '') {
        // Legacy static markup may contain links like support.html or absolute .../support.html.
        $detailHtml = preg_replace(
            '#href=(["\'])(?:https?://[^"\']+)?(?:/projects/[^"\']+)?/support\.html(?:\?[^"\']*)?\1#iu',
            'href="/support/"',
            $detailHtml
        );
        $detailHtml = preg_replace('#https?://[^"\']+/support\.html(?:\?[^"\']*)?#iu', '/support/', $detailHtml);
        $detailHtml = preg_replace('#(^|[/"\'])support\.html(?:\?[^"\']*)?([/"\']|$)#iu', '$1/support/$2', $detailHtml);
        $detailHtml = str_ireplace(
            ['href="support.html"', "href='support.html'"],
            ['href="/support/"', "href='/support/'"],
            $detailHtml
        );

        // Remove template-only sections from iblock HTML to avoid duplicate layout/buttons.
        $detailHtml = preg_replace(
            '#<section[^>]*class=(["\'])[^"\']*\bbanner-other\b[^"\']*\1[^>]*>.*?</section>#isu',
            '',
            $detailHtml
        );
        $detailHtml = preg_replace(
            '#<section[^>]*class=(["\'])[^"\']*\bbutton-help\b[^"\']*\1[^>]*>.*?</section>#isu',
            '',
            $detailHtml
        );
        $detailHtml = preg_replace(
            '#<section[^>]*class=(["\'])[^"\']*(?:^|\s)project-help(?:\s|$)[^"\']*\1[^>]*>.*?</section>#isu',
            '',
            $detailHtml
        );
    }
    ?>
    <?= $detailHtml ?>

    <section class="project-programm" style="background:transparent;">
        <div class="container">
            <p style="margin-top:24px">
                <?php
                $projectStatus = $projectStatusNorm;
                $isActiveProject = in_array($projectStatus, ['active', 'активный', 'в работе', 'активен']);
                ?>
                <?php if ($isActiveProject || empty($projectStatus)): ?>
                <a href="/support/?project=<?= urlencode($arElement['NAME']) ?>" class="btn">
                    Поддержать проект
                </a>
                <?php endif; ?>
                <?php
                $secondBtnHref = '/projects/';
                $secondBtnText = 'Вернуться к проектам';
                if (!empty($arProps['PROJECT_LINK']['VALUE'])) {
                    $secondBtnHref = (string)$arProps['PROJECT_LINK']['VALUE'];
                    $secondBtnText = 'Подробнее о проекте';
                }
                ?>
                <a href="<?= htmlspecialchars($secondBtnHref) ?>" class="btn btn-transparent"<?= $secondBtnText === 'Подробнее о проекте' ? ' target="_blank"' : '' ?> style="margin-left:12px">
                    <?= htmlspecialchars($secondBtnText) ?>
                </a>
            </p>
        </div>
    </section>
    <?php else: ?>
    <section><div class="container" style="padding:60px 0">
        <p>Проект не найден. <a href="/projects/">← Вернуться к списку</a></p>
    </div></section>
    <?php endif; ?>
</main>

<?php if ($arElement): ?>
<?php
$_projName  = htmlspecialchars($arElement['NAME'], ENT_QUOTES);
$_projDescSource = trim((string)($arProps['PROJECT_SUBTITLE']['VALUE'] ?? ''));
if ($_projDescSource === '') {
    $_projDescSource = (string)($arElement['PREVIEW_TEXT'] ?? '');
}
$_projDesc  = htmlspecialchars(strip_tags($_projDescSource), ENT_QUOTES);
$_projUrl   = !empty($arElement['CODE'])
    ? 'https://bauman-polytech.ru/projects/' . rawurlencode($arElement['CODE']) . '/'
    : 'https://bauman-polytech.ru/projects/detail/?id=' . $arElement['ID'];
$_projDate  = !empty($arElement['DATE_ACTIVE_FROM']) ? date('c', strtotime($arElement['DATE_ACTIVE_FROM'])) : date('c', strtotime($arElement['TIMESTAMP_X']));
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CreativeWork",
  "name": "<?= $_projName ?>",
  "description": "<?= $_projDesc ?>",
  "dateCreated": "<?= $_projDate ?>",
  "url": "<?= $_projUrl ?>",
  "creator": {
    "@type": "Organization",
    "name": "Политехническое общество выпускников МГТУ им. Н.Э. Баумана",
    "url": "https://bauman-polytech.ru"
  }
}
</script>
<?php endif; ?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>