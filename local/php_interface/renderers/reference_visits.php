<?php
/**
 * Рендерит секцию "Визиты" на странице reference
 * 
 * Использует инфоблок IBLOCK_REFERENCE_ID
 * Поддерживает фильтрацию: ?visits_tab=active|past
 * 
 * Подключение: подключается автоматически через init.php
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('po_render_reference_visits')) {
    /**
     * Выводит HTML-разметку секции "Визиты"
     * 
     * @param string $sectionTitle Заголовок секции
     */
    function po_render_reference_visits(
        string $sectionTitle = 'Визиты'
    ): void 
    {
        $arActiveVisits = [];
        $arPastVisits   = [];

        if (\Bitrix\Main\Loader::includeModule('iblock') && defined('IBLOCK_REFERENCE_ID') && IBLOCK_REFERENCE_ID > 0) {
            $dbRef = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                ['IBLOCK_ID' => IBLOCK_REFERENCE_ID, 'ACTIVE' => 'Y'],
                false, false,
                ['ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'PREVIEW_PICTURE']
            );
            while ($el = $dbRef->GetNext()) {
                $dbP = CIBlockElement::GetProperty(
                    IBLOCK_REFERENCE_ID, $el['ID'], 'sort', 'asc', []
                );
                $elProps = []; $statusXml = '';
                $refCodes = ['REF_STATUS', 'REF_DATE', 'REF_LOCATION', 'REF_DURATION', 'REF_REGISTER_URL'];
                while ($p = $dbP->Fetch()) {
                    if (!in_array($p['CODE'], $refCodes)) continue;
                    $elProps[$p['CODE']] = $p['VALUE_ENUM'] ?: $p['VALUE'];
                    if ($p['CODE'] === 'REF_STATUS') $statusXml = $p['VALUE_XML_ID'] ?? '';
                }
                $el['_PROPS']      = $elProps;
                $el['_STATUS_XML'] = $statusXml;
                if ($statusXml === 'completed') {
                    $arPastVisits[]   = $el;
                } else {
                    $arActiveVisits[] = $el;
                }
            }
        }

        $visitsTab = $_GET['visits_tab'] ?? 'active';
        $hasAny = !empty($arActiveVisits) || !empty($arPastVisits);
        ?>

        <section class="visits">
            <div class="container">
                <h2 class="main-title visits__title"><?= htmlspecialchars($sectionTitle) ?></h2>
                <?php if ($hasAny): ?>
                <div style="display:flex;gap:12px;margin-bottom:32px;flex-wrap:wrap;margin-top:24px">
                    <a href="?visits_tab=active" class="btn <?= $visitsTab !== 'active' ? 'btn-empty' : '' ?>"
                       style="padding:10px 24px">Активные (<?= count($arActiveVisits) ?>)</a>
                    <a href="?visits_tab=past" class="btn <?= $visitsTab !== 'past' ? 'btn-empty' : '' ?>"
                       style="padding:10px 24px">Завершённые (<?= count($arPastVisits) ?>)</a>
                </div>
                <?php $visitsToShow = ($visitsTab === 'past') ? $arPastVisits : $arActiveVisits; ?>
                <?php if (empty($visitsToShow)): ?>
                <p style="color:#888">
                    <?= $visitsTab === 'past' ? 'Завершённых визитов пока нет.' : 'Активных визитов пока нет.' ?>
                </p>
                <?php else: ?>
                <div class="visits__list">
                    <?php foreach ($visitsToShow as $visit):
                        $vProps   = $visit['_PROPS'] ?? [];
                        $imgSrc   = !empty($visit['PREVIEW_PICTURE'])
                            ? CFile::GetPath($visit['PREVIEW_PICTURE'])
                            : SITE_TEMPLATE_PATH . '/assets/img/reference-page/reference-main-img-1.png';
                        $detailUrl = '/reference/' . ($visit['CODE'] ?: $visit['ID']) . '/';
                    ?>
                    <div class="visits__card">
                        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($visit['NAME']) ?>" class="visits__image">
                        <div class="visits__content">
                            <div class="visits__date">
                                <div class="visits__date-current">
                                    <?php if (!empty($vProps['REF_DATE'])): ?>
                                    <p><span>Дата: </span><?= htmlspecialchars($vProps['REF_DATE']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($vProps['REF_LOCATION'])): ?>
                                    <p><?= htmlspecialchars($vProps['REF_LOCATION']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($vProps['REF_DURATION'])): ?>
                                    <p><?= htmlspecialchars($vProps['REF_DURATION']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <h3 class="visits__subtitle"><?= htmlspecialchars($visit['NAME']) ?></h3>
                            <?php if (!empty($visit['PREVIEW_TEXT'])): ?>
                            <p class="visits__text"><?= htmlspecialchars($visit['PREVIEW_TEXT']) ?></p>
                            <?php endif; ?>
                            <a href="<?= htmlspecialchars($detailUrl) ?>" class="btn visits__btn btn-transparent">Подробнее</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <p style="color:#888;margin-top:16px">Визиты пока не добавлены.</p>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
