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

$adminEmail = defined('PO_ADMIN_EMAIL') ? PO_ADMIN_EMAIL : 'info@bauman-polytech.ru';

$subject = 'Связаться с организаторами / Стать партнёром';
$body = "Имя: $name\nEmail: $email\n";
if ($phone)   $body .= "Телефон: $phone\n";
if ($message) $body .= "\nСообщение:\n$message\n";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "From: {$adminEmail}\r\n";
$headers .= "Reply-To: " . ($email) . "\r\n";

$encSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
@mail($adminEmail, $encSubject, $body, $headers);

if (function_exists('po_logAction')) {
    po_logAction('form_submit', 'contact', 0, 'Связаться с организаторами: ' . $name);
}

echo json_encode(['success' => true]);
die();
