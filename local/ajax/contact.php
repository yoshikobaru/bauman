<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK',    true);
define('NOT_CHECK_PERMISSIONS', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    die();
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name)  { echo json_encode(['success' => false, 'message' => 'Введите имя']);   die(); }
if (!$email) { echo json_encode(['success' => false, 'message' => 'Введите email']); die(); }
if ($phone !== '' && function_exists('po_is_valid_phone_chars') && !po_is_valid_phone_chars($phone)) {
    echo json_encode(['success' => false, 'message' => 'Телефон может содержать только цифры, пробел, + и -.']);
    die();
}

if (function_exists('po_sendAdminEmail')) {
    po_sendAdminEmail('contact', [
        'name'  => $name,
        'email' => $email,
        'phone' => $phone,
        'msg'   => $message,
    ]);
}

if (function_exists('po_logAction')) {
    po_logAction('form_submit', 'contact', 0, 'Связаться с организаторами: ' . $name);
}

echo json_encode(['success' => true]);
die();
