<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новости и события");
$APPLICATION->SetPageProperty('description', 'Новости и события Политехнического общества выпускников МГТУ им. Н.Э. Баумана: конференции, встречи, лекции и другие мероприятия.');

use Bitrix\Main\Loader;
$iblockOk = Loader::includeModule('iblock');

// ?type=news | events | (all by default)
$typeFilter = $_GET['type'] ?? 'all';
if (!in_array($typeFilter, ['news', 'events', 'all'])) {
    $typeFilter = 'all';
}

// Собираем ID нужных инфоблоков
$iblockIds = [];
if ($iblockOk) {
    if ($typeFilter === 'all' || $typeFilter === 'news') {
        if (defined('IBLOCK_NEWS_ID') && IBLOCK_NEWS_ID > 0)
            $iblockIds[] = IBLOCK_NEWS_ID;
    }
    if ($typeFilter === 'all' || $typeFilter === 'events') {
        if (defined('IBLOCK_EVENTS_ID') && IBLOCK_EVENTS_ID > 0)
            $iblockIds[] = IBLOCK_EVENTS_ID;
    }
}

$filterLabels = ['all' => 'Все', 'news' => 'Новости', 'events' => 'События'];
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

            <!-- Фильтр по типу -->
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:32px;">
                <?php foreach ($filterLabels as $key => $label): ?>
                <a href="/news/?type=<?= $key ?>"
                   class="btn <?= $typeFilter === $key ? '' : 'btn-transparent' ?>"
                   style="<?= $typeFilter === $key ? '' : 'opacity:0.7;' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>

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
                        $isEvent = defined('IBLOCK_EVENTS_ID') && (int)$row['IBLOCK_ID'] === IBLOCK_EVENTS_ID;
                ?>
                <a href="<?= $link ?>" class="news__card">
                    <?php if ($isEvent): ?>
                    <span class="news__badge" style="position:absolute;top:10px;left:10px;background:#e31e24;color:#fff;font-size:11px;padding:2px 8px;border-radius:4px;">Событие</span>
                    <?php endif; ?>
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
