<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
use Bitrix\Main\Loader;
Loader::includeModule('iblock');

$APPLICATION->SetTitle('Встреча выпускников');

$cmsImage = '';
if (defined('IBLOCK_PROJECTS_ID') && IBLOCK_PROJECTS_ID > 0) {
    $dbEl = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => IBLOCK_PROJECTS_ID, 'ACTIVE' => 'Y', 'PROPERTY_DETAIL_URL' => '/projects/conference/'],
        false, false,
        ['ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE']
    );
    if ($arEl = $dbEl->GetNext()) {
        $picId = $arEl['DETAIL_PICTURE'] ?: $arEl['PREVIEW_PICTURE'];
        if ($picId) $cmsImage = CFile::GetPath($picId);
    }
}

$tplPath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/my_template/project_conference.html';
$html    = file_get_contents($tplPath);
if (preg_match('/<main>(.*?)<\/main>/si', $html, $m)) {
    $content = $m[1];
    $content = str_replace('src="img/',  'src="/local/templates/my_template/assets/img/', $content);
    $content = str_replace("src='img/",  "src='/local/templates/my_template/assets/img/", $content);
    $content = str_replace('href="support.html"',  'href="/support/"',  $content);
    $content = str_replace('href="projects.html"', 'href="/projects/"', $content);
    if ($cmsImage) {
        $content = str_replace(
            '/local/templates/my_template/assets/img/reference-page/banner-other-img.png',
            htmlspecialchars($cmsImage),
            $content
        );
    }
    echo '<main>' . $content . '</main>';
}
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
