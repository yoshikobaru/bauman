<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проекты");

use Bitrix\Main\Loader;
$iblockOk = Loader::includeModule('iblock');

$dbProjects = null;
if ($iblockOk && defined('IBLOCK_PROJECTS_ID') && IBLOCK_PROJECTS_ID > 0) {
    $dbProjects = CIBlockElement::GetList(
        ['SORT' => 'ASC'],
        ['IBLOCK_ID' => IBLOCK_PROJECTS_ID, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID', 'NAME', 'CODE', 'DATE_ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL']
    );
}
?>
<main>
    <!-- banner-other -->
    <section class="banner-other banner-other-project">
        <div class="container">
            <div class="banner-other__wrapper">
                <div class="banner-other__content">
                    <div class="banner-other__info">
                        <h1 class="banner-other__title main-title">
                            Инициативные резиденты сообщества запустили знаковые проекты Политеха
                        </h1>
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                </div>
                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-img.png" alt="" class="banner-other__image">
            </div>
        </div>
    </section>

    <!-- visits / проекты из CMS -->
    <section class="visits">
        <div class="container">
            <h2 class="main-title visits__title">Текущие проекты</h2>
            <div class="visits__list">
                <?php if ($dbProjects):
                    $hasItems = false;
                    while ($row = $dbProjects->GetNext()):
                        $hasItems = true;
                        $imgSrc = SITE_TEMPLATE_PATH . '/assets/img/projects-page/current-project-img-1.png';
                        if (!empty($row['PREVIEW_PICTURE'])) {
                            $img = CFile::GetPath($row['PREVIEW_PICTURE']);
                            if ($img) $imgSrc = $img;
                        }
                        $link = '/projects/detail/?id=' . (int)$row['ID'];
                ?>
                <div class="visits__card">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" class="visits__image">
                    <div class="visits__content">
                        <?php if (!empty($row['DATE_ACTIVE_FROM'])): ?>
                        <div class="visits__date">
                            <div class="visits__date-current">
                                <p><span>Дата:</span> <?= date('d F Y', strtotime($row['DATE_ACTIVE_FROM'])) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <h3 class="visits__subtitle"><?= htmlspecialchars($row['NAME']) ?></h3>
                        <?php if (!empty($row['PREVIEW_TEXT'])): ?>
                        <p class="visits__text"><?= htmlspecialchars($row['PREVIEW_TEXT']) ?></p>
                        <?php endif; ?>
                        <div class="visits__buttons">
                            <a href="/support/" class="btn visits__btn visits__btn--help">Поддержать</a>
                            <a href="<?= $link ?>" class="btn visits__btn btn-transparent">Подробнее</a>
                        </div>
                    </div>
                </div>
                <?php endwhile;
                    if (!$hasItems): ?>
                    <p style="color:#888">Проекты появятся здесь после добавления в административной панели.</p>
                <?php endif;
                else: ?>
                    <p style="color:#888">Контент из CMS недоступен — настройте инфоблоки в <code>local/php_interface/init.php</code>.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
