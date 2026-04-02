<?php
/**
 * AJAX-обработчик входа для модального окна в header.
 * POST: email, password, remember → JSON { success, redirect|message }
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    die();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember']) ? 'Y' : 'N';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Заполните email и пароль']);
    die();
}

$result = $USER->Login($email, $password, $remember);
if ($result === true) {
    echo json_encode(['success' => true, 'redirect' => '/profile/']);
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный email или пароль']);
}
