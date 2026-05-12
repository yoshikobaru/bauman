<?php
/**
 * Рендерит секцию "Члены Политехнического общества"
 * 
 * Использует группы пользователей для выборки членов общества
 * Данные: ID, NAME, LAST_NAME, SECOND_NAME, PERSONAL_PHOTO, 
 *         UF_GRADUATE_DEPT, UF_GRADUATE_YEAR, UF_MEMBERSHIP_TYPE, UF_COMPANY_DESC
 * 
 * Подключение: require_once dirname(__DIR__) . '/renderers/members_section.php';
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('po_render_members_section')) {
    /**
     * Выводит HTML-разметку секции "Члены Политехнического общества"
     *
     * @param string $title Заголовок секции (по умолчанию: "Члены Политехнического общества")
     * @param int $limit Лимит элементов (по умолчанию: 12)
     * @param bool $useFancybox Включить data-fancybox для модальных окон (по умолчанию: false)
     * @param string|null $groupIdFilter Фильтр по группе пользователей (null - все группы)
     */
    function po_render_members_section(
        string $title = 'Члены Политехнического общества',
        int $limit = 12,
        bool $useFancybox = false,
        ?int $groupIdFilter = null
    ): void
    {
        $members = [];
        
        // Определяем группы для выборки
        $memberGroups = [
            defined('PO_MEMBER_BASIC_ID') ? PO_MEMBER_BASIC_ID : 0,
            defined('PO_MEMBER_PREMIUM_ID') ? PO_MEMBER_PREMIUM_ID : 0,
            defined('PO_PARTNER_ID') ? PO_PARTNER_ID : 0,
        ];
        
        if ($groupIdFilter !== null) {
            $memberGroups = [$groupIdFilter];
        }
        
        $memberGroups = array_filter($memberGroups);
        
        if (empty($memberGroups)) {
            return;
        }
        
        // Фильтр по группам
        $arFilter = ['ACTIVE' => 'Y'];
        if (count($memberGroups) === 1) {
            $arFilter['GROUPS_ID'] = reset($memberGroups);
        }
        
        $arSelect = [
            'ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 
            'PERSONAL_PHOTO', 'EMAIL', 'WORK_COMPANY', 'WORK_POSITION',
            'UF_GRADUATE_DEPT', 'UF_GRADUATE_YEAR', 'UF_COMPANY_NAME', 
            'UF_COMPANY_DESC', 'UF_MEMBERSHIP_TYPE'
        ];
        
        $rsUsers = CUser::GetList(
            'LAST_NAME', 'ASC',
            $arFilter,
            ['SELECT' => $arSelect]
        );
        
        $count = 0;
        while ($user = $rsUsers->Fetch()) {
            if ($limit > 0 && $count >= $limit) {
                break;
            }
            
            $fullName = trim(($user['LAST_NAME'] ?? '') . ' ' . ($user['NAME'] ?? '') . ' ' . ($user['SECOND_NAME'] ?? ''));
            if (empty($fullName)) {
                $fullName = $user['LOGIN'] ?? '';
            }
            
            $photoSrc = !empty($user['PERSONAL_PHOTO'])
                ? CFile::GetPath($user['PERSONAL_PHOTO'])
                : '';
            
            $department = $user['UF_GRADUATE_DEPT'] ?? '';
            $year = $user['UF_GRADUATE_YEAR'] ?? '';
            $subText = implode(', ', array_filter([$department, $year ? 'выпуск ' . $year : '']));
            
            $companyName = $user['UF_COMPANY_NAME'] ?: ($user['WORK_COMPANY'] ?: '');
            $companyDesc = $user['UF_COMPANY_DESC'] ?? '';
            
            $members[] = [
                'id' => (int)$user['ID'],
                'name' => $fullName,
                'photo' => $photoSrc,
                'dept' => $department,
                'year' => $year,
                'sub_text' => $subText,
                'company' => $companyName,
                'company_desc' => $companyDesc,
                'membership_type' => $user['UF_MEMBERSHIP_TYPE'] ?? '',
                'email' => $user['EMAIL'] ?? '',
            ];
            
            $count++;
        }
        
        // Если данных нет — ничего не выводим
        if (empty($members)) {
            return;
        }
        ?>
        <section class="members-section">
        <div class="container">
            <div class="members-section__wrapper">
                <h2 class="main-title"><?= htmlspecialchars($title) ?></h2>
                <div class="members-section__list">
                    <?php foreach ($members as $member): ?>
                    <div class="members-section__item"<?= $useFancybox ? ' data-fancybox data-src="#member-modal-' . $member['id'] . '"' : '' ?>>
                        <?php if (!empty($member['photo'])): ?>
                        <img alt="<?= htmlspecialchars($member['name']) ?>"
                             src="<?= htmlspecialchars($member['photo']) ?>"
                             class="members-section__item-image">
                        <?php else: ?>
                        <div class="members-section__item-placeholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <h3 class="members-section__item-title"><?= htmlspecialchars($member['name']) ?></h3>
                        <?php if (!empty($member['company'])): ?>
                        <p class="members-section__item-company"><?= htmlspecialchars($member['company']) ?></p>
                        <?php endif; ?>
                        <p class="members-section__item-text"><?= htmlspecialchars($member['sub_text']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        </section>
        <?php
    }
}
