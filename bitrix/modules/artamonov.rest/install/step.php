<?php

/**
 * @var CMain $APPLICATION
 */

use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

$date = new DateTime();

$APPLICATION->SetTitle(Loc::getMessage('ArtamonovRestInstallPageTitle', ['#MODULE_NAME#' => settings()->get('module')['name']]));
$textPage = Loc::getMessage('ArtamonovRestInstallMessageHeader');
$textPage .= Loc::getMessage('ArtamonovRestInstallMessageBody', ['#MODULE_NAME#' => settings()->get('module')['name']]);
$bitrixDir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/init.php';
$localDir = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
$connectionString = "<?php" . PHP_EOL . PHP_EOL;
$connectionString .= "/**" . PHP_EOL;
$connectionString .= " * " . settings()->get('module')['name'] . PHP_EOL;
$connectionString .= " * " . PHP_EOL;
$connectionString .= " * @install  " . $date->toString() . PHP_EOL;
$connectionString .= " * @package  " . settings()->get('module')['id'] . PHP_EOL;
$connectionString .= " * @website  https://marketplace.1c-bitrix.ru/solutions/" . settings()->get('module')['id'] . PHP_EOL;
$connectionString .= " */" . PHP_EOL;
$connectionString .= settings()->get('module')['connectionString'] . PHP_EOL;
$path = '';
if (is_file($localDir)) {
    $path = $localDir;
} elseif (is_file($bitrixDir)) {
    $path = $bitrixDir;
}
if (!$path) {
    File::putFileContents($localDir, $connectionString);
    $textPage = str_replace('#PATH#', str_replace($_SERVER['DOCUMENT_ROOT'], '', $localDir), $textPage);
} else {
    $content = File::getFileContents($path);

    if (mb_stripos($content, settings()->get('module')['connectionString']) === false) {
        File::putFileContents(str_replace('.php', '-bcp-' . time() . '.php', $path), $content);
        if (mb_stripos($content, '<?php') !== false) {
            $content = str_replace_once('<?php', $connectionString, $content);
        } elseif (mb_stripos($content, '<?') !== false) {
            $content = str_replace_once('<?', $connectionString, $content);
        } else {
            $content = $connectionString;
        }
    } elseif (mb_stripos($content, '// ' . settings()->get('module')['connectionString']) !== false) {
        $content = str_replace('// ' . settings()->get('module')['connectionString'], settings()->get('module')['connectionString'], $content);
    }
    File::putFileContents($path, $content);
    $textPage = str_replace('#PATH#', str_replace($_SERVER['DOCUMENT_ROOT'], '', $path), $textPage);
}
function str_replace_once($search, $replace, $text)
{
    $pos = mb_strpos($text, $search);
    return $pos !== false ? substr_replace($text, $replace, $pos, mb_strlen($search)) : $text;
}

$textPage .= Loc::getMessage('ArtamonovRestInstallMessageFooter');
CAdminMessage::ShowNote($textPage);
