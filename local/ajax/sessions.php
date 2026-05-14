<?php
/**
 * AJAX-обработчик для работы с активными сессиями пользователя.
 * GET  — получить список активных сессий
 * POST — завершить указанную сессию
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

// ============================================================
// GET — список активных сессий
// ============================================================
if ($method === 'GET') {
    if (!($USER instanceof CUser) || !$USER->IsAuthorized()) {
        echo json_encode(['success' => false, 'message' => 'Не авторизован']);
        die();
    }
    $userId = (int)$USER->GetID();
    $sessions = po_get_user_sessions($userId);
    echo json_encode(['success' => true, 'sessions' => $sessions]);
    die();
}

// ============================================================
// POST — завершить сессию (CSRF-защита)
// ============================================================
if ($method === 'POST') {
    if (!($USER instanceof CUser) || !$USER->IsAuthorized()) {
        echo json_encode(['success' => false, 'message' => 'Не авторизован']);
        die();
    }

    if (!check_bitrix_sessid()) {
        echo json_encode(['success' => false, 'message' => 'Сессия устарела. Обновите страницу.']);
        die();
    }

    $userId = (int)$USER->GetID();
    $sessionHash = trim($_POST['session_hash'] ?? '');

    if ($sessionHash === '') {
        echo json_encode(['success' => false, 'message' => 'Не указан идентификатор сессии']);
        die();
    }

    $result = po_terminate_session($userId, $sessionHash);
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Не удалось завершить сессию. Возможно, она уже завершена.']);
    }
    die();
}

echo json_encode(['success' => false, 'message' => 'Неизвестный запрос']);
die();
