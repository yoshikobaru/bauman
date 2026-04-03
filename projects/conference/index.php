<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Встреча выпускников');
$tplPath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/my_template/project_conference.html';
$html = file_get_contents($tplPath);
if (preg_match('/<main>(.*?)<\/main>/si', $html, $m)) {
    $content = $m[1];
    $content = str_replace('src="img/', 'src="/local/templates/my_template/img/', $content);
    $content = str_replace("src='img/", "src='/local/templates/my_template/img/", $content);
    $content = str_replace('href="support.html"', 'href="/support/"', $content);
    $content = str_replace('href="projects.html"', 'href="/projects/"', $content);
    echo '<main>' . $content . '</main>';
}
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
