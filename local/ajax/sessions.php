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

// Оборачиваем всё в try-catch для отладки 500-й ошибки
try {
    $method = $_SERVER['REQUEST_METHOD'];

    /**
     * PHP 7-compatible str_contains polyfill.
     */
    $str_contains = function(string $haystack, string $needle): bool {
        return $needle === '' || mb_strpos($haystack, $needle) !== false;
    };

    // ============================================================
    // GET — список активных сессий
    // ============================================================
    if ($method === 'GET') {
        if (!($USER instanceof CUser) || !$USER->IsAuthorized()) {
            echo json_encode(['success' => false, 'message' => 'Не авторизован']);
            die();
        }
        $userId = (int)$USER->GetID();
        $sessions = [];

        // Пытаемся использовать функцию из init.php
        if (function_exists('po_get_user_sessions')) {
            try {
                $result = po_get_user_sessions($userId);
                if (is_array($result)) $sessions = $result;
            } catch (Throwable $e) {
                // ignore
            }
        }

        // Всегда возвращаем хотя бы текущую сессию (hard fallback)
        if (empty($sessions)) {
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $uaLower = function_exists('mb_strtolower') ? mb_strtolower($ua) : strtolower($ua);

            $deviceName = 'Браузер';
            $iconType = 'desktop';

            if ($str_contains($uaLower, 'iphone') || $str_contains($uaLower, 'ipad')) {
                $deviceName = 'iPhone';
                $iconType = 'smartphone';
            } elseif ($str_contains($uaLower, 'android')) {
                $deviceName = 'Android';
                $iconType = 'smartphone';
            } elseif ($str_contains($uaLower, 'mobile')) {
                $deviceName = 'Мобильное устройство';
                $iconType = 'smartphone';
            } elseif ($str_contains($uaLower, 'chrome')) {
                $deviceName = 'Chrome';
            } elseif ($str_contains($uaLower, 'safari')) {
                $deviceName = 'Safari';
            } elseif ($str_contains($uaLower, 'firefox')) {
                $deviceName = 'Firefox';
            } elseif ($str_contains($uaLower, 'yabrowser')) {
                $deviceName = 'Яндекс.Браузер';
            } elseif ($str_contains($uaLower, 'edg/')) {
                $deviceName = 'Edge';
            }

            $currentSessId = '';
            if (session_status() === PHP_SESSION_ACTIVE) {
                $currentSessId = session_id();
            }

            $sessions = [[
                'hash'          => md5($currentSessId ?: 'po_session'),
                'device_type'   => $iconType === 'smartphone' ? 'mobile' : 'desktop',
                'device_name'   => $deviceName,
                'icon_type'     => $iconType,
                'city'          => '',
                'last_activity' => 'Сегодня',
                'is_current'    => true,
                'ip'            => $_SERVER['REMOTE_ADDR'] ?? '',
            ]];
        }

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

        if (!function_exists('po_terminate_session')) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
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
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка сервера: ' . $e->getMessage(),
        'debug'   => $e->getFile() . ':' . $e->getLine(),
    ]);
}
die();
