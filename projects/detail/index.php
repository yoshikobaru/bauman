<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
$iblockOk = Loader::includeModule('iblock');

$elementId = (int)($_GET['id'] ?? 0);
$arElement = [];
$arProps   = [];

if ($iblockOk && $elementId > 0) {
    $dbEl = CIBlockElement::GetByID($elementId);
    if ($arElement = $dbEl->GetNext()) {
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
                        <?php if (!empty($arProps['PROJECT_STATUS']['VALUE'])): ?>
                        <div class="banner-other__date">
                            <p class="banner-other__status"><?= htmlspecialchars($arProps['PROJECT_STATUS']['VALUE']) ?></p>
                            <?php if (!empty($arElement['DATE_ACTIVE_FROM'])): ?>
                            <p class="banner-other__time"><span>Запущен</span> <?= date('d F Y', strtotime($arElement['DATE_ACTIVE_FROM'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <h1 class="banner-other__title main-title">
                            <?= $arElement ? htmlspecialchars($arElement['NAME']) : 'Проект не найден' ?>
                        </h1>
                        <?php if (!empty($arElement['PREVIEW_TEXT'])): ?>
                        <p style="margin-top:16px"><?= htmlspecialchars($arElement['PREVIEW_TEXT']) ?></p>
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
    <section class="project-programm">
        <div class="container">
            <div class="project-programm__wrapper">
                <div class="project-programm__preview">
                    <?php if (!empty($arProps['PROJECT_AMOUNT']['VALUE'])): ?>
                    <h2 class="project-programm__preview-title">
                        Цель сбора: <?= htmlspecialchars($arProps['PROJECT_AMOUNT']['VALUE']) ?>
                    </h2>
                    <?php endif; ?>
                </div>
                <div style="max-width:700px">
                    <?= $arElement['DETAIL_TEXT'] ?>
                    <?php if (!empty($arProps['PROJECT_LINK']['VALUE'])): ?>
                    <p style="margin-top:24px">
                        <a href="<?= htmlspecialchars($arProps['PROJECT_LINK']['VALUE']) ?>" target="_blank" class="btn">
                            Подробнее о проекте
                        </a>
                    </p>
                    <?php endif; ?>
                    <p style="margin-top:24px">
                        <a href="/support/" class="btn">Поддержать проект</a>
                        <a href="/projects/" class="btn btn-transparent" style="margin-left:12px">← К списку проектов</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
    <?php else: ?>
    <section><div class="container" style="padding:60px 0">
        <p>Проект не найден. <a href="/projects/">← Вернуться к списку</a></p>
    </div></section>
    <?php endif; ?>
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
