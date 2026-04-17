<?php
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('NO_KEEP_STATISTIC', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: text/plain; charset=UTF-8');

if (!function_exists('po_get_paykeeper_config') || !function_exists('po_paykeeper_get_account_for_callback_payload')) {
    http_response_code(500);
    echo 'Configuration error';
    exit;
}

$config = po_get_paykeeper_config();
$payload = $_POST;

$paykeeperAccount = po_paykeeper_get_account_for_callback_payload($payload, $config);
if (!$paykeeperAccount) {
    http_response_code(400);
    echo 'Invalid signature';
    exit;
}
$secretWord = (string)($paykeeperAccount['secret_word'] ?? '');
$accountKey = (string)($paykeeperAccount['account_key'] ?? '');

$orderId = (string)($payload['orderid'] ?? '');
$paymentId = (string)($payload['id'] ?? '');
$sum = (string)($payload['sum'] ?? '');
$applicationId = function_exists('po_paykeeper_extract_application_id')
    ? po_paykeeper_extract_application_id($orderId)
    : 0;

if ($paymentId === '' || $applicationId <= 0 || $sum === '') {
    http_response_code(400);
    echo 'Invalid payload';
    exit;
}

if (!\Bitrix\Main\Loader::includeModule('highloadblock') || !defined('HL_APPLICATIONS_ID') || HL_APPLICATIONS_ID <= 0) {
    http_response_code(500);
    echo 'Storage unavailable';
    exit;
}

$hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
if (!$hlEntity) {
    http_response_code(500);
    echo 'Storage unavailable';
    exit;
}

$hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
$application = $hlClass::getById($applicationId)->fetch();
if (!$application) {
    http_response_code(404);
    echo 'Application not found';
    exit;
}

$data = [];
if (!empty($application['UF_DATA'])) {
    $decoded = json_decode((string)$application['UF_DATA'], true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}

$paymentData = [];
if (!empty($data['payment']) && is_array($data['payment'])) {
    $paymentData = $data['payment'];
}
if (!empty($paymentData['account_key']) && (string)$paymentData['account_key'] !== $accountKey) {
    http_response_code(400);
    echo 'Account mismatch';
    exit;
}

$expectedAmount = '';
if (!empty($paymentData['amount_rub'])) {
    $expectedAmount = (string)$paymentData['amount_rub'];
} elseif (function_exists('po_paykeeper_normalize_amount')) {
    $expectedAmount = (string)po_paykeeper_normalize_amount((string)($data['amount'] ?? ''));
}

$paidAmount = number_format((float)$sum, 2, '.', '');
if ($expectedAmount !== '' && $paidAmount !== number_format((float)$expectedAmount, 2, '.', '')) {
    http_response_code(400);
    echo 'Amount mismatch';
    exit;
}

if (($paymentData['status'] ?? '') === 'paid' && (string)($paymentData['payment_id'] ?? '') === $paymentId) {
    echo po_paykeeper_build_callback_ack($paymentId, $secretWord);
    exit;
}

$paymentData['status'] = 'paid';
$paymentData['payment_id'] = $paymentId;
$paymentData['order_id'] = $orderId;
$paymentData['amount_rub'] = $paidAmount;
$paymentData['paid_at'] = date('c');
$paymentData['account_key'] = $accountKey;
$paymentData['account_base_url'] = (string)($paykeeperAccount['base_url'] ?? '');
$paymentData['ps_id'] = (string)($payload['ps_id'] ?? '');
$paymentData['client_email'] = (string)($payload['client_email'] ?? '');
$paymentData['client_phone'] = (string)($payload['client_phone'] ?? '');

$data['payment'] = $paymentData;
$updateResult = $hlClass::update($applicationId, [
    'UF_STATUS' => 'paid',
    'UF_DATA' => json_encode($data, JSON_UNESCAPED_UNICODE),
]);

if (!$updateResult->isSuccess()) {
    http_response_code(500);
    echo 'Update failed';
    exit;
}

if (function_exists('po_logAction')) {
    po_logAction('payment_success', 'application', $applicationId, 'PayKeeper payment: ' . $paymentId);
}

echo po_paykeeper_build_callback_ack($paymentId, $secretWord);
