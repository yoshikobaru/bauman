<?php

use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$settings = require __DIR__.'/../../../settings.php';

if (Loader::includeSharewareModule($settings['module']['id']) === Loader::MODULE_DEMO_EXPIRED) {
    LocalRedirect('/bitrix/admin/partner_modules.php?lang=' . LANG);
}

Loader::includeModule($settings['module']['id']);
page()->checkAccess('accessDocumentation');
page()->addCss('/bitrix/css/' . settings()->get('module')['id'] . '/' . basename(__DIR__) . '-' . basename(__FILE__, '.php') . '.min.css');
page()->setTitle(loc('ArtamonovRestRoutesPageTitle'));
$tabs = [
    ['DIV' => 'tab-1', 'TAB' => helper()->GET(), 'TITLE' => helper()->GET()],
    ['DIV' => 'tab-2', 'TAB' => helper()->POST(), 'TITLE' => helper()->POST()],
    ['DIV' => 'tab-3', 'TAB' => helper()->PUT(), 'TITLE' => helper()->PUT()],
    ['DIV' => 'tab-4', 'TAB' => helper()->PATCH(), 'TITLE' => helper()->PATCH()],
    ['DIV' => 'tab-5', 'TAB' => helper()->DELETE(), 'TITLE' => helper()->DELETE()],
    ['DIV' => 'tab-6', 'TAB' => helper()->HEAD(), 'TITLE' => helper()->HEAD()],
];
$tabControl = new CAdminTabControl('tabControl', $tabs);
$routes = [];
$dir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'routes';
$files[$dir] = array_diff(scandir($dir), ['..', '.']);
if (config()->get('localRouteMap')) {
    $dir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . config()->get('localRouteMap');
    $files[$dir] = array_diff(scandir($dir), ['..', '.']);
}
foreach ($files as $dir => $items) {
    foreach ($items as $file) {
        if ((!config()->get('useNativeRoute') && $file === settings()->get('file')['native']) || (!config()->get('useExampleRoute') && $file === settings()->get('file')['example'])) continue;
        if (config()->get('localRouteMap') && mb_strpos($dir, config()->get('localRouteMap'))) {
            $file = DIRECTORY_SEPARATOR . config()->get('localRouteMap') . DIRECTORY_SEPARATOR . $file;
        } else {
            $file = BX_ROOT . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . settings()->get('module')['id'] . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . $file;
        }
        if (is_array($ar = require $_SERVER['DOCUMENT_ROOT'] . $file)) {
            foreach ($ar as $type => $r) {
                foreach ($r as $route => $config) {
                    if ($config['documentation']['exclude']['admin']) continue;
                    $routes[$type][$file][$route] = $config;
                }
            }
        }
    }
}
$groups = [];
$by = 'NAME';
$order = 'ASC';
$result = CGroup::GetList($by, $order, ['ACTIVE' => 'Y', 'ANONYMOUS' => 'N']);
while ($group = $result->fetch()) {
    $groups[$group['ID']] = $group['NAME'];
}
$pathClass = [];
function printRoutes($data, $type, $groups, &$pathClass)
{
    if (!$data) {
        ?>
        <div class="routes-empty"><i class="far fa-folder-open"></i> ...</div>
        <?
        return false;
    }
    ?>
    <div class="routes <?= mb_strtolower($type) ?>">
        <? foreach ($data as $file => $routes): ?>
            <? foreach ($routes as $route => $config): ?>
                <div class="route">
                    <ul class="meta">
                        <li class="path">
                            <a title="<?= loc('ArtamonovRestEditHint') ?>"
                               href="/bitrix/admin/fileman_file_edit.php?path=<?= $file ?>&full_src=Y&lang=<?= LANG ?>">
                                <?= config()->get('pathRestApi') !== helper()->ROOT() ? config()->get('pathRestApi') . '/' : '' ?><?= trim($route, '/') ?>
                            </a>
                        </li>
                        <? if ($config['controller']): ?>
                            <li class="controller">
                                <?
                                $pathClass = [];
                                $errorText = '';

                                if (mb_strpos($config['controller'], '@') !== false) {
                                    $namespace = explode('@', $config['controller'])[0];

                                    if (!$pathClass[$namespace]) {

                                        $errorText = loc('ArtamonovRestClassNotFound');

                                        if (!class_exists($namespace)) {

                                            spl_autoload_register(function ($file) {
                                                $file = mb_strtolower($file);
                                                $file = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $file) . '.php';

                                                if (is_file($file)) {
                                                    require $file;
                                                    $errorText = '';
                                                }
                                            });

                                            if (class_exists($namespace)) {
                                                $reflector = new \ReflectionClass($namespace);
                                                $pathClass[$namespace] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $reflector->getFileName());
                                                $errorText = '';
                                            }

                                        } else if ($reflector = new \ReflectionClass($namespace)) {
                                            $pathClass[$namespace] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $reflector->getFileName());
                                            $errorText = '';
                                        } else {
                                            $pathClass[$namespace] = '';
                                        }


                                    }

                                } else {
                                    $namespace = str_replace($_SERVER['DOCUMENT_ROOT'], '', $config['controller']);
                                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $namespace)) {
                                        $pathClass[$namespace] = $namespace;
                                        $errorText = loc('ArtamonovRestFileNotFound');
                                    } else {
                                        $pathClass[$namespace] = '';
                                        $errorText = loc('ArtamonovRestFileNotFound');
                                    }
                                    $config['controller'] = $namespace;
                                }
                                ?>

                                <? if ($pathClass[$namespace]): ?>
                                    <a title="<?= loc('ArtamonovRestEditHint') ?>"
                                       href="/bitrix/admin/fileman_file_edit.php?path=<?= $pathClass[$namespace] ?>&full_src=Y&lang=<?= LANG ?>">
                                        <?= trim($config['controller'], '\\, /') ?>
                                    </a>
                                <? else: ?>
                                    <?= trim($config['controller'], '\\, /') ?><i class="fas fa-exclamation-circle"
                                                                                  title="<?= $errorText ?>"
                                                                                  style="color: rgb(255, 245, 245);transform: translateY(1px);margin-left: 10px;box-shadow: 0 0 10px 2px red;border-radius: 50%;"></i>
                                <? endif ?>
                            </li>
                        <? endif ?>
                        <? if ($config['contentType']): ?>
                            <li class="content-type"
                                title="<?= loc('ArtamonovRestContentTypedHint') ?>"><?= $config['contentType'] ?></li>
                        <? endif ?>
                        <? if ((config()->get('useToken') || config()->get('useLoginPassword')) && $config['security']['auth']['required']): ?>
                            <li class="security-auth"
                                title="<?= loc('ArtamonovRestSecurityAuthHint') ?>"><?= loc('ArtamonovRestSecurityAuth') ?></li>
                        <? endif ?>
                        <? if ($config['active'] === false): ?>
                            <li class="disabled"
                                title="<?= loc('ArtamonovRestDisabledHint') ?>"><i class="fas fa-power-off"></i></li>
                        <? endif ?>
                    </ul>
                    <? if ($config['description']): ?>
                        <div class="description">
                            <section>
                                <div class="header"><i
                                            class="fas fa-file-alt"></i><?= loc('ArtamonovRestDescription') ?></div>
                                <div class="body"><?= $config['description'] ?></div>
                            </section>
                        </div>
                    <? endif ?>
                    <? if ($config['parameters']): ?>
                        <section>
                            <div class="header"><i class="fas fa-upload"></i><?= loc('ArtamonovRestParameters') ?></div>
                            <div class="body">
                                <?php
                                foreach ($config['parameters'] as $code => $parameter) {

                                    $arCode = [];
                                    printParameters(1, $code, $parameter, $arCode, mb_strpos($code, 'separator:') !== false);
                                }
                                ?>
                            </div>
                        </section>
                    <? endif ?>
                    <? if ($config['example']['request']['url'] && $config['example']['request']['response']['json']): ?>
                        <section>
                            <div class="header"><i
                                        class="fas fa-download"></i><?= loc('ArtamonovRestResponseExample') ?>
                            </div>
                            <div class="body" style="margin-bottom: 15px">
                                <?
                                $config['example']['request']['url'] = str_replace(['#DOMAIN#', '#API#'], [$_SERVER['HTTP_HOST'], config()->get('pathRestApi')], $config['example']['request']['url']);
                                ?>
                                <a href="<?= $config['example']['request']['url'] ?>"
                                   target="_blank"><?= $config['example']['request']['url'] ?></a>
                            </div>
                            <div class="body">
                                <pre><?= json_encode(json_decode($config['example']['request']['response']['json']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                            </div>
                        </section>
                    <? endif ?>
                    <? if ((config()->get('useToken') || config()->get('useLoginPassword')) && $config['security']['auth']['required'] && ($config['security']['login']['whitelist'] || $config['security']['token']['whitelist'] || $config['security']['group']['whitelist'])): ?>
                        <div class="whitelist" style="grid-template-columns: 1fr 1fr 1fr;">
                            <? if (config()->get('useLoginPassword') && $config['security']['login']['whitelist']): ?>
                                <section>
                                    <div class="header"><i
                                                class="fas fa-key"></i><?= loc('ArtamonovRestSecurityUsersWhitelist') ?>
                                    </div>
                                    <div class="body">
                                        <? foreach ($config['security']['login']['whitelist'] as $item): ?>
                                            <div class="row">
                                                <div class="cell"><?= $item ?></div>
                                            </div>
                                        <? endforeach ?>
                                    </div>
                                </section>
                            <? endif ?>
                            <? if (config()->get('useToken') && $config['security']['token']['whitelist']): ?>
                                <section>
                                    <div class="header"><i
                                                class="fas fa-key"></i><?= loc('ArtamonovRestSecurityTokensWhitelist') ?>
                                    </div>
                                    <div class="body">
                                        <? foreach ($config['security']['token']['whitelist'] as $item): ?>
                                            <div class="row">
                                                <div class="cell"><?= $item ?></div>
                                            </div>
                                        <? endforeach ?>
                                    </div>
                                </section>
                            <? endif ?>
                            <? if ($config['security']['group']['whitelist']): ?>
                                <section>
                                    <div class="header"><i
                                                class="fas fa-user-friends"></i><?= loc('ArtamonovRestSecurityGroupsWhitelist') ?>
                                    </div>
                                    <div class="body">
                                        <? foreach ($config['security']['group']['whitelist'] as $id): ?>
                                            <? if ($groups[$id]): ?>
                                                <div class="row">
                                                    <div class="cell"><?= $groups[$id] ?></div>
                                                </div>
                                            <? endif ?>
                                        <? endforeach ?>
                                    </div>
                                </section>
                            <? endif ?>
                        </div>

                    <? endif ?>
                </div>
                <br>
            <? endforeach ?>
        <? endforeach ?>
    </div>
    <?
}

if (!function_exists('printParameters')) {
    function printParameters($level, $code, $parameter, $arCode, $isSeparator)
    {
        if ($isSeparator === true) {
            ?>
            <div class="row separator">
                <div class="cell">
                    <?php if ($parameter['title']): ?>
                        <div class="separator-title"><i class="fas fa-bars"></i><?= $parameter['title'] ?></div>
                    <?php else: ?>
                        &nbsp;
                    <?php endif ?>
                </div>
                <div class="cell">&nbsp;</div>
                <div class="cell">&nbsp;</div>
                <div class="cell">&nbsp;</div>
                <div class="cell">&nbsp;</div>
            </div>
            <?php
            return;
        }
        $arCode[$level] = $code;
        $strCode = implode('<i class="fas fa-long-arrow-alt-right" style="margin: 0 6px;color: #d51c1c;font-size: 12px;"></i>', $arCode);
        ?>
        <div class="row">
            <div class="cell"><?= $strCode ?></div>
            <div class="cell"><?= $parameter['type'] ?></div>
            <div class="cell"><? if ($parameter['required']): ?><span
                        class="required">required</span><? else: ?>optional<? endif ?></div>
            <div class="cell"
                 style="max-width: 600px;"><?= $parameter['possibleValue'] ? implode('<span style="color: #c6c6c6; margin: 0 2px">,</span>', $parameter['possibleValue']) : '<span style="color: #c6c6c6">-</span>' ?></div>
            <div class="cell"><?= $parameter['description'] ?></div>
        </div>
        <?php
        // Дочерние параметры
        $parameters = [];
        if (isset($parameter['parameters'][0])) {
            $parameters = $parameter['parameters'][0];
        } else if (isset($parameter['parameters'])) {
            $parameters = $parameter['parameters'];
        }
        if (count($parameters) > 0) {
            $level++;
            foreach ($parameters as $code => $parameter) {
                printParameters($level, $code, $parameter, $arCode, mb_strpos($code, 'separator:') !== false);
            }
        }
    }
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
$tabControl->Begin();
$tabControl->BeginNextTab();
printRoutes($routes[helper()->GET()], helper()->GET(), $groups, $pathClass);
$tabControl->BeginNextTab();
printRoutes($routes[helper()->POST()], helper()->POST(), $groups, $pathClass);
$tabControl->BeginNextTab();
printRoutes($routes[helper()->PUT()], helper()->PUT(), $groups, $pathClass);
$tabControl->BeginNextTab();
printRoutes($routes[helper()->PATCH()], helper()->PATCH(), $groups, $pathClass);
$tabControl->BeginNextTab();
printRoutes($routes[helper()->DELETE()], helper()->DELETE(), $groups, $pathClass);
$tabControl->BeginNextTab();
printRoutes($routes[helper()->HEAD()], helper()->HEAD(), $groups, $pathClass);
$tabControl->End();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
