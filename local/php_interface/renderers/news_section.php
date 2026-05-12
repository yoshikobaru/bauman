<?php
/**
 * Рендерит секцию "Новости и события"
 * 
 * Использует инфоблоки IBLOCK_NEWS_ID и IBLOCK_EVENTS_ID
 * 
 * Подключение: require_once dirname(__DIR__) . '/renderers/news_section.php';
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('po_render_news_section')) {
    /**
     * Выводит HTML-разметку секции "Новости и события"
     * 
     * @param string $title Заголовок секции
     * @param int $limit Лимит новостей
     * @param string $showAllText Текст кнопки "Все новости"
     */
    function po_render_news_section(
        string $title = 'Новости и события', 
        int $limit = 6,
        string $showAllText = 'Все новости'
    ): void 
    {
        $newsItems = [];

        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockIds = [];
            
            if (defined('IBLOCK_NEWS_ID') && IBLOCK_NEWS_ID > 0) {
                $iblockIds[] = IBLOCK_NEWS_ID;
            }
            if (defined('IBLOCK_EVENTS_ID') && IBLOCK_EVENTS_ID > 0) {
                $iblockIds[] = IBLOCK_EVENTS_ID;
            }

            if (!empty($iblockIds)) {
                $dbNews = CIBlockElement::GetList(
                    ['DATE_ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'],
                    ['IBLOCK_ID' => $iblockIds, 'ACTIVE' => 'Y'],
                    false,
                    ['nTopCount' => $limit],
                    ['ID', 'NAME', 'DATE_ACTIVE_FROM', 'PREVIEW_PICTURE', 'IBLOCK_ID']
                );

                while ($item = $dbNews->GetNext()) {
                    $img = $item['PREVIEW_PICTURE']
                        ? CFile::GetPath($item['PREVIEW_PICTURE'])
                        : SITE_TEMPLATE_PATH . '/assets/img/news-img.png';
                    
                    $date = $item['DATE_ACTIVE_FROM']
                        ? date('d.m.Y', strtotime($item['DATE_ACTIVE_FROM']))
                        : '';

                    $newsItems[] = [
                        'id' => (int)$item['ID'],
                        'name' => (string)$item['NAME'],
                        'img' => $img,
                        'date' => $date,
                    ];
                }
            }
        }
        ?>
        <section class="news">
        <div class="container">
            <h2 class="main-title news__title"><?= htmlspecialchars($title) ?></h2>
            <div class="news__wrapper">
                <?php if (!empty($newsItems)): ?>
                    <?php foreach ($newsItems as $item): ?>
                    <a href="/news/detail/?id=<?= $item['id'] ?>" class="news__card">
                        <img alt="<?= htmlspecialchars($item['name']) ?>" 
                             src="<?= htmlspecialchars($item['img']) ?>">
                        <div class="news__content">
                            <h3 class="news__card-title"><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="news__row">
                                <p class="news__date"><?= htmlspecialchars($item['date']) ?></p>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="news__empty" style="color:#888;padding:20px;">Новости скоро появятся</p>
                <?php endif; ?>
            </div>
            <a href="/news/" class="btn news__btn btn-transparent"><?= htmlspecialchars($showAllText) ?></a>
        </div>
        </section>
        <?php
    }
}