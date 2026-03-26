<?php

/**
 * @var $APPLICATION
 */

use Bitrix\Main\Loader;
use \Bitrix\Main\UserTable;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$settings = require __DIR__.'/../../settings.php';

if (Loader::includeSharewareModule($settings['module']['id']) === Loader::MODULE_DEMO_EXPIRED) {
    LocalRedirect('/bitrix/admin/partner_modules.php?lang=' . LANG);
}

Loader::includeModule($settings['module']['id']);

page()->checkAccess('accessSecurity');
page()->setTitle(loc('ArtamonovRestSecurityPageTitle'));
$tabs = [
    ['DIV' => 'tab-1', 'TAB' => loc('ArtamonovRestTabAuthorizationByTokenTitle')],
    ['DIV' => 'tab-2', 'TAB' => loc('ArtamonovRestTabAuthorizationByLoginTitle')],
    ['DIV' => 'tab-3', 'TAB' => loc('ArtamonovRestTabRequestLimitTitle')],
    ['DIV' => 'tab-4', 'TAB' => loc('ArtamonovRestTabFiltersTitle')],
];
$tabControl = new CAdminTabControl('tabControl', $tabs);

$ajax = BX_PERSONAL_ROOT . '/admin/rest-api-security-ajax.php';

// Подготовка файлов
if (!is_file($_SERVER['DOCUMENT_ROOT'] . $ajax)) {
    CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . BX_PERSONAL_ROOT . '/modules/' . settings()->get('module')['id'] . '/install/admin/rest-api-security-ajax.php', $_SERVER['DOCUMENT_ROOT'] . $ajax);
}

$groups = [];
$by = 'NAME';
$order = 'ASC';
$result = CGroup::GetList($by, $order, ['ACTIVE' => 'Y', 'ANONYMOUS' => 'N']);
while ($group = $result->fetch()) {
    if ($group['ID'] == helper()->adminGroupId() || $group['ID'] == helper()->ratingVoteGroupId() || $group['ID'] == helper()->ratingVoteAuthorityGroupId()) continue;
    $groups[$group['ID']] = $group['NAME'];
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

// Генерация токенов
if ($_GET['generateToken'] == 'Y') {
    if (config()->get('useToken')) {
        $updateToken = isset($_GET['updateToken']) && $_GET['updateToken'] === 'Y' ? 'Y' : 'N';
        try {
            $parameters = [
                'filter' => [
                    'ACTIVE' => 'Y'
                ]
            ];
            if ($updateToken === 'Y') {
                $parameters['filter']['!'.settings()->getTokenFieldCode()] = false;
            } else {
                $parameters['filter'][settings()->getTokenFieldCode()] = false;
            }
            $usersCount = UserTable::getList($parameters)->getSelectedRowsCount();
            $userCountByStep = config()->get('tokenGenerateSizeStep') ? config()->get('tokenGenerateSizeStep') : \Artamonov\Rest\Foundation\Token::SIZE_STEP; // количество пользователей за 1 итерацию ------ !!! вынести в параметр --- чтобы регулировать нагрузку
            ?>
            <div id="progress-bar">
                <?php
                CAdminMessage::ShowMessage([
                    'TYPE' => 'PROGRESS',
                    'DETAILS' => '#PROGRESS_BAR#' . '<div id="progress-bar-value" data-processed="0" style="margin-top: 10px;margin-bottom: -15px;"><span class="bx-ui-loc-ri-loader"></span>&nbsp;<span class="bx-ui-loc-ri-status-text">' . loc('ArtamonovRestTokenGenerated', ['#COUNT#' => 0, '#TOTAL_COUNT#' => $usersCount]) . '</span></div>',
                    'HTML' => true,
                    'PROGRESS_TOTAL' => 100,
                    'PROGRESS_VALUE' => $usersCount === 0 ? 100 : 0,
                    'PROGRESS_TEMPLATE' => '<span class="bx-ui-loc-ri-percents">#PROGRESS_VALUE#</span>%'
                ]);
                ?>
            </div>
            <script>
                const progressBar = document.getElementById('progress-bar')
                const request = {
                    parameters: <?= CUtil::PhpToJSObject($parameters) ?>,
                    total: <?= $usersCount ?> || 0,
                    token: {
                        update: "<?= $updateToken ?>",
                        count: 0
                    }
                }
                request.parameters.limit = <?= $userCountByStep ?> || <?= \Artamonov\Rest\Foundation\Token::SIZE_STEP ?>
                //let step = 1
                // start generate
                generateToken()

                function generateToken() {
                    if (request.total === 0) {
                        //console.log('completed')
                        return
                    }
                    const progressValue = document.getElementById('progress-bar-value')
                    request.token.count = progressValue.dataset.processed
                    request.parameters.offset = request.token.count
                    //console.log('step: ' + step)
                    //console.log(request)
                    BX.ajax({
                        url: "<?= $ajax ?>",
                        data: request,
                        method: 'POST',
                        dataType: 'html',
                        //timeout: <?php //= $timeout ?>,
                        //async: true,
                        onsuccess: function (response) {
                            if (response === 'completed') {
                                //console.log('completed')
                                return
                            }
                            //step++
                            if (response) {
                                progressBar.innerHTML = response;
                                generateToken()
                            }
                        }
                    })
                }
            </script>
            <?php
        } catch (\Bitrix\Main\ObjectPropertyException $e) {
        } catch (\Bitrix\Main\ArgumentException $e) {
        } catch (\Bitrix\Main\SystemException $e) {
        }
    }
}

if (config()->get('tokenFieldCode')) {
    try {
        if (!db()->query('select FIELD_NAME from b_user_field where FIELD_NAME="' . config()->get('tokenFieldCode') . '" LIMIT 1')->fetchRaw()) {
            CAdminMessage::ShowMessage(loc('ArtamonovRestTokenFieldCodeNotFound', ['#REST_API_TOKEN_FIELD_CODE#' => config()->get('tokenFieldCode')]));
        }
    } catch (\Bitrix\Main\Db\SqlQueryException $e) {
    };
}
$tabControl->Begin();
?>
    <form method="POST" name="<?= basename(__FILE__, '.php') ?>" action="<?= $APPLICATION->GetCurUri() ?>">
        <?= bitrix_sessid_post() ?>

        <?php $tabControl->BeginNextTab() ?>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestUseToken') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:useToken', true, config()->get('useToken')) ?>
                <?php ShowJSHint(loc('ArtamonovRestUseTokenHint', ['#FIELD_NAME_REST_API_TOKEN#' => loc('ArtamonovRestTokenField'), '#FIELD_NAME_REST_API_TOKEN_EXPIRE#' => loc('ArtamonovRestTokenFieldExpire')])) ?>
            <td>
        </tr>

        <?php if (config()->get('showExamples')): ?>
            <tr>
                <td width="45%" valign="middle"><?= loc('ArtamonovRestExample') ?>
                <td>
                <td width="55%" valign="middle"
                    style="color: <?= config()->get('useToken') ? 'rgb(34, 162, 59)' : 'rgb(206, 0, 0)' ?>">
                    <?= loc('ArtamonovRestExampleToken', ['#KEYWORD#' => config()->get('tokenKey') ? config()->get('tokenKey') . ':' : '', '#TOKEN#' => '434337b6-f12691d2-47bf6fb9-c040ae6b']) ?>
                    <?php ShowJSHint(loc('ArtamonovRestExampleHint')) ?>
                <td>
            </tr>
            <tr>
                <td colspan="4">&nbsp;</td>
            </tr>
        <?php endif ?>

        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestCheckExpireToken') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:checkExpireToken', true, config()->get('checkExpireToken')) ?>
                <?php ShowJSHint(loc('ArtamonovRestCheckExpireTokenHint')) ?>
            <td>
        </tr>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestUseGenerateTokenRegisterUser') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:useGenerateTokenRegisterUser', true, config()->get('useGenerateTokenRegisterUser')) ?>
                <?php ShowJSHint(loc('ArtamonovRestUseGenerateTokenRegisterUserHint')) ?>
            <td>
        </tr>

        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestTokenKey') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('text', 'parameter:tokenKey', config()->get('tokenKey'), false, false, false, config()->get('useToken') ? '' : 'disabled') ?>
                <?php ShowJSHint(loc('ArtamonovRestTokenKeyHint')) ?>
            <td>
        </tr>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestTokenFieldCode') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('text', 'parameter:tokenFieldCode', config()->get('tokenFieldCode'), false, false, false, config()->get('useToken') ? '' : 'disabled') ?>
                <?php ShowJSHint(loc('ArtamonovRestTokenFieldCodeHint', ['#REST_API_TOKEN_FIELD_CODE#' => settings()->get('config')['token']['code']])) ?>
            <td>
        </tr>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestTokenLifetime') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('text', 'parameter:tokenLifetime', config()->get('tokenLifetime'), false, false, false, config()->get('useToken') ? '' : 'disabled') ?>
                <?php ShowJSHint(loc('ArtamonovRestTokenLifetimeHint')) ?>
            <td>
        </tr>
        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>

        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestTokenGenerateSizeStep') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('text', 'parameter:tokenGenerateSizeStep', config()->get('tokenGenerateSizeStep') ? config()->get('tokenGenerateSizeStep') : \Artamonov\Rest\Foundation\Token::SIZE_STEP, false, false, '', config()->get('useToken') ? '' : 'disabled') ?>
                <?php ShowJSHint(loc('ArtamonovRestTokenGenerateSizeStepHint', ['#TOKEN_GENERATE_SIZE_STEP#' => \Artamonov\Rest\Foundation\Token::SIZE_STEP])) ?>
            <td>
        </tr>
        <tr>
            <td width='45%' valign='middle'><?= loc('ArtamonovRestGenerateToken') ?>
            <td>
            <td width='55%' valign='middle'>
                <?php if (config()->get('useToken')): ?>
                    <a href="/bitrix/admin/rest-api-security.php?lang=<?= LANG ?>&generateToken=Y"><?= loc('ArtamonovRestGenerateTokenLinkCreateTitle') ?></a>
                    &nbsp;&nbsp;
                    <a href="/bitrix/admin/rest-api-security.php?lang=<?= LANG ?>&generateToken=Y&updateToken=Y"><?= loc('ArtamonovRestGenerateTokenLinkUpdateTitle') ?></a>
                <?php else: ?>
                    <span style="opacity: .3"><?= loc('ArtamonovRestGenerateTokenLinkCreateTitle') ?></span>
                    &nbsp;&nbsp;
                    <span style="opacity: .3"><?= loc('ArtamonovRestGenerateTokenLinkUpdateTitle') ?></span>
                <?php endif ?>

                <?php ShowJSHint(loc('ArtamonovRestGenerateTokenHint')) ?>
            <td>
        </tr>

        <?php $tabControl->BeginNextTab() ?>
        <tr>
            <td width='45%' valign='top'><?= loc('ArtamonovRestUseLoginPassword') ?>
            <td>
            <td width='55%' valign='middle'>
                <?= InputType('checkbox', 'parameter:useLoginPassword', true, config()->get('useLoginPassword')) ?>
                <?php ShowJSHint(loc('ArtamonovRestUseLoginPasswordHint')) ?>
            <td>
        </tr>
        <?php $tabControl->BeginNextTab() ?>
        <tr>
            <td width="45%" valign="middle"><?= loc('ArtamonovRestUseRequestLimit') ?>
            <td>
            <td width="55%" valign="middle">
                <?= InputType('checkbox', 'parameter:useRequestLimit', true, config()->get('useRequestLimit'), false, false, config()->get('useToken') || config()->get('useLoginPassword') ? '' : 'disabled') ?>
                <?php ShowJSHint(loc('ArtamonovRestUseRequestLimitHint')) ?>
            <td>
        </tr>

        <?php if (count($groups) > 0): ?>
            <?php $current = json_decode(config()->get('requestLimit'), true) ?>
            <?php foreach ($groups as $id => $name): ?>
                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>
                <tr>
                    <td width="45%" valign="middle"><?= loc('ArtamonovRestGroup') ?>
                    <td>
                    <td width="55%" valign="middle">
                        <?= $name ?>
                        <?php ShowJSHint(loc('ArtamonovRestGroupHint')) ?>
                    <td>
                </tr>

                <tr>
                    <td width="45%" valign="middle"><?= loc('ArtamonovRestNumber') ?>
                    <td>
                    <td width="55%" valign="middle">
                        <?= InputType('text', 'data:requestLimitNumber-' . $id, $current[$id]['number'], false, false, false) ?>
                        <?php ShowJSHint(loc('ArtamonovRestNumberHint')) ?>
                    <td>
                </tr>

                <tr>
                    <td width="45%" valign="middle"><?= loc('ArtamonovRestPeriod') ?>
                    <td>
                    <td width="55%" valign="middle">
                        <?= InputType('text', 'data:requestLimitPeriod-' . $id, $current[$id]['period'], false, false, false) ?>
                        <?php ShowJSHint(loc('ArtamonovRestPeriodHint')) ?>
                    <td>
                </tr>

            <?php endforeach ?>
        <?php else: ?>
            <tr>
                <td colspan="4" align="center">
                    <?php helper()->note(loc('ArtamonovRestNoteNotEnoughGroups', ['#LANG#' => LANG])) ?>
                </td>
            </tr>
        <?php endif ?>

        <?php $tabControl->BeginNextTab() ?>

        <tr>
            <td width='45%' valign='top'><?= loc('ArtamonovRestUseCorsFilter') ?>
            <td>
            <td width='55%' valign='middle'>
                <?= InputType('checkbox', 'parameter:useCorsFilter', true, config()->get('useCorsFilter')) ?>
                <?php ShowJSHint(loc('ArtamonovRestUseCorsFilterHint')) ?>
            <td>
        </tr>
        <tr>
            <td width='45%'
                valign='top'><?= loc('ArtamonovRestAccessControlAllowHeaders') ?>
            <td>
            <td width='55%' valign='top'>
                <textarea name="parameter:accessControlAllowHeaders" cols="50"
                          rows="5"><?= config()->get('accessControlAllowHeaders') ?></textarea>
                <?php ShowJSHint(loc('ArtamonovRestAccessControlAllowHeadersHint')) ?>
            <td>
        </tr>
        <tr>
            <td width='45%'
                valign='top'><?= loc('ArtamonovRestCorsListDomains') ?>
            <td>
            <td width='55%' valign='top'>
                <textarea name="parameter:corsListDomains" cols="50"
                          rows="5"><?= config()->get('corsListDomains') ?></textarea>
                <?php ShowJSHint(loc('ArtamonovRestCorsListDomainsHint')) ?>
            <td>
        </tr>
    </form>
<?php
$tabControl->Buttons();
echo InputType('submit', 'save', loc('ArtamonovRestButtonSave'), false, false, false, config()->get('useRestApi') ? 'class="adm-btn-save"' : 'disabled');
echo InputType('submit', 'restore', loc('ArtamonovRestButtonRestore'), false, false, false, config()->get('useRestApi') ? '' : 'disabled');
$tabControl->End();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
