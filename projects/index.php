<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проекты");
$APPLICATION->SetPageProperty('description', 'Проекты Политехнического общества выпускников МГТУ: инициативы по развитию науки, технологий и связей с индустрией.');

use Bitrix\Main\Loader;
$iblockOk = Loader::includeModule('iblock');

// ?status=active | completed | (all by default)
$statusFilter = $_GET['status'] ?? 'all';
if (!in_array($statusFilter, ['active', 'completed', 'all'])) {
    $statusFilter = 'all';
}

$dbProjects = null;
if ($iblockOk && defined('IBLOCK_PROJECTS_ID') && IBLOCK_PROJECTS_ID > 0) {
    $arFilter = ['IBLOCK_ID' => IBLOCK_PROJECTS_ID, 'ACTIVE' => 'Y'];
    if ($statusFilter !== 'all') {
        // Filter by PROJECT_STATUS property value
        $arFilter['PROPERTY_PROJECT_STATUS'] = $statusFilter;
    }
    $dbProjects = CIBlockElement::GetList(
        ['SORT' => 'ASC'],
        $arFilter,
        false,
        false,
        ['ID', 'NAME', 'CODE', 'DATE_ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL', 'PROPERTY_DETAIL_URL']
    );
}

$statusLabels = ['all' => 'Все', 'active' => 'Активные', 'completed' => 'Завершённые'];
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
            <h2 class="main-title visits__title">Проекты сообщества</h2>

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
<?php
// Статичные активные проекты (fallback если инфоблок пуст)
$_staticActive = [
    ['name' => 'PolytechExpo',             'text' => 'Ежегодная конференция выпускников и партнёров МГТУ им. Н.Э. Баумана', 'url' => '/projects/politech-expo/',  'img' => '/assets/img/projects-page/current-project-img-1.png'],
    ['name' => 'Встреча выпускников',      'text' => 'Традиционная встреча выпускников всех поколений Бауманки',            'url' => '/projects/conference/',    'img' => '/assets/img/projects-page/current-project-img-1.png'],
    ['name' => 'Попечительский совет МТ4', 'text' => 'Поддержка развития кафедры МТ4 МГТУ им. Н.Э. Баумана',              'url' => '/projects/trustees/',      'img' => '/assets/img/projects-page/current-project-img-1.png'],
    ['name' => 'Реставрация ротонды',      'text' => 'Проект по восстановлению исторической ротонды МГТУ',                  'url' => '/projects/restoration/',   'img' => '/assets/img/projects-page/current-project-img-1.png'],
];
?>
                <?php
                $hasItems = false;
                if ($dbProjects) {
                    while ($row = $dbProjects->GetNext()) {
                        $hasItems = true;
                        $imgSrc = SITE_TEMPLATE_PATH . '/assets/img/projects-page/current-project-img-1.png';
                        if (!empty($row['PREVIEW_PICTURE'])) {
                            $img = CFile::GetPath($row['PREVIEW_PICTURE']);
                            if ($img) $imgSrc = $img;
                        }
                        $link = !empty($row['PROPERTY_DETAIL_URL_VALUE'])
                            ? $row['PROPERTY_DETAIL_URL_VALUE']
                            : '/projects/detail/?id=' . (int)$row['ID'];
                        ?>
                <div class="visits__card">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" class="visits__image">
                    <div class="visits__content">
                        <?php if (!empty($row['DATE_ACTIVE_FROM'])): ?>
                        <div class="visits__date"><div class="visits__date-current">
                            <p><span>Дата:</span> <?= date('d F Y', strtotime($row['DATE_ACTIVE_FROM'])) ?></p>
                        </div></div>
                        <?php endif; ?>
                        <h3 class="visits__subtitle"><?= htmlspecialchars($row['NAME']) ?></h3>
                        <?php if (!empty($row['PREVIEW_TEXT'])): ?>
                        <p class="visits__text"><?= htmlspecialchars($row['PREVIEW_TEXT']) ?></p>
                        <?php endif; ?>
                        <div class="visits__buttons">
                            <a href="/support/" class="btn visits__btn visits__btn--help">Поддержать</a>
                            <a href="<?= htmlspecialchars($link) ?>" class="btn visits__btn btn-transparent">Подробнее</a>
                        </div>
                    </div>
                </div>
                <?php
                    }
                }

                // Fallback — статичные карточки активных проектов
                if (!$hasItems && $statusFilter !== 'completed') {
                    foreach ($_staticActive as $sp): ?>
                <div class="visits__card">
                    <img src="<?= SITE_TEMPLATE_PATH . $sp['img'] ?>" alt="" class="visits__image">
                    <div class="visits__content">
                        <h3 class="visits__subtitle"><?= $sp['name'] ?></h3>
                        <p class="visits__text"><?= $sp['text'] ?></p>
                        <div class="visits__buttons">
                            <a href="/support/" class="btn visits__btn visits__btn--help">Поддержать</a>
                            <a href="<?= $sp['url'] ?>" class="btn visits__btn btn-transparent">Подробнее</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; }

                // Архивная карточка при фильтре completed
                if ($statusFilter === 'completed' && !$hasItems): ?>
                <div class="visits__card">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/projects-page/current-project-img-1.png" alt="" class="visits__image">
                    <div class="visits__content">
                        <h3 class="visits__subtitle">Завершённые проекты</h3>
                        <p class="visits__text">Архив инициатив и реализованных программ Политехнического общества.</p>
                        <div class="visits__buttons">
                            <a href="/projects/archive-template/" class="btn visits__btn btn-transparent">Подробнее</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
