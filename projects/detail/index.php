<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
$iblockOk = Loader::includeModule('iblock');

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
    $APPLICATION->SetTitle('Проект');
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
                        if (!empty($projectStatusView)):
                        ?>
                        <div class="banner-other__date">
                            <p class="banner-other__status"><?= htmlspecialchars($projectStatusView) ?></p>
                            <?php if (!empty($arElement['DATE_ACTIVE_FROM'])): ?>
                            <p class="banner-other__time"><span>Запущен</span> <?= date('d F Y', strtotime($arElement['DATE_ACTIVE_FROM'])) ?></p>
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
                        <p class="banner-other__text main-text" style="margin-top:16px"><?= htmlspecialchars($projectSubtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                </div>
                <img src="<?= htmlspecialchars($detailPicSrc) ?>" alt="" class="banner-other__image banner-other__image--current">
            </div>
        </div>
    </section>

    <?php if ($arElement): ?>
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

    <?= $arElement['DETAIL_TEXT'] ?>

    <section class="project-programm">
        <div class="container">
            <?php if (!empty($arProps['PROJECT_LINK']['VALUE'])): ?>
            <p style="margin-top:24px">
                <a href="<?= htmlspecialchars($arProps['PROJECT_LINK']['VALUE']) ?>" target="_blank" class="btn">
                    Подробнее о проекте
                </a>
            </p>
            <?php endif; ?>
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
                <a href="/projects/" class="btn btn-transparent" style="margin-left:12px">← К списку проектов</a>
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
