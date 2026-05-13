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
$subject = trim($_POST['subject'] ?? '');
$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');

if (!$email)   { echo json_encode(['success' => false, 'message' => 'Введите e-mail']); die(); }
if (!$subject) { echo json_encode(['success' => false, 'message' => 'Введите тему письма']); die(); }
if ($phone !== '' && function_exists('po_is_valid_phone_chars') && !po_is_valid_phone_chars($phone)) {
    echo json_encode(['success' => false, 'message' => 'Телефон может содержать только цифры, пробел, + и -.']);
    die();
}

if (function_exists('po_sendAdminEmail')) {
    po_sendAdminEmail('contact', [
        'name'    => $name,
        'email'   => $email,
        'subject' => $subject,
        'phone'   => $phone,
        'msg'     => $message,
    ]);
}

if (function_exists('po_logAction')) {
    po_logAction('form_submit', 'contact', 0, 'Связаться с организаторами: ' . $email);
}

echo json_encode(['success' => true]);
die();
