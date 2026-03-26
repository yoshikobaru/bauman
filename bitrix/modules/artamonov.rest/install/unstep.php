<?php

/**
 * @var CMain $APPLICATION
 */

use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;

$config = require __DIR__ . '/../settings.php';

$APPLICATION->SetTitle(Loc::getMessage('ArtamonovRestUninstallPageTitle', ['#MODULE_NAME#' => $config['module']['name']]));

$textPage = Loc::getMessage('ArtamonovRestUninstallMessageHeader');
$textPage .= Loc::getMessage('ArtamonovRestUninstallMessageBody', ['#MODULE_NAME#' => $config['module']['name']]);

$bitrixDir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/init.php';
$localDir = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
$path = '';
if (is_file($localDir)) {
    $path = $localDir;
} elseif (is_file($bitrixDir)) {
    $path = $bitrixDir;
}
if ($path) {
    $content = File::getFileContents($path);
    if (mb_stripos($content, $config['module']['connectionString']) !== false) {
        File::putFileContents(str_replace('.php', '-bcp-' . time() . '.php', $path), $content);
        $content = str_replace($config['module']['connectionString'], '// ' . $config['module']['connectionString'], $content);
        File::putFileContents($path, $content);
        $textPage .= Loc::getMessage('ArtamonovRestUninstallModuleDisconnected', ['#PATH#' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $path)]);
    }
}
$textPage .= Loc::getMessage('ArtamonovRestUninstallMessageFooter');
CAdminMessage::ShowNote($textPage);
