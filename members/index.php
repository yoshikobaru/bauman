<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Участники");
$APPLICATION->SetPageProperty('description', 'Члены Политехнического общества выпускников МГТУ им. Н.Э. Баумана: базовые, профессиональные и партнёрские участники организации.');

/**
 * Страница каталога участников общества (по ТЗ).
 * 4 вкладки: Почётные (group 7), Профессиональные (group 6),
 *            Компании-партнёры (group 8), Правление/Модераторы (group 9)
 */

$tabs = [
    'premium'   => ['label' => 'Почётные члены',       'group_id' => PO_MEMBER_PREMIUM_ID],
    'basic'     => ['label' => 'Профессиональные',      'group_id' => PO_MEMBER_BASIC_ID],
    'partner'   => ['label' => 'Компании-партнёры',     'group_id' => PO_PARTNER_ID],
    'moderator' => ['label' => 'Правление / Модераторы','group_id' => PO_MODERATOR_ID],
];

$activeTab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs)
    ? $_GET['tab']
    : 'premium';

// Load users for each tab
function po_getGroupMembers($groupId) {
    $arFilter = ['GROUPS_ID' => $groupId, 'ACTIVE' => 'Y'];
    $arSelect = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'PHOTO', 'UF_GRADUATE_YEAR', 'UF_GRADUATE_DEPT', 'UF_MEMBERSHIP_TYPE'];
    $rsUsers = CUser::GetList('last_name', 'asc', $arFilter, ['SELECT' => $arSelect]);
    $members = [];
    while ($u = $rsUsers->Fetch()) {
        $members[] = $u;
    }
    return $members;
}
?>

<main>
    <!-- banner-other -->
    <section class="banner-other banner-other-project">
        <div class="container">
            <div class="banner-other__wrapper">
                <div class="banner-other__content">
                    <div class="banner-other__info">
                        <h1 class="banner-other__title main-title">
                            Участники
                        </h1>
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                </div>
                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-img.png" alt="" class="banner-other__image">
            </div>
        </div>
        <!-- /.container -->
    </section>
    <!-- /.banner-other -->

    <!-- members tabs -->
    <section class="boards boards--members" style="padding: 60px 0;">
        <div class="container">
            <div class="main-tabs-click" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:40px;">
<?php foreach ($tabs as $key => $tab): ?>
                <button
                    class="btn <?= $key === $activeTab ? '' : 'btn-transparent' ?> main-tabs-click__item"
                    data-tab="tab-<?= $key ?>"
                    onclick="window.location.href='/members/?tab=<?= $key ?>'"
                    style="<?= $key === $activeTab ? '' : 'opacity:0.7;' ?>"
                >
                    <?= htmlspecialchars($tab['label']) ?>
                </button>
<?php endforeach; ?>
            </div>

<?php foreach ($tabs as $key => $tab): ?>
            <div class="main-tabs-pane" id="tab-<?= $key ?>" <?= $key !== $activeTab ? 'style="display:none;"' : '' ?>>
<?php
    $members = po_getGroupMembers($tab['group_id']);
    if (empty($members)):
?>
                <p class="main-text" style="color:#888;">Участников в этой категории пока нет.</p>
<?php else: ?>
                <div class="boards__list">
<?php foreach ($members as $member):
    $fullName = trim(($member['LAST_NAME'] ?? '') . ' ' . ($member['NAME'] ?? '') . ' ' . ($member['SECOND_NAME'] ?? ''));
    $photoSrc = !empty($member['PHOTO'])
        ? CFile::GetPath($member['PHOTO'])
        : SITE_TEMPLATE_PATH . '/assets/img/board-placeholder.png';
    $dept = $member['UF_GRADUATE_DEPT'] ?? '';
    $year = $member['UF_GRADUATE_YEAR'] ?? '';
    $subText = implode(', ', array_filter([$dept, $year ? 'выпуск ' . $year : '']));
?>
                    <div class="boards__item">
                        <img src="<?= htmlspecialchars($photoSrc) ?>" alt="<?= htmlspecialchars($fullName) ?>" class="boards__item-image">
                        <h3 class="boards__item-title">
                            <?= htmlspecialchars($fullName) ?>
                        </h3>
                        <?php if ($subText): ?>
                        <p class="boards__item-text">
                            <?= htmlspecialchars($subText) ?>
                        </p>
                        <?php endif; ?>
                    </div>
<?php endforeach; ?>
                </div>
<?php endif; ?>
            </div>
<?php endforeach; ?>

        </div>
        <!-- /.container -->
    </section>
    <!-- /.members -->
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
