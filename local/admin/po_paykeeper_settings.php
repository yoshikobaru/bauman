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

$accountsRows = [];
foreach ($cfg['accounts'] as $key => $acc) {
    if (!is_array($acc)) {
        continue;
    }
    $accountsRows[] = [
        'key' => (string)$key,
        'base_url' => (string)($acc['base_url'] ?? ''),
        'secret_word' => (string)($acc['secret_word'] ?? ''),
        'username' => (string)($acc['username'] ?? ''),
        'password' => (string)($acc['password'] ?? ''),
    ];
}
if (empty($accountsRows)) {
    $accountsRows[] = ['key' => '', 'base_url' => '', 'secret_word' => '', 'username' => '', 'password' => ''];
}

$routesRows = [];
foreach ($cfg['project_accounts'] as $projectName => $accountKey) {
    $routesRows[] = ['project_name' => (string)$projectName, 'account_key' => (string)$accountKey];
}
if (empty($routesRows)) {
    $routesRows[] = ['project_name' => '', 'account_key' => ''];
}

$aliasesRows = [];
foreach ($cfg['project_aliases'] as $aliasName => $canonicalName) {
    $aliasesRows[] = ['alias_name' => (string)$aliasName, 'canonical_name' => (string)$canonicalName];
}
if (empty($aliasesRows)) {
    $aliasesRows[] = ['alias_name' => '', 'canonical_name' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $defaultAccount = trim((string)($_POST['default_account'] ?? ''));
    $accountsInput = isset($_POST['accounts_rows']) && is_array($_POST['accounts_rows']) ? $_POST['accounts_rows'] : [];
    $routesInput = isset($_POST['routes_rows']) && is_array($_POST['routes_rows']) ? $_POST['routes_rows'] : [];
    $aliasesInput = isset($_POST['aliases_rows']) && is_array($_POST['aliases_rows']) ? $_POST['aliases_rows'] : [];

    $parseErrors = [];
    $accounts = [];
    $seenKeys = [];

    foreach ($accountsInput as $idx => $row) {
        if (!is_array($row)) {
            continue;
        }
        $key = trim((string)($row['key'] ?? ''));
        $baseUrl = trim((string)($row['base_url'] ?? ''));
        $secretWord = trim((string)($row['secret_word'] ?? ''));
        $username = trim((string)($row['username'] ?? ''));
        $password = trim((string)($row['password'] ?? ''));

        if ($key === '' && $baseUrl === '' && $secretWord === '' && $username === '' && $password === '') {
            continue;
        }
        if ($key === '' || $baseUrl === '' || $secretWord === '') {
            $parseErrors[] = 'Строка аккаунта #' . ($idx + 1) . ': обязательны key, base_url и secret_word.';
            continue;
        }
        if (isset($seenKeys[$key])) {
            $parseErrors[] = 'Дублирующийся account_key: "' . htmlspecialcharsbx($key) . '".';
            continue;
        }
        $seenKeys[$key] = true;

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

    $projectAccounts = [];
    foreach ($routesInput as $idx => $row) {
        if (!is_array($row)) {
            continue;
        }
        $projectName = trim((string)($row['project_name'] ?? ''));
        $accountKey = trim((string)($row['account_key'] ?? ''));
        if ($projectName === '' && $accountKey === '') {
            continue;
        }
        if ($projectName === '' || $accountKey === '') {
            $parseErrors[] = 'Строка маршрута #' . ($idx + 1) . ': заполните название проекта и account_key.';
            continue;
        }
        $projectAccounts[$projectName] = $accountKey;
    }

    $projectAliases = [];
    foreach ($aliasesInput as $idx => $row) {
        if (!is_array($row)) {
            continue;
        }
        $aliasName = trim((string)($row['alias_name'] ?? ''));
        $canonicalName = trim((string)($row['canonical_name'] ?? ''));
        if ($aliasName === '' && $canonicalName === '') {
            continue;
        }
        if ($aliasName === '' || $canonicalName === '') {
            $parseErrors[] = 'Строка алиаса #' . ($idx + 1) . ': заполните оба поля.';
            continue;
        }
        $projectAliases[$aliasName] = $canonicalName;
    }

    if ($defaultAccount !== '' && !isset($accounts[$defaultAccount])) {
        $parseErrors[] = 'Default account key не найден в таблице аккаунтов.';
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

        $accountsRows = [];
        foreach ($accounts as $key => $acc) {
            $accountsRows[] = [
                'key' => (string)$key,
                'base_url' => (string)($acc['base_url'] ?? ''),
                'secret_word' => (string)($acc['secret_word'] ?? ''),
                'username' => (string)($acc['username'] ?? ''),
                'password' => (string)($acc['password'] ?? ''),
            ];
        }
        if (empty($accountsRows)) {
            $accountsRows[] = ['key' => '', 'base_url' => '', 'secret_word' => '', 'username' => '', 'password' => ''];
        }

        $routesRows = [];
        foreach ($projectAccounts as $projectName => $accountKey) {
            $routesRows[] = ['project_name' => (string)$projectName, 'account_key' => (string)$accountKey];
        }
        if (empty($routesRows)) {
            $routesRows[] = ['project_name' => '', 'account_key' => ''];
        }

        $aliasesRows = [];
        foreach ($projectAliases as $aliasName => $canonicalName) {
            $aliasesRows[] = ['alias_name' => (string)$aliasName, 'canonical_name' => (string)$canonicalName];
        }
        if (empty($aliasesRows)) {
            $aliasesRows[] = ['alias_name' => '', 'canonical_name' => ''];
        }
    } else {
        $error = implode('<br>', $parseErrors);
        $accountsRows = [];
        foreach ($accountsInput as $row) {
            if (!is_array($row)) {
                continue;
            }
            $accountsRows[] = [
                'key' => trim((string)($row['key'] ?? '')),
                'base_url' => trim((string)($row['base_url'] ?? '')),
                'secret_word' => trim((string)($row['secret_word'] ?? '')),
                'username' => trim((string)($row['username'] ?? '')),
                'password' => trim((string)($row['password'] ?? '')),
            ];
        }
        if (empty($accountsRows)) {
            $accountsRows[] = ['key' => '', 'base_url' => '', 'secret_word' => '', 'username' => '', 'password' => ''];
        }

        $routesRows = [];
        foreach ($routesInput as $row) {
            if (!is_array($row)) {
                continue;
            }
            $routesRows[] = [
                'project_name' => trim((string)($row['project_name'] ?? '')),
                'account_key' => trim((string)($row['account_key'] ?? '')),
            ];
        }
        if (empty($routesRows)) {
            $routesRows[] = ['project_name' => '', 'account_key' => ''];
        }

        $aliasesRows = [];
        foreach ($aliasesInput as $row) {
            if (!is_array($row)) {
                continue;
            }
            $aliasesRows[] = [
                'alias_name' => trim((string)($row['alias_name'] ?? '')),
                'canonical_name' => trim((string)($row['canonical_name'] ?? '')),
            ];
        }
        if (empty($aliasesRows)) {
            $aliasesRows[] = ['alias_name' => '', 'canonical_name' => ''];
        }
    }
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

if ($message !== '') {
    echo '<div class="adm-info-message-wrap"><div class="adm-info-message">' . htmlspecialcharsbx($message) . '</div></div>';
}
if ($error !== '') {
    echo '<div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message">' . $error . '</div></div>';
}
?>

<style>
.po-pk-table {
    width: 100%;
    border-collapse: collapse;
}
.po-pk-table th, .po-pk-table td {
    border: 1px solid #dce1e5;
    padding: 8px;
    vertical-align: top;
}
.po-pk-table th {
    background: #f5f7f8;
    text-align: left;
    font-weight: 600;
}
.po-pk-table input, .po-pk-table select {
    width: 100%;
    box-sizing: border-box;
}
.po-pk-row-actions {
    display: flex;
    gap: 6px;
}
.po-pk-muted {
    color: #666;
    margin-top: 6px;
}
</style>

<datalist id="po_project_names">
    <?php foreach ($projectsFromSite as $projectName): ?>
        <option value="<?= htmlspecialcharsbx($projectName) ?>"></option>
    <?php endforeach; ?>
</datalist>
<datalist id="po_account_keys">
    <?php foreach ($accountsRows as $row): ?>
        <?php if ((string)$row['key'] !== ''): ?>
            <option value="<?= htmlspecialcharsbx((string)$row['key']) ?>"></option>
        <?php endif; ?>
    <?php endforeach; ?>
</datalist>

<form method="post" action="" id="po_pk_form">
    <?= bitrix_sessid_post(); ?>
    <table class="adm-detail-content-table edit-table" style="max-width:1600px;">
        <tr class="heading">
            <td colspan="2">Настройки маршрутизации PayKeeper</td>
        </tr>
        <tr>
            <td width="24%">Default account key</td>
            <td>
                <input type="text" name="default_account" value="<?= htmlspecialcharsbx((string)$cfg['default_account']) ?>" list="po_account_keys" style="width:380px">
                <div class="po-pk-muted">Используется как fallback, если проект не найден в маршрутах.</div>
            </td>
        </tr>
        <tr>
            <td>Аккаунты PayKeeper</td>
            <td>
                <table class="po-pk-table" id="accounts_table">
                    <thead>
                    <tr>
                        <th style="width:12%">Account key</th>
                        <th style="width:24%">Base URL</th>
                        <th style="width:22%">Secret word</th>
                        <th style="width:14%">Username</th>
                        <th style="width:14%">Password</th>
                        <th style="width:14%">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($accountsRows as $idx => $row): ?>
                        <tr>
                            <td><input type="text" name="accounts_rows[<?= (int)$idx ?>][key]" value="<?= htmlspecialcharsbx((string)$row['key']) ?>"></td>
                            <td><input type="text" name="accounts_rows[<?= (int)$idx ?>][base_url]" value="<?= htmlspecialcharsbx((string)$row['base_url']) ?>"></td>
                            <td><input type="text" name="accounts_rows[<?= (int)$idx ?>][secret_word]" value="<?= htmlspecialcharsbx((string)$row['secret_word']) ?>"></td>
                            <td><input type="text" name="accounts_rows[<?= (int)$idx ?>][username]" value="<?= htmlspecialcharsbx((string)$row['username']) ?>"></td>
                            <td><input type="text" name="accounts_rows[<?= (int)$idx ?>][password]" value="<?= htmlspecialcharsbx((string)$row['password']) ?>"></td>
                            <td><div class="po-pk-row-actions"><button type="button" class="adm-btn js-row-remove">Удалить</button></div></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:8px;">
                    <button type="button" class="adm-btn" id="add_account_row">Добавить аккаунт</button>
                </div>
            </td>
        </tr>
        <tr>
            <td>Маршрутизация проектов</td>
            <td>
                <table class="po-pk-table" id="routes_table">
                    <thead>
                    <tr>
                        <th style="width:56%">Название проекта</th>
                        <th style="width:30%">Account key</th>
                        <th style="width:14%">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($routesRows as $idx => $row): ?>
                        <tr>
                            <td><input type="text" name="routes_rows[<?= (int)$idx ?>][project_name]" value="<?= htmlspecialcharsbx((string)$row['project_name']) ?>" list="po_project_names"></td>
                            <td><input type="text" name="routes_rows[<?= (int)$idx ?>][account_key]" value="<?= htmlspecialcharsbx((string)$row['account_key']) ?>" list="po_account_keys"></td>
                            <td><div class="po-pk-row-actions"><button type="button" class="adm-btn js-row-remove">Удалить</button></div></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:8px;">
                    <button type="button" class="adm-btn" id="add_route_row">Добавить маршрут</button>
                </div>
            </td>
        </tr>
        <tr>
            <td>Алиасы названий</td>
            <td>
                <table class="po-pk-table" id="aliases_table">
                    <thead>
                    <tr>
                        <th style="width:44%">Альтернативное название</th>
                        <th style="width:42%">Каноничное название</th>
                        <th style="width:14%">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($aliasesRows as $idx => $row): ?>
                        <tr>
                            <td><input type="text" name="aliases_rows[<?= (int)$idx ?>][alias_name]" value="<?= htmlspecialcharsbx((string)$row['alias_name']) ?>" list="po_project_names"></td>
                            <td><input type="text" name="aliases_rows[<?= (int)$idx ?>][canonical_name]" value="<?= htmlspecialcharsbx((string)$row['canonical_name']) ?>" list="po_project_names"></td>
                            <td><div class="po-pk-row-actions"><button type="button" class="adm-btn js-row-remove">Удалить</button></div></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:8px;">
                    <button type="button" class="adm-btn" id="add_alias_row">Добавить алиас</button>
                </div>
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

<script>
(function() {
    function addRow(tableId, html, indexRef) {
        var table = document.getElementById(tableId);
        if (!table) return;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;
        var tr = document.createElement('tr');
        tr.innerHTML = html.replace(/__INDEX__/g, String(indexRef.value++));
        tbody.appendChild(tr);
        refreshAccountDatalist();
    }

    function removeRow(btn) {
        var tr = btn.closest('tr');
        if (!tr) return;
        var tbody = tr.parentNode;
        tr.remove();
        if (tbody && tbody.children.length === 0) {
            var fake = document.createElement('tr');
            fake.innerHTML = '<td colspan="6" style="color:#666;">Список пуст. Нажмите "Добавить".</td>';
            tbody.appendChild(fake);
        }
        refreshAccountDatalist();
    }

    function refreshAccountDatalist() {
        var datalist = document.getElementById('po_account_keys');
        if (!datalist) return;
        datalist.innerHTML = '';
        document.querySelectorAll('#accounts_table tbody input[name$="[key]"]').forEach(function(input) {
            var val = (input.value || '').trim();
            if (!val) return;
            var option = document.createElement('option');
            option.value = val;
            datalist.appendChild(option);
        });
    }

    var accountsIdx = { value: document.querySelectorAll('#accounts_table tbody tr').length };
    var routesIdx = { value: document.querySelectorAll('#routes_table tbody tr').length };
    var aliasesIdx = { value: document.querySelectorAll('#aliases_table tbody tr').length };

    var accountTpl = ''
        + '<td><input type="text" name="accounts_rows[__INDEX__][key]"></td>'
        + '<td><input type="text" name="accounts_rows[__INDEX__][base_url]"></td>'
        + '<td><input type="text" name="accounts_rows[__INDEX__][secret_word]"></td>'
        + '<td><input type="text" name="accounts_rows[__INDEX__][username]"></td>'
        + '<td><input type="text" name="accounts_rows[__INDEX__][password]"></td>'
        + '<td><div class="po-pk-row-actions"><button type="button" class="adm-btn js-row-remove">Удалить</button></div></td>';
    var routeTpl = ''
        + '<td><input type="text" name="routes_rows[__INDEX__][project_name]" list="po_project_names"></td>'
        + '<td><input type="text" name="routes_rows[__INDEX__][account_key]" list="po_account_keys"></td>'
        + '<td><div class="po-pk-row-actions"><button type="button" class="adm-btn js-row-remove">Удалить</button></div></td>';
    var aliasTpl = ''
        + '<td><input type="text" name="aliases_rows[__INDEX__][alias_name]" list="po_project_names"></td>'
        + '<td><input type="text" name="aliases_rows[__INDEX__][canonical_name]" list="po_project_names"></td>'
        + '<td><div class="po-pk-row-actions"><button type="button" class="adm-btn js-row-remove">Удалить</button></div></td>';

    var addAccount = document.getElementById('add_account_row');
    if (addAccount) addAccount.addEventListener('click', function() { addRow('accounts_table', accountTpl, accountsIdx); });
    var addRoute = document.getElementById('add_route_row');
    if (addRoute) addRoute.addEventListener('click', function() { addRow('routes_table', routeTpl, routesIdx); });
    var addAlias = document.getElementById('add_alias_row');
    if (addAlias) addAlias.addEventListener('click', function() { addRow('aliases_table', aliasTpl, aliasesIdx); });

    document.addEventListener('click', function(e) {
        var target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.classList.contains('js-row-remove')) {
            e.preventDefault();
            removeRow(target);
        }
    });

    document.addEventListener('input', function(e) {
        var target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.matches('#accounts_table tbody input[name$="[key]"]')) {
            refreshAccountDatalist();
        }
    });

    refreshAccountDatalist();
})();
</script>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
