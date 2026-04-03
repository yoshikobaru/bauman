<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Референс-визит в МАШ ЮНИТ');
$tplPath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/my_template/reference_visit_mash.html';
$html = file_get_contents($tplPath);
if (preg_match('/<main>(.*?)<\/main>/si', $html, $m)) {
    $content = $m[1];
    // Исправляем пути к ресурсам (img/ -> /local/templates/my_template/img/)
    $content = str_replace('src="img/', 'src="/local/templates/my_template/img/', $content);
    $content = str_replace("src='img/", "src='/local/templates/my_template/img/", $content);
    $content = str_replace('href="support.html"', 'href="/support/"', $content);
    $content = str_replace('href="reference.html"', 'href="/reference/"', $content);
    echo '<main>' . $content . '</main>';
}
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
