<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новости и события");

use Bitrix\Main\Loader;
$iblockOk = Loader::includeModule('iblock');

// Собираем ID активных инфоблоков
$iblockIds = [];
if ($iblockOk) {
    foreach (['IBLOCK_NEWS_ID', 'IBLOCK_EVENTS_ID'] as $c) {
        if (defined($c) && constant($c) > 0) {
            $iblockIds[] = constant($c);
        }
    }
}
?>
<main>
    <section class="breadcrumbs">
        <div class="container">
            <ul>
                <li><a href="/">Главная</a></li>
                <li><a href="/news/">Новости и события</a></li>
            </ul>
        </div>
    </section>

    <section class="news news-page">
        <div class="container">
            <h2 class="main-title news__title">Новости и события</h2>
            <div class="news__wrapper">
                <?php if ($iblockOk && !empty($iblockIds)):
                    $dbItems = CIBlockElement::GetList(
                        ['DATE_ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'],
                        ['IBLOCK_ID' => $iblockIds, 'ACTIVE' => 'Y'],
                        false,
                        ['nPageSize' => 12],
                        ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'DATE_ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE']
                    );
                    $hasItems = false;
                    while ($row = $dbItems->GetNext()):
                        $hasItems = true;
                        $imgSrc = SITE_TEMPLATE_PATH . '/assets/img/news-img.png';
                        if (!empty($row['PREVIEW_PICTURE'])) {
                            $imgFile = CFile::GetPath($row['PREVIEW_PICTURE']);
                            if ($imgFile) $imgSrc = $imgFile;
                        }
                        $date = !empty($row['DATE_ACTIVE_FROM'])
                            ? date('d.m.Y', strtotime($row['DATE_ACTIVE_FROM']))
                            : '';
                        $link = '/news/detail/?id=' . (int)$row['ID'];
                ?>
                <a href="<?= $link ?>" class="news__card">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="">
                    <div class="news__content">
                        <h3 class="news__card-title"><?= htmlspecialchars($row['NAME']) ?></h3>
                        <div class="news__row">
                            <?php if ($date): ?>
                            <p class="news__date"><?= $date ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endwhile;
                    if (!$hasItems): ?>
                    <p style="color:#888">Публикации появятся здесь после добавления в административной панели.</p>
                <?php endif;
                else: ?>
                    <p style="color:#888">Контент из CMS недоступен — настройте инфоблоки в <code>local/php_interface/init.php</code>.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
