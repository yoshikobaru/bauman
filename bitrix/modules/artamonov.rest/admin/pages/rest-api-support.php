<?php

use Artamonov\Rest\Foundation\RequestResponseTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$settings = require __DIR__ . '/../../settings.php';

if (Loader::includeSharewareModule($settings['module']['id']) === Loader::MODULE_DEMO_EXPIRED) {
    LocalRedirect('/bitrix/admin/partner_modules.php?lang=' . LANG);
}

Loader::includeModule($settings['module']['id']);
page()->checkAccess('accessSupport');
page()->addCss('/bitrix/css/' . settings()->get('module')['id'] . '/' . basename(__FILE__, '.php') . '.min.css');
page()->setTitle(loc('ArtamonovRestSupportPageTitle'));
$tabs = [
    ['DIV' => 'tab-1', 'TAB' => loc('ArtamonovRestTabMainTitle'), 'TITLE' => loc('ArtamonovRestTabMainDescription')],
    ['DIV' => 'tab-2', 'TAB' => loc('ArtamonovRestTabMonitorTitle'), 'TITLE' => loc('ArtamonovRestTabMonitorDescription')],
];
$tabControl = new CAdminTabControl('tabControl', $tabs);
$routes = [];
$dir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'routes';
$files[$dir] = array_diff(scandir($dir), ['..', '.']);
if (config()->get('localRouteMap')) {
    $dir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . config()->get('localRouteMap');
    $files[$dir] = array_diff(scandir($dir), ['..', '.']);
}
foreach ($files as $dir => $items) {
    foreach ($items as $file) {
        if ((!config()->get('useNativeRoute') && $file === settings()->get('file')['native']) || (!config()->get('useExampleRoute') && $file === settings()->get('file')['example'])) continue;
        $file = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_array($ar = require $file)) {
            foreach ($ar as $type => $r) {
                foreach ($r as $route => $config) {
                    $routes[$type][] = $route;
                    if ($config['active'] === false) $routes['disabled'][] = $route;
                    if ($config['security']['auth']['required']) $routes['auth'][] = $route;
                    if ($config['security']['login']['whitelist']) $routes['login-whitelist'][] = $route;
                    if ($config['security']['token']['whitelist']) $routes['token-whitelist'][] = $route;
                    if ($config['security']['group']['whitelist']) $routes['group-whitelist'][] = $route;
                }
            }
        }
    }
}
$totalTokens = (config()->get('useToken')) ? db()->query('SELECT COUNT(VALUE_ID) as COUNT FROM b_uts_user WHERE ' . settings()->getTokenFieldCode() . ' IS NOT NULL')->fetchRaw()['COUNT'] : 0;

$requests['TOTAL'] = 0;
$requests[HttpClient::HTTP_GET] = 0;
$requests[HttpClient::HTTP_POST] = 0;
$requests[HttpClient::HTTP_PUT] = 0;
$requests[HttpClient::HTTP_PATCH] = 0;
$requests[HttpClient::HTTP_DELETE] = 0;
$requests[HttpClient::HTTP_HEAD] = 0;
$r = RequestResponseTable::getList([
    'select' => [
        'METHOD',
        'CNT' => new ExpressionField('METHOD', 'COUNT(*)'),
    ],
    'cache' => [
        'ttl' => 3600,
    ],
]);

while ($a = $r->fetchRaw()) {
    $requests[$a['METHOD']] = $a['CNT'];
    $requests['TOTAL'] += $a['CNT'];
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
$tabControl->Begin();
?>
<?php $tabControl->BeginNextTab() ?>
    <div class="paragraphs main">
        <div class="paragraph">
            <div class="meta">
                <ul>
                    <li><?= settings()->get('module')['name'] ?></li>
                    <li class="version"
                        title="<?= loc('ArtamonovRestVersion') ?>"><?= settings()->get('module')['version']['value'] ?></li>
                    <li class="version-date"
                        title="<?= loc('ArtamonovRestVersionDate') ?>"><?= date_format(date_create(settings()->get('module')['version']['date']), 'd.m.Y') ?></li>
                </ul>
            </div>
            <div class="description">
                <section>
                    <div class="header"><i class="fas fa-file-alt"></i><?= loc('ArtamonovRestDescription') ?></div>
                    <div class="body"><?= settings()->get('module')['description'] ?></div>
                </section>
            </div>
        </div>
        <div class="paragraph">
            <div class="meta">
                <ul>
                    <li title="<?= loc('ArtamonovRestVendor') ?>">
                        <?= loc('ArtamonovRestVendor') ?>:
                        <?= print_url(settings()->get('author')['website'], settings()->get('author')['company'], 'target="_blank"') ?>
                    </li>
                    <li class="marketplace"
                        title="<?= loc('ArtamonovRestMarketplace') ?>"><?= print_url(settings()->get('path')['marketplace'], loc('ArtamonovRestMarketplace'), 'target="_blank"') ?></li>
                </ul>
            </div>
        </div>
    </div>

<?php $tabControl->BeginNextTab() ?>
    <div class="paragraphs monitor">
        <div class="paragraph">
            <div class="meta">
                <ul>
                    <li><?= loc('ArtamonovRestModule') ?></li>
                </ul>
            </div>
            <div class="table-two-column">
                <section>
                    <div class="header"><i class="fas fa-cog"></i><?= loc('ArtamonovRestConfig') ?></div>
                    <div class="body">
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestModuleName') ?></div>
                            <div class="cell"
                                 style="color: <?= config()->get('useRestApi') ? 'rgb(34, 162, 59)' : 'rgb(206, 0, 0)' ?>"><?= config()->get('useRestApi') ? loc('ArtamonovRestEnabled') : loc('ArtamonovRestDisabled') ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestPathRestApi') ?></div>
                            <div class="cell"><?= config()->get('pathRestApi') ? config()->get('pathRestApi') : '-' ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestSiteList') ?></div>
                            <div class="cell"><?= config()->get('siteList') ? str_replace('|', ', ', config()->get('siteList')) : '-' ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestNativeRoute') ?></div>
                            <div class="cell"
                                 style="color: <?= config()->get('useExampleRoute') ? 'rgb(34, 162, 59)' : 'rgb(206, 0, 0)' ?>"><?= config()->get('useNativeRoute') ? loc('ArtamonovRestEnabled') : loc('ArtamonovRestDisabled') ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestExampleRoute') ?></div>
                            <div class="cell"
                                 style="color: <?= config()->get('useExampleRoute') ? 'rgb(34, 162, 59)' : 'rgb(206, 0, 0)' ?>"><?= config()->get('useExampleRoute') ? loc('ArtamonovRestEnabled') : loc('ArtamonovRestDisabled') ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestUseJournal') ?></div>
                            <div class="cell"
                                 style="color: <?= config()->get('useJournal') ? 'rgb(34, 162, 59)' : 'rgb(206, 0, 0)' ?>"><?= config()->get('useJournal') ? loc('ArtamonovRestEnabled') : loc('ArtamonovRestDisabled') ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestShowExamples') ?></div>
                            <div class="cell"
                                 style="color: <?= config()->get('showExamples') ? 'rgb(34, 162, 59)' : 'rgb(206, 0, 0)' ?>"><?= config()->get('showExamples') ? loc('ArtamonovRestEnabled') : loc('ArtamonovRestDisabled') ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestPhp') ?></div>
                            <div class="cell"><?= PHP_VERSION ?></div>
                        </div>
                    </div>
                </section>
                <section>
                    <div class="header"><i class="fas fa-key"></i><?= loc('ArtamonovRestAuth') ?></div>
                    <div class="body">
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestUseLoginPassword') ?></div>
                            <div class="cell"
                                 style="color: <?= config()->get('useLoginPassword') ? 'rgb(34, 162, 59)' : 'rgb(206, 0, 0)' ?>"><?= config()->get('useLoginPassword') ? loc('ArtamonovRestEnabled') : loc('ArtamonovRestDisabled') ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestUseToken') ?></div>
                            <div class="cell"
                                 style="color: <?= config()->get('useToken') ? 'rgb(34, 162, 59)' : 'rgb(206, 0, 0)' ?>"><?= config()->get('useToken') ? loc('ArtamonovRestEnabled') : loc('ArtamonovRestDisabled') ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestTokenKey') ?></div>
                            <div class="cell"><?= config()->get('tokenKey') ? config()->get('tokenKey') : '-' ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestTokenLifetime') ?></div>
                            <div class="cell"><?= config()->get('tokenLifetime') ? config()->get('tokenLifetime') : '-' ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestTokenFiledCode') ?></div>
                            <div class="cell"><?= settings()->getTokenFieldCode() ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestUseRequestLimit') ?></div>
                            <div class="cell"
                                 style="color: <?= config()->get('useRequestLimit') ? 'rgb(34, 162, 59)' : 'rgb(206, 0, 0)' ?>"><?= config()->get('useRequestLimit') ? loc('ArtamonovRestEnabled') : loc('ArtamonovRestDisabled') ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestTotalTokens') ?></div>
                            <div class="cell"><?= $totalTokens ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="paragraph">
            <div class="meta">
                <ul>
                    <li><?= loc('ArtamonovRestStatistics') ?></li>
                </ul>
            </div>
            <div class="table-two-column">
                <section>
                    <div class="header"><i class="fas fa-sitemap"></i><?= loc('ArtamonovRestRoutes') ?></div>
                    <div class="body">
                        <div class="row">
                            <div class="cell"><?= HttpClient::HTTP_GET ?></div>
                            <div class="cell"><?= $routes[HttpClient::HTTP_GET] ? count($routes[HttpClient::HTTP_GET]) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= HttpClient::HTTP_POST ?></div>
                            <div class="cell"><?= $routes[HttpClient::HTTP_POST] ? count($routes[HttpClient::HTTP_POST]) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= HttpClient::HTTP_PUT ?></div>
                            <div class="cell"><?= $routes[HttpClient::HTTP_PUT] ? count($routes[HttpClient::HTTP_PUT]) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= HttpClient::HTTP_PATCH ?></div>
                            <div class="cell"><?= $routes[HttpClient::HTTP_PATCH] ? count($routes[HttpClient::HTTP_PATCH]) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= HttpClient::HTTP_DELETE ?></div>
                            <div class="cell"><?= $routes[HttpClient::HTTP_DELETE] ? count($routes[HttpClient::HTTP_DELETE]) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= HttpClient::HTTP_HEAD ?></div>
                            <div class="cell"><?= $routes[HttpClient::HTTP_HEAD] ? count($routes[HttpClient::HTTP_HEAD]) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestRoutesDisabled') ?></div>
                            <div class="cell"><?= ($routes['disabled']) ? count($routes['disabled']) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestRoutesAuth') ?></div>
                            <div class="cell"><?= ($routes['auth']) ? count($routes['auth']) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestRoutesLoginWhitelist') ?></div>
                            <div class="cell"><?= ($routes['login-whitelist']) ? count($routes['login-whitelist']) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestRoutesTokensWhitelist') ?></div>
                            <div class="cell"><?= ($routes['token-whitelist']) ? count($routes['token-whitelist']) : 0 ?></div>
                        </div>
                        <div class="row">
                            <div class="cell"><?= loc('ArtamonovRestRoutesGroupsWhitelist') ?></div>
                            <div class="cell"><?= ($routes['group-whitelist']) ? count($routes['group-whitelist']) : 0 ?></div>
                        </div>
                    </div>
                </section>
                <section>
                    <div class="header"><i class="fas fa-sign-in-alt"></i><?= loc('ArtamonovRestRequests') ?></div>
                    <div class="body">
                        <?php foreach ($requests as $code => $value): ?>
                            <div class="row">
                                <?php if ($code === 'TOTAL'): ?>
                                    <div class="cell"><?= loc('ArtamonovRestTotalRequest') ?></div>
                                    <div class="cell">
                                        <a href="/bitrix/admin/rest-api-journal-request-response.php?lang=<?= LANG ?>"><?= $requests['TOTAL'] ?? 0 ?></a>
                                    </div>
                                <?php else: ?>
                                    <div class="cell"><?= $code ?></div>
                                    <div class="cell">
                                        <a href="/bitrix/admin/rest-api-journal-request-response.php?PAGEN_1=1&SIZEN_1=20&lang=<?= LANG ?>&set_filter=Y&adm_filter_applied=0&find_method=<?= $code ?>"><?= $value ?? 0 ?></a>
                                    </div>
                                <?php endif ?>
                            </div>
                        <?php endforeach ?>
                    </div>
                </section>
            </div>
        </div>
    </div>
<?php
$tabControl->End();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
