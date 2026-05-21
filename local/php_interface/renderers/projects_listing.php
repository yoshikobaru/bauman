<?php
/**
 * Рендерит страницу "Проекты" со списком проектов
 * 
 * Использует инфоблок IBLOCK_PROJECTS_ID
 * Поддерживает фильтрацию: ?status=active|completed|all
 * 
 * Подключение: подключается автоматически через init.php
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('po_render_projects_listing')) {
    /**
     * Форматирует дату проекта в русский формат
     */
    function po_format_project_date(?string $rawDate): string {
        if (!$rawDate) return '';
        $ts = strtotime($rawDate);
        if (!$ts) return '';
        $months = [
            1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
            5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря',
        ];
        return date('d', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    }

    /**
     * Выводит HTML-разметку страницы "Проекты"
     * 
     * @param string $pageTitle Заголовок страницы
     */
    function po_render_projects_listing(
        string $pageTitle = 'Проекты общества'
    ): void 
    {
        // Параметр фильтрации
        $statusFilter = $_GET['status'] ?? 'all';
        if (!in_array($statusFilter, ['active', 'completed', 'all'])) {
            $statusFilter = 'all';
        }

        $statusLabels = ['all' => 'Все', 'active' => 'Активные', 'completed' => 'Завершённые'];

        // Fallback статичные проекты
        $staticProjects = [
            ['name' => 'PolytechExpo',             'text' => 'Ежегодная конференция выпускников и партнёров МГТУ им. Н.Э. Баумана', 'url' => '/projects/politech-expo/',  'detail_url' => '/projects/politech-expo/',  'img' => '/assets/img/projects-page/current-project-img-1.png'],
            ['name' => 'Встреча выпускников',      'text' => 'Традиционная встреча выпускников всех поколений Бауманки',            'url' => '/projects/conference/',    'detail_url' => '/projects/conference/',    'img' => '/assets/img/projects-page/current-project-img-2.png'],
            ['name' => 'Попечительский совет МТ4', 'text' => 'Поддержка развития кафедры МТ4 МГТУ им. Н.Э. Баумана',              'url' => '/projects/trustees/',      'detail_url' => '/projects/trustees/',      'donation_name' => 'Попечительский совет МТ4', 'img' => '/assets/img/projects-page/current-project-img-3.png'],
            ['name' => 'Реставрация ротонды',      'text' => 'Проект по восстановлению исторической ротонды МГТУ',                  'url' => '/projects/restoration/',   'detail_url' => '/projects/restoration/',   'donation_name' => 'Реставрация Ротонды',      'img' => '/assets/img/projects-page/current-project-img-4.png'],
        ];
        foreach ($staticProjects as &$sp) {
            $sp['support_link'] = function_exists('po_support_page_url_for_project')
                ? po_support_page_url_for_project([
                    'name' => $sp['name'],
                    'detail_url' => $sp['detail_url'] ?? '',
                    'donation_name' => $sp['donation_name'] ?? '',
                ])
                : '/support/?project=' . rawurlencode($sp['donation_name'] ?? $sp['name']);
        }
        unset($sp);

        $projects = [];
        $hasItems = false;

        // Получаем проекты из инфоблока
        if (\Bitrix\Main\Loader::includeModule('iblock') && defined('IBLOCK_PROJECTS_ID') && IBLOCK_PROJECTS_ID > 0) {
            $arFilter = ['IBLOCK_ID' => IBLOCK_PROJECTS_ID, 'ACTIVE' => 'Y'];
            if ($statusFilter !== 'all') {
                $arFilter['PROPERTY_PROJECT_STATUS'] = $statusFilter;
            }
            
            $dbProjects = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                $arFilter,
                false,
                false,
                ['ID', 'NAME', 'CODE', 'DATE_ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL', 'PROPERTY_DETAIL_URL']
            );

            while ($row = $dbProjects->GetNext()) {
                $hasItems = true;
                $imgSrc = SITE_TEMPLATE_PATH . '/assets/img/projects-page/current-project-img-1.png';
                if (!empty($row['PREVIEW_PICTURE'])) {
                    $img = CFile::GetPath($row['PREVIEW_PICTURE']);
                    if ($img) $imgSrc = $img;
                }
                $detailUrl = !empty($row['PROPERTY_DETAIL_URL_VALUE'])
                    ? (string)$row['PROPERTY_DETAIL_URL_VALUE']
                    : '';
                $link = $detailUrl !== ''
                    ? $detailUrl
                    : (!empty($row['CODE']) ? '/projects/' . rawurlencode($row['CODE']) . '/' : '/projects/detail/?id=' . (int)$row['ID']);

                $formattedDate = po_format_project_date($row['DATE_ACTIVE_FROM'] ?? '');
                $supportLink = function_exists('po_support_page_url_for_project')
                    ? po_support_page_url_for_project([
                        'id' => (int)$row['ID'],
                        'name' => (string)$row['NAME'],
                        'code' => (string)($row['CODE'] ?? ''),
                        'detail_url' => $detailUrl !== '' ? $detailUrl : $link,
                    ])
                    : '/support/?project=' . rawurlencode((string)$row['NAME']);

                $projects[] = [
                    'img' => $imgSrc,
                    'date' => $formattedDate,
                    'name' => $row['NAME'],
                    'text' => $row['PREVIEW_TEXT'] ?? '',
                    'link' => $link,
                    'support_link' => $supportLink,
                ];
            }
        }
        ?>

            <!-- visits / проекты из CMS -->
            <section class="visits">
                <div class="container">
                    <h2 class="main-title visits__title"><?= htmlspecialchars($pageTitle) ?></h2>

                    <!-- Фильтр по статусу -->
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:32px;">
                        <?php foreach ($statusLabels as $key => $label): ?>
                        <a href="/projects/?status=<?= $key ?>"
                           class="btn <?= $statusFilter === $key ? '' : 'btn-transparent' ?>"
                           style="<?= $statusFilter === $key ? '' : 'opacity:0.7;' ?>">
                            <?= $label ?>
                        </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="visits__list">
                        <?php if ($hasItems): ?>
                            <?php foreach ($projects as $proj): ?>
                        <div class="visits__card">
                            <img src="<?= htmlspecialchars($proj['img']) ?>" alt="<?= htmlspecialchars($proj['name']) ?>" class="visits__image">
                            <div class="visits__content">
                                <?php if (!empty($proj['date'])): ?>
                                <p class="visits__text" style="opacity: 0.5; margin-bottom: 8px;"><?= htmlspecialchars($proj['date']) ?></p>
                                <?php endif; ?>
                                <h3 class="visits__subtitle"><?= htmlspecialchars($proj['name']) ?></h3>
                                <?php if (!empty($proj['text'])): ?>
                                <p class="visits__text"><?= htmlspecialchars($proj['text']) ?></p>
                                <?php endif; ?>
                                <div class="visits__buttons">
                                    <a href="<?= htmlspecialchars($proj['link']) ?>" class="btn visits__btn btn-transparent">Подробнее</a>
                                    <a href="<?= htmlspecialchars($proj['support_link'] ?? '/support/') ?>" class="btn visits__btn visits__btn--help">Поддержать</a>
                                </div>
                            </div>
                        </div>
                            <?php endforeach; ?>
                        <?php elseif ($statusFilter !== 'completed'): ?>
                            <?php foreach ($staticProjects as $sp): ?>
                        <div class="visits__card">
                            <img src="<?= htmlspecialchars($sp['img']) ?>" alt="<?= htmlspecialchars($sp['name']) ?>" class="visits__image">
                            <div class="visits__content">
                                <h3 class="visits__subtitle"><?= htmlspecialchars($sp['name']) ?></h3>
                                <p class="visits__text"><?= htmlspecialchars($sp['text']) ?></p>
                                <div class="visits__buttons">
                                    <a href="<?= htmlspecialchars($sp['url']) ?>" class="btn visits__btn btn-transparent">Подробнее</a>
                                    <a href="<?= htmlspecialchars($sp['support_link'] ?? '/support/') ?>" class="btn visits__btn visits__btn--help">Поддержать</a>
                                </div>
                            </div>
                        </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($statusFilter === 'completed' && !$hasItems): ?>
                    <p style="color:#888;margin-top:12px">Завершённых проектов пока нет.</p>
                    <?php endif; ?>
                </div>
            </section>
        <?php
    }
}
