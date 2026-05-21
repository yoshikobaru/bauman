<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
use Bitrix\Main\Loader;
Loader::includeModule('iblock');

$APPLICATION->SetTitle('Конференция PolytechExpo');

// Пытаемся получить CMS-фото из инфоблока проектов
$cmsImage = '';
$projectRow = function_exists('po_get_iblock_project_by_detail_url')
    ? po_get_iblock_project_by_detail_url('/projects/politech-expo/')
    : null;
if (!$projectRow) {
    $projectRow = ['detail_url' => '/projects/politech-expo/'];
}
if ($projectRow) {
    $dbEl = CIBlockElement::GetByID((int)$projectRow['id']);
    if ($arEl = $dbEl->GetNext()) {
        $picId = $arEl['DETAIL_PICTURE'] ?: $arEl['PREVIEW_PICTURE'];
        if ($picId) $cmsImage = CFile::GetPath($picId);
    }
}

$tplPath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/my_template/project_PolytechExpo.html';
$html    = file_get_contents($tplPath);
if (preg_match('/<main>(.*?)<\/main>/si', $html, $m)) {
    $content = $m[1];
    // Исправляем пути: шаблон использует img/, в Bitrix это assets/img/
    $content = str_replace('src="img/',  'src="/local/templates/my_template/assets/img/', $content);
    $content = str_replace("src='img/",  "src='/local/templates/my_template/assets/img/", $content);
    $content = str_replace('href="projects.html"', 'href="/projects/"', $content);
    if (function_exists('po_replace_support_links_in_html')) {
        $content = po_replace_support_links_in_html($content, $projectRow);
    } else {
        $content = str_replace('href="support.html"', 'href="/support/"', $content);
    }
    // Заменяем статичную правую фотку на CMS-изображение, если оно задано в инфоблоке
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
