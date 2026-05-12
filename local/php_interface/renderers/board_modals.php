<?php
/**
 * Рендерит модальные окна для членов Совета
 * 
 * Использует инфоблок IBLOCK_BOARD_ID
 * Данные: ID, NAME, PREVIEW_PICTURE, PREVIEW_TEXT, DETAIL_TEXT
 * 
 * Подключение: require_once dirname(__DIR__) . '/renderers/board_modals.php';
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('po_render_board_modals')) {
    /**
     * Выводит HTML-разметку модальных окон для членов Совета
     * Используется с data-fancybox для открытия при клике на карточку
     */
    function po_render_board_modals(): void 
    {
        $members = [];

        if (defined('IBLOCK_BOARD_ID') && IBLOCK_BOARD_ID > 0 && \Bitrix\Main\Loader::includeModule('iblock')) {
            $dbBoard = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                ['IBLOCK_ID' => IBLOCK_BOARD_ID, 'ACTIVE' => 'Y'],
                false, false,
                ['ID', 'NAME', 'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'DETAIL_TEXT']
            );

            while ($bm = $dbBoard->GetNext()) {
                $members[] = $bm;
            }
        }

        if (empty($members)) {
            return;
        }

        foreach ($members as $bm): 
            $photoSrc = !empty($bm['PREVIEW_PICTURE'])
                ? CFile::GetPath($bm['PREVIEW_PICTURE'])
                : SITE_TEMPLATE_PATH . '/assets/img/board-placeholder.png';
        ?>
        <div class="form-boards" id="board-modal-<?= (int)$bm['ID'] ?>" style="display:none;max-width:1100px;">
            <div class="form-boards__wrapper">
                <img alt="<?= htmlspecialchars($bm['NAME']) ?>" 
                     src="<?= htmlspecialchars($photoSrc) ?>" 
                     class="form-boards__image">
                <div class="form-boards__content">
                    <h2><?= htmlspecialchars($bm['NAME']) ?></h2>
                    <?php if (!empty($bm['DETAIL_TEXT'])): ?>
                    <div><?= $bm['DETAIL_TEXT'] ?></div>
                    <?php else: ?>
                    <p><?= htmlspecialchars($bm['PREVIEW_TEXT']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php 
        endforeach;
    }
}