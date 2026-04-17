<?php
/**
 * Настройки PayKeeper (маршрутизация проектов и аккаунтов).
 * URL: /local/admin/po_paykeeper_settings.php
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Loader;

$isModerator = $USER->IsAdmin()
    || (defined('PO_MODERATOR_ID') && in_array(PO_MODERATOR_ID, $USER->GetUserGroupArray()));

if (!$USER->IsAuthorized()) {
    $APPLICATION->AuthForm('Авторизуйтесь для доступа к настройкам PayKeeper.');
}
if (!$isModerator) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
    echo '<div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message">Нет доступа. Требуется роль Модератора или Администратора.</div></div>';
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
    die();
}

$message = '';
$error = '';

$getConfig = function (): array {
    $raw = (string)COption::GetOptionString('main', 'po_paykeeper_config_json', '');
    if ($raw === '') {
        return [
            'default_account' => '',
            'accounts' => [],
            'project_accounts' => [],
            'project_aliases' => [],
        ];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [
            'default_account' => '',
            'accounts' => [],
            'project_accounts' => [],
            'project_aliases' => [],
        ];
    }
    return [
        'default_account' => (string)($decoded['default_account'] ?? ''),
        'accounts' => isset($decoded['accounts']) && is_array($decoded['accounts']) ? $decoded['accounts'] : [],
        'project_accounts' => isset($decoded['project_accounts']) && is_array($decoded['project_accounts']) ? $decoded['project_accounts'] : [],
        'project_aliases' => isset($decoded['project_aliases']) && is_array($decoded['project_aliases']) ? $decoded['project_aliases'] : [],
    ];
};

$cfg = $getConfig();

$projectsFromSite = [
    'Пожертвование на ведение уставной деятельности',
    'Реставрация Ротонды',
    'Конференция PolytechExpo',
    'Конференция Встреча выпускников',
    'Попечительский совет МТ4',
];
if (Loader::includeModule('iblock') && defined('IBLOCK_PROJECTS_ID') && IBLOCK_PROJECTS_ID > 0) {
    $dbProj = CIBlockElement::GetList(
        ['SORT' => 'ASC'],
        ['IBLOCK_ID' => IBLOCK_PROJECTS_ID, 'ACTIVE' => 'Y'],
        false,
        false,
        ['NAME']
    );
    while ($row = $dbProj->GetNext()) {
        $name = trim((string)($row['NAME'] ?? ''));
        if ($name !== '') {
            $projectsFromSite[] = $name;
        }
    }
}
$projectsFromSite = array_values(array_unique($projectsFromSite));
sort($projectsFromSite);

$accountsToText = function (array $accounts): string {
    $lines = [];
    foreach ($accounts as $key => $acc) {
        if (!is_array($acc)) {
            continue;
        }
        $lines[] = implode('|', [
            (string)$key,
            (string)($acc['base_url'] ?? ''),
            (string)($acc['secret_word'] ?? ''),
            (string)($acc['username'] ?? ''),
            (string)($acc['password'] ?? ''),
        ]);
    }
    return implode("\n", $lines);
};

$routesToText = function (array $routes): string {
    $lines = [];
    foreach ($routes as $project => $accountKey) {
        $lines[] = (string)$project . '|' . (string)$accountKey;
    }
    return implode("\n", $lines);
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $defaultAccount = trim((string)($_POST['default_account'] ?? ''));
    $accountsRaw = trim((string)($_POST['accounts_raw'] ?? ''));
    $routesRaw = trim((string)($_POST['routes_raw'] ?? ''));
    $aliasesRaw = trim((string)($_POST['aliases_raw'] ?? ''));

    $accounts = [];
    $parseErrors = [];
    if ($accountsRaw !== '') {
        $accountLines = preg_split('/\r\n|\r|\n/', $accountsRaw);
        foreach ($accountLines as $idx => $line) {
            $line = trim((string)$line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            $parts = explode('|', $line);
            while (count($parts) < 5) {
                $parts[] = '';
            }
            [$key, $baseUrl, $secretWord, $username, $password] = array_map('trim', array_slice($parts, 0, 5));
            if ($key === '' || $baseUrl === '' || $secretWord === '') {
                $parseErrors[] = 'Строка аккаунтов #' . ($idx + 1) . ': заполните key|base_url|secret_word.';
                continue;
            }
            $accounts[$key] = [
                'base_url' => rtrim($baseUrl, '/'),
                'secret_word' => $secretWord,
            ];
            if ($username !== '') {
                $accounts[$key]['username'] = $username;
            }
            if ($password !== '') {
                $accounts[$key]['password'] = $password;
            }
        }
    }

    $projectAccounts = [];
    if ($routesRaw !== '') {
        $routeLines = preg_split('/\r\n|\r|\n/', $routesRaw);
        foreach ($routeLines as $idx => $line) {
            $line = trim((string)$line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            $parts = explode('|', $line, 2);
            $projectName = trim((string)($parts[0] ?? ''));
            $accountKey = trim((string)($parts[1] ?? ''));
            if ($projectName === '' || $accountKey === '') {
                $parseErrors[] = 'Строка маршрутов #' . ($idx + 1) . ': формат "Название проекта|account_key".';
                continue;
            }
            $projectAccounts[$projectName] = $accountKey;
        }
    }

    $projectAliases = [];
    if ($aliasesRaw !== '') {
        $aliasLines = preg_split('/\r\n|\r|\n/', $aliasesRaw);
        foreach ($aliasLines as $idx => $line) {
            $line = trim((string)$line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            $parts = explode('|', $line, 2);
            $aliasName = trim((string)($parts[0] ?? ''));
            $canonicalName = trim((string)($parts[1] ?? ''));
            if ($aliasName === '' || $canonicalName === '') {
                $parseErrors[] = 'Строка алиасов #' . ($idx + 1) . ': формат "Альтернативное название|Каноничное название".';
                continue;
            }
            $projectAliases[$aliasName] = $canonicalName;
        }
    }

    if ($defaultAccount !== '' && !isset($accounts[$defaultAccount])) {
        $parseErrors[] = 'default_account не найден в списке аккаунтов.';
    }
    foreach ($projectAccounts as $projectName => $accountKey) {
        if (!isset($accounts[$accountKey])) {
            $parseErrors[] = 'Для проекта "' . htmlspecialcharsbx($projectName) . '" указан неизвестный account_key "' . htmlspecialcharsbx($accountKey) . '".';
        }
    }

    if (empty($parseErrors)) {
        $saveData = [
            'default_account' => $defaultAccount,
            'accounts' => $accounts,
            'project_accounts' => $projectAccounts,
            'project_aliases' => $projectAliases,
        ];
        COption::SetOptionString('main', 'po_paykeeper_config_json', json_encode($saveData, JSON_UNESCAPED_UNICODE));
        $cfg = $saveData;
        $message = 'Настройки PayKeeper сохранены.';
    } else {
        $error = implode('<br>', $parseErrors);
        $cfg = [
            'default_account' => $defaultAccount,
            'accounts' => $accounts,
            'project_accounts' => $projectAccounts,
            'project_aliases' => $projectAliases,
        ];
    }
}

$accountsRawView = $accountsToText($cfg['accounts']);
$routesRawView = $routesToText($cfg['project_accounts']);
$aliasesRawView = $routesToText($cfg['project_aliases']);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

if ($message !== '') {
    echo '<div class="adm-info-message-wrap"><div class="adm-info-message">' . htmlspecialcharsbx($message) . '</div></div>';
}
if ($error !== '') {
    echo '<div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message">' . $error . '</div></div>';
}
?>

<form method="post" action="">
    <?= bitrix_sessid_post(); ?>
    <table class="adm-detail-content-table edit-table" style="max-width:1300px;">
        <tr class="heading">
            <td colspan="2">Настройки маршрутизации PayKeeper</td>
        </tr>
        <tr>
            <td width="30%">Default account key</td>
            <td><input type="text" name="default_account" value="<?= htmlspecialcharsbx((string)$cfg['default_account']) ?>" style="width:320px"></td>
        </tr>
        <tr>
            <td>Аккаунты</td>
            <td>
                <textarea name="accounts_raw" rows="10" style="width:100%;font-family:monospace;"><?= htmlspecialcharsbx($accountsRawView) ?></textarea>
                <div style="margin-top:6px;color:#666;">Формат строки: <code>account_key|base_url|secret_word|username(optional)|password(optional)</code></div>
            </td>
        </tr>
        <tr>
            <td>Маршрутизация проектов</td>
            <td>
                <textarea name="routes_raw" rows="12" style="width:100%;font-family:monospace;"><?= htmlspecialcharsbx($routesRawView) ?></textarea>
                <div style="margin-top:6px;color:#666;">Формат строки: <code>Название проекта|account_key</code></div>
            </td>
        </tr>
        <tr>
            <td>Алиасы названий (опционально)</td>
            <td>
                <textarea name="aliases_raw" rows="8" style="width:100%;font-family:monospace;"><?= htmlspecialcharsbx($aliasesRawView) ?></textarea>
                <div style="margin-top:6px;color:#666;">Формат строки: <code>Альтернативное название|Каноничное название</code></div>
            </td>
        </tr>
        <tr>
            <td>Текущие названия проектов на сайте</td>
            <td>
                <div style="column-count:2;max-width:900px;">
                    <?php foreach ($projectsFromSite as $projectName): ?>
                        <div><?= htmlspecialcharsbx($projectName) ?></div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
    </table>
    <input type="submit" class="adm-btn-save" value="Сохранить">
</form>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
