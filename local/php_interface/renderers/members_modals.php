<?php
/**
 * Рендерит модальные окна для членов Политехнического общества
 * 
 * Использует группы пользователей для выборки членов общества
 * Данные: ID, NAME, LAST_NAME, PERSONAL_PHOTO, 
 *         UF_GRADUATE_DEPT, UF_GRADUATE_YEAR, UF_COMPANY_DESC
 * 
 * Подключение: require_once dirname(__DIR__) . '/renderers/members_modals.php';
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('po_render_members_modals')) {
    /**
     * Выводит HTML-разметку модальных окон для членов общества
     * Используется с data-fancybox для открытия при клике на карточку
     */
    function po_render_members_modals(): void 
    {
        $memberGroups = [
            defined('PO_MEMBER_BASIC_ID') ? PO_MEMBER_BASIC_ID : 0,
            defined('PO_MEMBER_PREMIUM_ID') ? PO_MEMBER_PREMIUM_ID : 0,
            defined('PO_PARTNER_ID') ? PO_PARTNER_ID : 0,
        ];
        $memberGroups = array_filter($memberGroups);
        
        if (empty($memberGroups)) {
            return;
        }
        
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
        
        while ($user = $rsUsers->Fetch()):
            $fullName = trim(($user['LAST_NAME'] ?? '') . ' ' . ($user['NAME'] ?? '') . ' ' . ($user['SECOND_NAME'] ?? ''));
            if (empty($fullName)) {
                $fullName = $user['LOGIN'] ?? 'Пользователь';
            }
            
            $photoSrc = !empty($user['PERSONAL_PHOTO'])
                ? CFile::GetPath($user['PERSONAL_PHOTO'])
                : '';
            
            $department = $user['UF_GRADUATE_DEPT'] ?? '';
            $year = $user['UF_GRADUATE_YEAR'] ?? '';
            $companyName = $user['UF_COMPANY_NAME'] ?: ($user['WORK_COMPANY'] ?: '');
            $companyDesc = $user['UF_COMPANY_DESC'] ?? '';
            $position = $user['WORK_POSITION'] ?? '';
            $email = $user['EMAIL'] ?? '';
            
            $detailText = '';
            if (!empty($companyDesc)) {
                $detailText = $companyDesc;
            } elseif (!empty($department) || !empty($year)) {
                $detailText = implode(', ', array_filter([$department, $year ? 'выпуск ' . $year : '']));
            }
        ?>
        <div class="form-boards" id="member-modal-<?= (int)$user['ID'] ?>" style="display:none;max-width:1100px;">
            <div class="form-boards__wrapper">
                <?php if (!empty($photoSrc)): ?>
                <img alt="<?= htmlspecialchars($fullName) ?>" 
                     src="<?= htmlspecialchars($photoSrc) ?>" 
                     class="form-boards__image">
                <?php else: ?>
                <div class="form-boards__placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <?php endif; ?>
                <div class="form-boards__content">
                    <h2><?= htmlspecialchars($fullName) ?></h2>
                    <?php if (!empty($companyName)): ?>
                    <p class="form-boards__company"><?= htmlspecialchars($companyName) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($position)): ?>
                    <p class="form-boards__position"><?= htmlspecialchars($position) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($detailText)): ?>
                    <div class="form-boards__detail"><?= htmlspecialchars($detailText) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($email)): ?>
                    <p class="form-boards__email">
                        <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php 
        endwhile;
    }
}
