<?php

/**
 * @var $APPLICATION
 */

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$settings = require __DIR__ . '/../../settings.php';

if (Loader::includeSharewareModule($settings['module']['id']) === Loader::MODULE_DEMO_EXPIRED) {
    LocalRedirect('/bitrix/admin/partner_modules.php?lang=' . LANG);
}

try {
    Loader::includeModule($settings['module']['id']);
} catch (LoaderException $e) {
}
page()->checkAccess('accessConfig');
page()->setTitle(loc('ArtamonovRestConfigPageTitle'));
$tabs = [
    ['DIV' => 'tab-1', 'TAB' => loc('ArtamonovRestTabMainTitle')],
    ['DIV' => 'tab-2', 'TAB' => loc('ArtamonovRestTabRoutesTitle')],
    ['DIV' => 'tab-3', 'TAB' => loc('ArtamonovRestTabAccessTitle')]
];
$tabControl = new CAdminTabControl('tabControl', $tabs);
$groups = [];
$by = 'NAME';
$order = 'ASC';
$result = CGroup::GetList($by, $order, ['ACTIVE' => 'Y', 'ANONYMOUS' => 'N']);
while ($group = $result->fetch()) {
    if ($group['ID'] == helper()->adminGroupId() || $group['ID'] == helper()->ratingVoteGroupId() || $group['ID'] == helper()->ratingVoteAuthorityGroupId()) continue;
    $groups['REFERENCE_ID'][] = $group['ID'];
    $groups['REFERENCE'][] = $group['NAME'];
}
$sites = [];
$by = 'ID';
$order = 'ASC';
$result = CSite::GetList($by, $order, ['ACTIVE' => 'Y']);
while ($site = $result->fetch()) {
    $sites['REFERENCE_ID'][] = $site['ID'];
    $sites['REFERENCE'][] = '[' . $site['ID'] . '] ' . $site['NAME'];
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
if ($_POST) {
    $_POST['form'] = basename(__FILE__, '.php');
    if (isset($_POST['save'])) {
        config()->save();
        echo CAdminMessage::ShowNote(loc('ArtamonovRestSaved'));
    } elseif (isset($_POST['restore'])) {
        config()->restore();
        echo CAdminMessage::ShowNote(loc('ArtamonovRestRestored'));
    }
}
$tabControl->Begin()
?>
    <form method="POST" name="<?= basename(__FILE__, '.php') ?>" action="<?= $APPLICATION->GetCurUri() ?>">
        <?= bitrix_sessid_post() ?>
        <?php $tabControl->BeginNextTab() ?>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestUseRestApi') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:useRestApi', true, config()->get('useRestApi')) ?>
                <?php ShowJSHint(loc('ArtamonovRestUseRestApiHint')) ?>
            <td>
        </tr>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestPathRestApi') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('text', 'parameter:pathRestApi', config()->get('pathRestApi'), false, false, false, 'size="10"') ?>
                <?php ShowJSHint(loc('ArtamonovRestPathRestApiHint')) ?>
            <td>
        </tr>
        <tr>
            <td colspan="4" align="center">&nbsp;</td>
        </tr>

        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestUseLateStart') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:useLateStart', true, config()->get('useLateStart')) ?>
                <?php ShowJSHint(loc('ArtamonovRestUseLateStartHint')) ?>
            <td>
        </tr>

        <tr>
            <td colspan="4" align="center">&nbsp;</td>
        </tr>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestUseJournal') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:useJournal', true, config()->get('useJournal')) ?>
                <?php ShowJSHint(loc('ArtamonovRestUseJournalHint')) ?>
            <td>
        </tr>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestShowExamples') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:showExamples', true, config()->get('showExamples')) ?>
                <?php ShowJSHint(loc('ArtamonovRestShowExamplesHint')) ?>
            <td>
        </tr>

        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>

        <tr>
            <td width="45%" valign="top"><?= loc('ArtamonovRestSiteList') ?>
            <td>
            <td width="55%" valign="middle">
                <?= SelectBoxMFromArray('parameter:siteList[]', $sites, explode('|', config()->get('siteList')), '', false, 5, 'class ="inputselect"') ?>
                <?php ShowJSHint(loc('ArtamonovRestSiteListHint')) ?>
            <td>
        </tr>

        <?php $tabControl->BeginNextTab() ?>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestUseNativeRoute') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:useNativeRoute', true, config()->get('useNativeRoute')) ?>
                <?php ShowJSHint(loc('ArtamonovRestUseNativeRouteHint')) ?>
            <td>
        </tr>

        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestUseExampleRoute') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:useExampleRoute', true, config()->get('useExampleRoute')) ?>
                <?php ShowJSHint(loc('ArtamonovRestUseExampleRouteHint')) ?>
            <td>
        </tr>

        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestLocalRouteMap') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('text', 'parameter:localRouteMap', config()->get('localRouteMap'), false, false, false, 'size="30"') ?>
                <?php ShowJSHint(loc('ArtamonovRestLocalRouteMapHint')) ?>
            <td>
        </tr>
        <?php $tabControl->BeginNextTab() ?>
        <tr>
            <td width="45%" valign="top"><?= loc('ArtamonovRestAccessDocumentation') ?>
            <td>
            <td width="55%" valign="middle">
                <?= SelectBoxMFromArray('parameter:accessDocumentation[]', $groups, explode('|', config()->get('accessDocumentation')), '', false, 5, 'class ="inputselect"') ?>
                <?php ShowJSHint(loc('ArtamonovRestAccessDocumentationHint')) ?>
            <td>
        </tr>
        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>
        <tr>
            <td width="45%" valign="top"><?= loc('ArtamonovRestAccessSecurity') ?>
            <td>
            <td width="55%" valign="middle">
                <?= SelectBoxMFromArray('parameter:accessSecurity[]', $groups, explode('|', config()->get('accessSecurity')), '', false, 5, 'class ="inputselect"') ?>
                <?php ShowJSHint(loc('ArtamonovRestAccessSecurityHint')) ?>
            <td>
        </tr>
        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>
        <tr>
            <td width="45%" valign="top"><?= loc('ArtamonovRestAccessJournal') ?>
            <td>
            <td width="55%" valign="middle">
                <?= SelectBoxMFromArray('parameter:accessJournal[]', $groups, explode('|', config()->get('accessJournal')), '', false, 5, 'class ="inputselect"') ?>
                <?php ShowJSHint(loc('ArtamonovRestAccessJournalHint')) ?>
            <td>
        </tr>
        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>
        <tr>
            <td width="45%" valign="top"><?= loc('ArtamonovRestAccessSupport') ?>
            <td>
            <td width="55%" valign="middle">
                <?= SelectBoxMFromArray('parameter:accessSupport[]', $groups, explode('|', config()->get('accessSupport')), '', false, 5, 'class ="inputselect"') ?>
                <?php ShowJSHint(loc('ArtamonovRestAccessSupportHint')) ?>
            <td>
        </tr>

        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>
        <tr>
            <td width="45%" valign="top"><?= loc('ArtamonovRestaccessMenuItems') ?>
            <td>
            <td width="55%" valign="middle">
                <?= SelectBoxMFromArray('parameter:accessMenuItems[]', $groups, explode('|', config()->get('accessMenuItems')), '', false, 5, 'class ="inputselect"') ?>
                <?php ShowJSHint(loc('ArtamonovRestaccessMenuItemsHint', ['#MODULE_NAME#' => settings()->get('module')['name']])) ?>
            <td>
        </tr>
    </form>

<?php
$tabControl->Buttons();
echo InputType('submit', 'save', loc('ArtamonovRestButtonSave'), false, false, false, 'class="adm-btn-save"');
echo InputType('submit', 'restore', loc('ArtamonovRestButtonRestore'), false);
$tabControl->End();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
