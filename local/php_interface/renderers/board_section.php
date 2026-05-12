<?php
/**
 * Рендерит секцию "Члены Совета Политехнического общества"
 * 
 * Использует инфоблок IBLOCK_BOARD_ID
 * Данные: ID, NAME, PREVIEW_PICTURE, PREVIEW_TEXT
 * 
 * Подключение: require_once dirname(__DIR__) . '/renderers/board_section.php';
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('po_render_board_section')) {
    /**
     * Выводит HTML-разметку секции "Члены Совета"
     *
     * @param string $title Заголовок секции (по умолчанию: "Члены Совета Политехнического общества")
     * @param int $limit Лимит элементов (по умолчанию: 12)
     * @param bool $useFancybox Включить data-fancybox для модальных окон (по умолчанию: false)
     */
    function po_render_board_section(
        string $title = 'Члены Совета Политехнического общества',
        int $limit = 12,
        bool $useFancybox = false
    ): void
    {
        $items = [];
        
        // Получаем ID элементов если нужен fancybox
        $elementIds = [];
        
        // Получаем данные из инфоблока
        if (defined('IBLOCK_BOARD_ID') && IBLOCK_BOARD_ID > 0 && \Bitrix\Main\Loader::includeModule('iblock')) {
            $dbBoard = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                ['IBLOCK_ID' => IBLOCK_BOARD_ID, 'ACTIVE' => 'Y'],
                false,
                ['nTopCount' => $limit],
                ['ID', 'NAME', 'PREVIEW_PICTURE', 'PREVIEW_TEXT']
            );

            while ($board = $dbBoard->GetNext()) {
                $items[] = [
                    'id' => (int)$board['ID'],
                    'name' => (string)$board['NAME'],
                    'pos' => (string)$board['PREVIEW_TEXT'],
                    'photo' => !empty($board['PREVIEW_PICTURE'])
                        ? CFile::GetPath($board['PREVIEW_PICTURE'])
                        : '',
                ];
                if ($useFancybox) {
                    $elementIds[] = (int)$board['ID'];
                }
            }
        }
        
        // Если данных нет — ничего не выводим
        if (empty($items)) {
            return;
        }
        ?>
        <section class="boards">
        <div class="container">
            <div class="boards__wrapper">
                <h2 class="main-title"><?= htmlspecialchars($title) ?></h2>
                <div class="boards__list">
                    <?php foreach ($items as $item): ?>
                    <div class="boards__item"<?= $useFancybox ? ' data-fancybox data-src="#board-modal-' . $item['id'] . '"' : '' ?>>
                        <?php if (!empty($item['photo'])): ?>
                        <img alt="<?= htmlspecialchars($item['name']) ?>"
                             src="<?= htmlspecialchars($item['photo']) ?>"
                             class="boards__item-image">
                        <?php endif; ?>
                        <h3 class="boards__item-title"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="boards__item-text"><?= htmlspecialchars($item['pos']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        </section>
        <?php
    }
}