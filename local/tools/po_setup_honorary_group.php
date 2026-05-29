<?php
/**
 * Одноразовое создание группы «Член общества — Почётное» в Битриксе.
 *
 * Запуск (под администратором): /local/tools/po_setup_honorary_group.php
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

global $USER;

if (!is_object($USER) || !$USER->IsAdmin()) {
    http_response_code(403);
    die('Доступ только для администратора сайта.');
}

$groupStringId = 'PO_MEMBER_HONORARY';
$groupName     = 'Член общества — Почётное';
$groupDesc     = 'Почётное членство Политехнического общества (назначается вручную в админке).';

function po_honorary_find_group_id(string $stringId): int
{
    $rs = CGroup::GetList('id', 'asc', ['STRING_ID' => $stringId]);
    if ($row = $rs->Fetch()) {
        return (int)$row['ID'];
    }
    $rs = CGroup::GetList('id', 'asc', ['NAME' => 'Член общества — Почётное']);
    if ($row = $rs->Fetch()) {
        return (int)$row['ID'];
    }
    return 0;
}

$groupId = po_honorary_find_group_id($groupStringId);
$created = false;
$error   = '';

if ($groupId <= 0) {
    $oGroup = new CGroup();
    $newId  = (int)$oGroup->Add([
        'ACTIVE'       => 'Y',
        'C_SORT'       => 510,
        'NAME'         => $groupName,
        'DESCRIPTION'  => $groupDesc,
        'STRING_ID'    => $groupStringId,
    ]);
    if ($newId > 0) {
        $groupId = $newId;
        $created = true;
    } else {
        $error = $oGroup->LAST_ERROR ?: 'Не удалось создать группу.';
    }
}

$initUpdated = false;
$initPath    = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php';
$initMessage = '';

if ($groupId > 0 && is_readable($initPath) && is_writable($initPath)) {
    $initContent = (string)file_get_contents($initPath);
    if (strpos($initContent, 'PO_MEMBER_HONORARY_ID') === false) {
        $needle = "define('PO_PARTNER_ID',        8); // Партнёр (юр. лицо)\r\n";
        $insert = "define('PO_PARTNER_ID',        8); // Партнёр (юр. лицо)\r\n"
            . "define('PO_MEMBER_HONORARY_ID', {$groupId}); // Член общества — Почётное\r\n";
        if (strpos($initContent, $needle) !== false) {
            $initContent = str_replace($needle, $insert, $initContent);
        } else {
            $needle = "define('PO_PARTNER_ID',        8); // Партнёр (юр. лицо)\n";
            $insert = "define('PO_PARTNER_ID',        8); // Партнёр (юр. лицо)\n"
                . "define('PO_MEMBER_HONORARY_ID', {$groupId}); // Член общества — Почётное\n";
            $initContent = str_replace($needle, $insert, $initContent);
        }
        if (strpos($initContent, 'PO_MEMBER_HONORARY_ID') !== false) {
            file_put_contents($initPath, $initContent);
            $initUpdated = true;
        } else {
            $initMessage = 'Группа создана, но в init.php не найдена строка для вставки константы — добавьте вручную.';
        }
    } else {
        $initMessage = 'Константа PO_MEMBER_HONORARY_ID уже есть в init.php.';
    }
} elseif ($groupId > 0) {
    $initMessage = 'Проверьте права на запись local/php_interface/init.php для автообновления константы.';
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Группа «Почётное»</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; line-height: 1.5; }
        .ok { color: #1e7e34; }
        .err { color: #c0392b; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 4px; }
        pre { background: #f4f4f4; padding: 12px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Группа почётного членства</h1>
    <?php if ($error !== ''): ?>
        <p class="err"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($groupId > 0): ?>
        <p class="ok">
            <?= $created ? 'Группа создана.' : 'Группа уже существовала.' ?>
            ID: <strong><?= (int)$groupId ?></strong>,
            STRING_ID: <code><?= htmlspecialchars($groupStringId) ?></code>
        </p>
        <p>Назначение: <strong>Настройки → Пользователи → Группы пользователей</strong> или в карточке пользователя.</p>
        <?php if ($initUpdated): ?>
            <p class="ok">В <code>local/php_interface/init.php</code> добавлено:<br>
                <code>define('PO_MEMBER_HONORARY_ID', <?= (int)$groupId ?>);</code>
            </p>
        <?php elseif ($initMessage !== ''): ?>
            <p><?= htmlspecialchars($initMessage) ?></p>
            <pre>define('PO_MEMBER_HONORARY_ID', <?= (int)$groupId ?>); // Член общества — Почётное</pre>
        <?php endif; ?>
        <p>Отдельных прав модулей группа не требует — используйте как метку членства.</p>
    <?php endif; ?>
    <p><a href="/bitrix/admin/group_admin.php?lang=ru">Открыть список групп в админке</a></p>
</body>
</html>
