<?php
/**
 * Рендерит страницу "Новости и события"
 * 
 * Использует инфоблоки IBLOCK_NEWS_ID и IBLOCK_EVENTS_ID
 * Поддерживает фильтрацию: ?type=news|events|all, ?when=upcoming|past
 * 
 * Подключение: подключается автоматически через init.php
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('po_render_news_page')) {
    /**
     * Выводит HTML-разметку страницы "Новости и события"
     * 
     * @param string $pageTitle Заголовок страницы
     */
    function po_render_news_page(
        string $pageTitle = 'Новости и события'
    ): void 
    {
        // Параметры фильтрации из URL
        $typeFilter = $_GET['type'] ?? 'all';
        if (!in_array($typeFilter, ['news', 'events', 'all'])) {
            $typeFilter = 'all';
        }

        $whenFilter = $_GET['when'] ?? 'upcoming';
        if (!in_array($whenFilter, ['upcoming', 'past'])) {
            $whenFilter = 'upcoming';
        }
        $today = date('Y-m-d');

        // Собираем ID нужных инфоблоков
        $iblockIds = [];
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            if ($typeFilter === 'all' || $typeFilter === 'news') {
                if (defined('IBLOCK_NEWS_ID') && IBLOCK_NEWS_ID > 0) {
                    $iblockIds[] = IBLOCK_NEWS_ID;
                }
            }
            if ($typeFilter === 'all' || $typeFilter === 'events') {
                if (defined('IBLOCK_EVENTS_ID') && IBLOCK_EVENTS_ID > 0) {
                    $iblockIds[] = IBLOCK_EVENTS_ID;
                }
            }
        }

        // Строим фильтр по дате для событий
        $arDateFilter = ['ACTIVE' => 'Y', 'IBLOCK_ID' => $iblockIds];
        if ($typeFilter === 'events') {
            if ($whenFilter === 'upcoming') {
                $arDateFilter['>=DATE_ACTIVE_FROM'] = $today;
            } else {
                $arDateFilter['<DATE_ACTIVE_FROM'] = $today;
            }
        }

        $filterLabels = ['all' => 'Все', 'news' => 'Новости', 'events' => 'События'];
        $iblockOk = !empty($iblockIds);
        $newsItems = [];

        if ($iblockOk) {
            $sortOrder = ($typeFilter === 'events' && $whenFilter === 'upcoming')
                ? ['DATE_ACTIVE_FROM' => 'ASC', 'ID' => 'ASC']
                : ['DATE_ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'];
            
            $dbItems = CIBlockElement::GetList(
                $sortOrder,
                $arDateFilter,
                false,
                ['nPageSize' => 12],
                ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'DATE_ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE']
            );

            while ($row = $dbItems->GetNext()) {
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

                $newsItems[] = [
                    'link' => $link,
                    'is_event' => $isEvent,
                    'img' => $imgSrc,
                    'name' => $row['NAME'],
                    'date' => $date,
                ];
            }
        }
        ?>

    <section class="news news-page">
        <div class="container">
            <h2 class="main-title news__title"><?= htmlspecialchars($pageTitle) ?></h2>

            <!-- Фильтр по типу -->
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
                <?php foreach ($filterLabels as $key => $label): ?>
                <a href="/news/?type=<?= $key ?><?= $key === 'events' ? '&when=' . $whenFilter : '' ?>"
                   class="btn <?= $typeFilter === $key ? '' : 'btn-transparent' ?>"
                   style="<?= $typeFilter === $key ? '' : 'opacity:0.7;' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Фильтр предстоящие/прошедшие (только для событий) -->
            <?php if ($typeFilter === 'events'): ?>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:28px;">
                <a href="/news/?type=events&when=upcoming"
                   class="btn <?= $whenFilter === 'upcoming' ? '' : 'btn-empty' ?>"
                   style="font-size:13px;padding:8px 18px">Предстоящие</a>
                <a href="/news/?type=events&when=past"
                   class="btn <?= $whenFilter === 'past' ? '' : 'btn-empty' ?>"
                   style="font-size:13px;padding:8px 18px">Прошедшие</a>
            </div>
            <?php endif; ?>

            <div class="news__wrapper">
                <?php if ($iblockOk && !empty($newsItems)): ?>
                    <?php foreach ($newsItems as $item): ?>
                    <a href="<?= htmlspecialchars($item['link']) ?>" class="news__card">
                        <?php if ($item['is_event']): ?>
                        <span class="news__badge" style="position:absolute;top:10px;left:10px;background:#e31e24;color:#fff;font-size:11px;padding:2px 8px;border-radius:4px;">Событие</span>
                        <?php endif; ?>
                        <img src="<?= htmlspecialchars($item['img']) ?>" alt="">
                        <div class="news__content">
                            <h3 class="news__card-title"><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="news__row">
                                <?php if ($item['date']): ?>
                                <p class="news__date"><?= htmlspecialchars($item['date']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php elseif (!$iblockOk): ?>
                    <p style="color:#888">Контент из CMS недоступен — настройте инфоблоки в <code>local/php_interface/init.php</code>.</p>
                <?php else: ?>
                    <p style="color:#888">Публикации появятся здесь после добавления в административной панели.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
        <?php
    }
}
