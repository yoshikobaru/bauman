<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Участники");
$APPLICATION->SetPageProperty('description', 'Члены Политехнического общества выпускников МГТУ им. Н.Э. Баумана: базовые, профессиональные и партнёрские участники организации.');

/**
 * Страница каталога участников общества (по ТЗ).
 * Вкладки: Почётные (premium group + badge Правление для модераторов),
 *          Профессиональные (basic group), Безвозмездные (honorary type),
 *          Компании-партнёры (partner group)
 */

$tabs = [
    'honorary'     => ['label' => 'Почётные члены',      'mode' => 'group',  'group_id' => PO_MEMBER_PREMIUM_ID],
    'professional' => ['label' => 'Профессиональные',    'mode' => 'group',  'group_id' => PO_MEMBER_BASIC_ID],
    'free'         => ['label' => 'Безвозмездные члены', 'mode' => 'type',   'type_val' => 'honorary'],
    'partner'      => ['label' => 'Компании-партнёры',   'mode' => 'group',  'group_id' => PO_PARTNER_ID],
];

$activeTab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs)
    ? $_GET['tab']
    : 'honorary';

$_moderatorGroupId = defined('PO_MODERATOR_ID') ? PO_MODERATOR_ID : 0;

// Load users by group
function po_getGroupMembers($groupId) {
    $arSelect = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'PHOTO', 'UF_GRADUATE_YEAR', 'UF_GRADUATE_DEPT', 'UF_MEMBERSHIP_TYPE'];
    $rsUsers = CUser::GetList('last_name', 'asc', ['GROUPS_ID' => $groupId, 'ACTIVE' => 'Y'], ['SELECT' => $arSelect]);
    $members = [];
    while ($u = $rsUsers->Fetch()) { $members[] = $u; }
    return $members;
}

// Load users by UF_MEMBERSHIP_TYPE value
function po_getMembersByType($typeVal) {
    $arSelect = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'PHOTO', 'UF_GRADUATE_YEAR', 'UF_GRADUATE_DEPT', 'UF_MEMBERSHIP_TYPE'];
    $rsUsers = CUser::GetList('last_name', 'asc', ['UF_MEMBERSHIP_TYPE' => $typeVal, 'ACTIVE' => 'Y'], ['SELECT' => $arSelect]);
    $members = [];
    while ($u = $rsUsers->Fetch()) { $members[] = $u; }
    return $members;
}

// Check if user is in moderator/board group (for badge)
function po_isBoardMember($userId, $moderatorGroupId) {
    if (!$moderatorGroupId || !$userId) return false;
    $groups = CUser::GetUserGroup((int)$userId);
    return in_array($moderatorGroupId, $groups);
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
    $members = ($tab['mode'] === 'type')
        ? po_getMembersByType($tab['type_val'])
        : po_getGroupMembers($tab['group_id']);
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
    $isBoard = ($key === 'honorary') && po_isBoardMember($member['ID'], $_moderatorGroupId);

    if ($key === 'partner'):
        // Для партнёров — показываем компанию
        $companyName = $member['UF_COMPANY_NAME'] ?? ($member['UF_GRADUATE_DEPT'] ?? '');
        $companyDesc = $member['UF_COMPANY_DESC'] ?? '';
        $displayName = $companyName ?: $fullName;
?>
                    <div class="boards__item" style="display:flex;flex-direction:column;align-items:center;text-align:center">
                        <img src="<?= htmlspecialchars($photoSrc) ?>" alt="<?= htmlspecialchars($displayName) ?>" class="boards__item-image">
                        <h3 class="boards__item-title" style="margin-top:12px"><?= htmlspecialchars($displayName) ?></h3>
                        <?php if ($fullName && $companyName && $fullName !== $companyName): ?>
                        <p class="boards__item-text" style="color:#666;font-size:13px"><?= htmlspecialchars($fullName) ?></p>
                        <?php endif; ?>
                        <?php if ($companyDesc): ?>
                        <p class="boards__item-text" style="font-size:13px;color:#555;margin-top:6px;line-height:1.5"><?= htmlspecialchars($companyDesc) ?></p>
                        <?php endif; ?>
                    </div>
<?php else:
        $dept = $member['UF_GRADUATE_DEPT'] ?? '';
        $year = $member['UF_GRADUATE_YEAR'] ?? '';
        $subText = implode(', ', array_filter([$dept, $year ? 'выпуск ' . $year : '']));
?>
                    <div class="boards__item">
                        <img src="<?= htmlspecialchars($photoSrc) ?>" alt="<?= htmlspecialchars($fullName) ?>" class="boards__item-image">
                        <h3 class="boards__item-title">
                            <?= htmlspecialchars($fullName) ?>
                            <?php if ($isBoard): ?>
                            <span style="display:inline-block;font-size:11px;font-weight:600;background:#1a3a6b;color:#fff;padding:2px 8px;border-radius:4px;margin-left:6px;vertical-align:middle">Правление</span>
                            <?php endif; ?>
                        </h3>
                        <?php if ($subText): ?>
                        <p class="boards__item-text"><?= htmlspecialchars($subText) ?></p>
                        <?php endif; ?>
                    </div>
<?php endif; ?>
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
