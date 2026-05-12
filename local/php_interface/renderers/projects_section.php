<?php
/**
 * Рендерит секцию "Проекты общества"
 * 
 * Использует инфоблок IBLOCK_PROJECTS_ID
 * Свойства: PROPERTY_HOME_IMAGE, PROPERTY_HOME_IMAGE_MOB, PROPERTY_DETAIL_URL
 * 
 * Подключение: require_once dirname(__DIR__) . '/renderers/projects_section.php';
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('po_render_projects_section')) {
    /**
     * Выводит HTML-разметку секции "Проекты общества"
     * 
     * @param string $title Заголовок секции
     * @param string $subtitle Подзаголовок
     */
    function po_render_projects_section(
        string $title = 'Проекты общества',
        string $subtitle = 'Члены общества запустили важные проекты Политеха. Станьте частью братства и используйте все возможности общества.'
    ): void 
    {
        // Fallback данные (если инфоблок пуст)
        $fallbackProjects = [
            [
                'name' => 'Конференция PolytechExpo', 
                'url' => '/projects/politech-expo/', 
                'img' => 'initiative-img-1.png', 
                'mob' => 'initiative-img-mob-1.png'
            ],
            [
                'name' => 'Конференция Встреча выпускников', 
                'url' => '/projects/conference/', 
                'img' => 'initiative-img-2.png', 
                'mob' => 'initiative-img-mob-2.png'
            ],
            [
                'name' => 'Попечительский совет', 
                'url' => '/projects/trustees/', 
                'img' => 'initiative-img-3.png', 
                'mob' => 'initiative-img-mob-3.png'
            ],
            [
                'name' => 'Реставрации Ротонды', 
                'url' => '/projects/restoration/', 
                'img' => 'initiative-img-4.png', 
                'mob' => 'initiative-img-mob-4.png'
            ],
        ];

        $projects = [];
        $desktopFallback = ['initiative-img-1.png', 'initiative-img-2.png', 'initiative-img-3.png', 'initiative-img-4.png'];
        $mobileFallback = ['initiative-img-mob-1.png', 'initiative-img-mob-2.png', 'initiative-img-mob-3.png', 'initiative-img-mob-4.png'];

        // Получаем данные из инфоблока
        if (defined('IBLOCK_PROJECTS_ID') && IBLOCK_PROJECTS_ID > 0 && \Bitrix\Main\Loader::includeModule('iblock')) {
            $dbProjects = CIBlockElement::GetList(
                ['SORT' => 'ASC', 'ID' => 'ASC'],
                ['IBLOCK_ID' => IBLOCK_PROJECTS_ID, 'ACTIVE' => 'Y'],
                false, false,
                ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PROPERTY_HOME_IMAGE', 
                 'PROPERTY_HOME_IMAGE_MOB', 'PROPERTY_DETAIL_URL']
            );
            $idx = 0;
            
            while ($proj = $dbProjects->GetNext()) {
                $homeImageId = (int)($proj['PROPERTY_HOME_IMAGE_VALUE'] ?? 0);
                $homeMobId = (int)($proj['PROPERTY_HOME_IMAGE_MOB_VALUE'] ?? 0);
                $previewId = (int)($proj['PREVIEW_PICTURE'] ?? 0);

                // Desktop image
                $desktopImg = '';
                foreach ([$homeImageId, $previewId] as $cid) {
                    if ($cid > 0) {
                        $path = CFile::GetPath($cid);
                        if ($path) { 
                            $desktopImg = $path; 
                            break; 
                        }
                    }
                }
                if (!$desktopImg) {
                    $desktopImg = SITE_TEMPLATE_PATH . '/assets/img/' . $desktopFallback[$idx % 4];
                }

                // Mobile image
                $mobileImg = '';
                foreach ([$homeMobId, $homeImageId, $previewId] as $cid) {
                    if ($cid > 0) {
                        $path = CFile::GetPath($cid);
                        if ($path) { 
                            $mobileImg = $path; 
                            break; 
                        }
                    }
                }
                if (!$mobileImg) {
                    $mobileImg = SITE_TEMPLATE_PATH . '/assets/img/' . $mobileFallback[$idx % 4];
                }

                // URL
                $detailUrl = trim((string)($proj['PROPERTY_DETAIL_URL_VALUE'] ?? ''));
                if (!$detailUrl) {
                    $detailUrl = trim((string)($proj['DETAIL_PAGE_URL'] ?? ''));
                }
                if (!$detailUrl) {
                    $detailUrl = '/projects/detail/?id=' . (int)$proj['ID'];
                }

                $projects[] = [
                    'name' => (string)$proj['NAME'],
                    'url' => $detailUrl,
                    'img' => $desktopImg,
                    'mob' => $mobileImg,
                ];
                $idx++;
            }
        }

        // Fallback если данных нет
        if (empty($projects)) {
            foreach ($fallbackProjects as $fp) {
                $projects[] = [
                    'name' => $fp['name'],
                    'url' => $fp['url'],
                    'img' => SITE_TEMPLATE_PATH . '/assets/img/' . $fp['img'],
                    'mob' => SITE_TEMPLATE_PATH . '/assets/img/' . $fp['mob'],
                ];
            }
        }
        ?>
        <section class="initiative">
        <div class="container">
            <div class="initiative__wrapper">
                <div class="initiative__info">
                    <h2 class="main-title"><?= htmlspecialchars($title) ?></h2>
                    <p class="main-text"><?= htmlspecialchars($subtitle) ?></p>
                    <div class="initiative__buttons">
                        <a href="/projects/" class="btn btn-empty">Все проекты</a>
                        <a href="/support/" class="btn">Поддержать</a>
                    </div>
                </div>
                <?php foreach ($projects as $project): ?>
                <a href="<?= htmlspecialchars($project['url']) ?>" class="initiative__card">
                    <h3><?= htmlspecialchars($project['name']) ?></h3>
                    <img alt="<?= htmlspecialchars($project['name']) ?>"
                         src="<?= htmlspecialchars($project['img']) ?>"
                         class="initiative__image desk-block">
                    <img alt="<?= htmlspecialchars($project['name']) ?>"
                         src="<?= htmlspecialchars($project['mob']) ?>"
                         class="initiative__image desk-none">
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        </section>
        <?php
    }
}