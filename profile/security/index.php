<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Безопасность");

if (!$USER->IsAuthorized()) {
    LocalRedirect('/authorization/?back_url=/profile/security/');
}

$userId = $USER->GetID();
$saveOk = false;
$saveError = '';
$securityFlash = function_exists('po_flash_get') ? po_flash_get('profile_security') : null;
if (is_array($securityFlash)) {
    $saveOk = !empty($securityFlash['done']);
    $saveError = (string)($securityFlash['error'] ?? '');
}

// — Смена пароля (без старого пароля) —
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['security_action'])) {
    if (!check_bitrix_sessid()) {
        $saveError = 'Сессия устарела. Обновите страницу и попробуйте снова.';
    } else {
        $newPwd = $_POST['new_password'] ?? '';
        $confirmPwd = $_POST['confirm_password'] ?? '';

        if (!$newPwd || !$confirmPwd) {
            $saveError = 'Заполните оба поля пароля.';
        } elseif (strlen($newPwd) < 6) {
            $saveError = 'Пароль должен содержать не менее 6 символов.';
        } elseif ($newPwd !== $confirmPwd) {
            $saveError = 'Пароли не совпадают.';
        } else {
            $oUser = new CUser();
            $result = $oUser->Update($userId, [
                'PASSWORD'         => $newPwd,
                'CONFIRM_PASSWORD' => $confirmPwd,
            ]);
            if ($result) {
                $USER->Authorize((int)$userId);
                $saveOk = true;
            } else {
                $saveError = $oUser->LAST_ERROR ?: 'Ошибка смены пароля.';
            }
        }
    }
    if (function_exists('po_flash_set')) {
        po_flash_set('profile_security', ['done' => $saveOk, 'error' => $saveError]);
    }
    LocalRedirect('/profile/security/?status=' . ($saveOk ? 'success' : 'error'));
    exit;
}

// — Данные пользователя —
$dbUser = CUser::GetByID($userId);
$arUser = $dbUser->Fetch() ?: [];
$userPhone = trim((string)($arUser['PERSONAL_PHONE'] ?? ''));
$userEmail = trim((string)($arUser['EMAIL'] ?? ''));

// — Двухфакторная аутентификация: что подключено —
$phoneEnabled = !empty($userPhone);
$emailEnabled = !empty($userEmail);

// — Социальные входы (заглушка — позже можно привязать реальные модули) —
$socialProviders = [
    [
        'id'    => 'yandex',
        'label' => 'Войти через Яндекс',
        'icon'  => SITE_TEMPLATE_PATH . '/assets/img/my_profile/yandex-icon.png',
        'linked'=> false,
    ],
    [
        'id'    => 'gosuslugi',
        'label' => 'Войти через Госуслуги',
        'icon'  => SITE_TEMPLATE_PATH . '/assets/img/my_profile/gos-icon.png',
        'linked'=> false,
    ],
    [
        'id'    => 'vk',
        'label' => 'Войти через Вконтакте',
        'icon'  => SITE_TEMPLATE_PATH . '/assets/img/my_profile/vk-icon.png',
        'linked'=> false,
    ],
];

// — SESSID для AJAX —
$sessid = bitrix_sessid();
?>

<main>
    <section class="account">
        <div class="container">
            <div class="account__wrapper">
                <div class="account__sidebar">
                    <div class="account__menu">
                        <?php $secTab = $_GET['tab'] ?? 'security'; ?>
                        <a href="/profile/" class="account__menu-item <?= $secTab === 'profile' ? 'account__menu-item--active' : '' ?>">Мой профиль</a>
                        <a href="/profile/security/" class="account__menu-item account__menu-item--active">Безопасность</a>
                        <a href="/profile/?tab=membership" class="account__menu-item <?= $secTab === 'membership' ? 'account__menu-item--active' : '' ?>">Моё членство</a>
                        <a href="/profile/?tab=activities" class="account__menu-item <?= $secTab === 'activities' ? 'account__menu-item--active' : '' ?>">Мои активности</a>
                        <a href="/profile/?tab=applications" class="account__menu-item <?= $secTab === 'applications' ? 'account__menu-item--active' : '' ?>">Мои заявки</a>
                        <?php if (defined('PO_PARTNER_ID') && in_array(PO_PARTNER_ID, $USER->GetUserGroupArray())): ?>
                        <a href="/profile/?tab=company" class="account__menu-item <?= $secTab === 'company' ? 'account__menu-item--active' : '' ?>">Моя компания</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="account__main">
                    <div class="account__block account__block--security">

                        <?php if ($saveOk): ?>
                        <div class="authorization__alert authorization__alert--success" id="security-success-msg" style="margin-bottom:16px;display:none">
                            <p>Пароль успешно изменён.</p>
                        </div>
                        <?php endif; ?>
                        <?php if ($saveError): ?>
                        <div class="authorization__alert authorization__alert--error" id="security-error-msg" style="margin-bottom:16px;display:none">
                            <p><?= htmlspecialchars($saveError) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Заголовок -->
                        <h2 class="security-title">Безопасность</h2>

                        <!-- ================================
                             1. СМЕНА ПАРОЛЯ
                             ================================ -->
                        <div class="security-section">
                            <h3 class="security-section__subtitle">Смена пароля</h3>
                            <form method="POST" action="/profile/security/" id="security-password-form" class="security-form">
                                <input type="hidden" name="security_action" value="1">
                                <?= bitrix_sessid_post() ?>
                                <div class="security-password-fields">
                                    <div class="security-field">
                                        <input type="password"
                                               name="new_password"
                                               id="security-new-password"
                                               placeholder="Придумайте пароль"
                                               autocomplete="new-password"
                                               required>
                                        <button type="button" class="security-field__eye" data-target="security-new-password" aria-label="Показать пароль">
                                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                                <line x1="1" y1="1" x2="23" y2="23"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="security-field">
                                        <input type="password"
                                               name="confirm_password"
                                               id="security-confirm-password"
                                               placeholder="Повторите пароль"
                                               autocomplete="new-password"
                                               required>
                                        <button type="button" class="security-field__eye" data-target="security-confirm-password" aria-label="Показать пароль">
                                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                                <line x1="1" y1="1" x2="23" y2="23"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <button type="submit" class="security-save-btn" id="security-save-btn">Сохранить</button>
                                </div>
                                <div class="security-password-error" id="security-password-error" style="display:none"></div>
                            </form>
                        </div>

                        <!-- ================================
                             2. ДВУХФАКТОРНАЯ АУТЕНТИФИКАЦИЯ
                             ================================ -->
                        <div class="security-section">
                            <h3 class="security-section__subtitle">Двухфакторная аутентификация</h3>
                            <div class="security-2fa-list">
                                <div class="security-2fa-item">
                                    <span class="security-2fa-value"><?= htmlspecialchars($userPhone ?: 'Не указан') ?></span>
                                    <span class="security-2fa-status <?= $phoneEnabled ? 'security-2fa-status--on' : '' ?>">
                                        <?= $phoneEnabled ? 'Подключено' : 'Не подключено' ?>
                                    </span>
                                </div>
                                <div class="security-2fa-item">
                                    <span class="security-2fa-value"><?= htmlspecialchars($userEmail) ?></span>
                                    <span class="security-2fa-status <?= $emailEnabled ? 'security-2fa-status--on' : '' ?>">
                                        <?= $emailEnabled ? 'Подключено' : 'Не подключено' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- ================================
                             3. ПРИВЯЗКА АККАУНТА ДЛЯ БЫСТРОГО ВХОДА
                             ================================ -->
                        <div class="security-section">
                            <h3 class="security-section__subtitle">Привязка аккаунта для быстрого входа</h3>
                            <div class="security-social-list">
                                <?php foreach ($socialProviders as $provider): ?>
                                <button class="security-social-btn" data-provider="<?= htmlspecialchars($provider['id']) ?>" disabled>
                                    <img src="<?= htmlspecialchars($provider['icon']) ?>" alt="" aria-hidden="true">
                                    <?= htmlspecialchars($provider['label']) ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- ================================
                             4. АКТИВНЫЕ СЕССИИ
                             ================================ -->
                        <div class="security-section">
                            <h3 class="security-section__subtitle">Активные сессии</h3>
                            <div class="security-sessions-list" id="security-sessions-list">
                                <div class="security-sessions-loading">
                                    <span>Загрузка...</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
(function() {
    // — Показ/скрытие пароля —
    document.querySelectorAll('.security-field__eye').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (!input) return;
            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            var openIcon = this.querySelector('.eye-open');
            var closedIcon = this.querySelector('.eye-closed');
            if (openIcon) openIcon.style.display = isPassword ? 'none' : '';
            if (closedIcon) closedIcon.style.display = isPassword ? '' : 'none';
        });
    });

    // — Валидация пароля в реальном времени —
    var newPwdInput = document.getElementById('security-new-password');
    var confirmPwdInput = document.getElementById('security-confirm-password');
    var saveBtn = document.getElementById('security-save-btn');
    var errorBlock = document.getElementById('security-password-error');

    function validatePassword() {
        var np = newPwdInput.value;
        var cp = confirmPwdInput.value;
        var valid = true;
        var msg = '';

        if (np.length > 0 && np.length < 6) {
            msg = 'Пароль должен содержать не менее 6 символов';
            valid = false;
        }
        if (cp.length > 0 && np !== cp) {
            msg = 'Пароли не совпадают';
            valid = false;
        }
        if (msg && errorBlock) {
            errorBlock.textContent = msg;
            errorBlock.style.display = '';
        } else if (errorBlock) {
            errorBlock.style.display = 'none';
        }

        if (saveBtn) {
            saveBtn.style.opacity = (np.length > 0 && cp.length > 0 && valid) ? '1' : '0.3';
            saveBtn.style.pointerEvents = (np.length > 0 && cp.length > 0 && valid) ? '' : 'none';
        }
    }

    if (newPwdInput) newPwdInput.addEventListener('input', validatePassword);
    if (confirmPwdInput) confirmPwdInput.addEventListener('input', validatePassword);

    // — Загрузка активных сессий —
    function renderSessions(sessions) {
        var container = document.getElementById('security-sessions-list');
        if (!container) return;
        container.innerHTML = '';

        if (!sessions || sessions.length === 0) {
            container.innerHTML = '<p style="color:#888;font-size:14px">Нет активных сессий.</p>';
            return;
        }

        sessions.forEach(function(sess) {
            var iconSvg = '';
            if (sess.icon_type === 'smartphone') {
                iconSvg = '<svg class="session-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>';
            } else if (sess.icon_type === 'tablet') {
                iconSvg = '<svg class="session-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>';
            } else {
                iconSvg = '<svg class="session-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';
            }

            var cityLabel = sess.city ? sess.city : '';
            var timeLabel = sess.last_activity ? sess.last_activity : '';
            var currentLabel = sess.is_current ? '<span class="session-current-label">Это устройство</span>' : '';

            var item = document.createElement('div');
            item.className = 'session-item';
            item.dataset.hash = sess.hash;
            item.dataset.isCurrent = sess.is_current ? '1' : '0';

            item.innerHTML =
                '<div class="session-info">' +
                    iconSvg +
                    '<div class="session-details">' +
                        '<span class="session-device-name">' + escHtml(sess.device_name) + '</span>' +
                        '<div class="session-meta">' +
                            (cityLabel ? '<span class="session-city">' + escHtml(cityLabel) + '</span>' : '') +
                            timeLabel +
                            currentLabel +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="session-actions">' +
                    '<button type="button" class="session-menu-btn" aria-label="Действия">' +
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>' +
                    '</button>' +
                    '<div class="session-dropdown" style="display:none">' +
                        (sess.is_current
                            ? '<span class="session-dropdown-info">Текущая сессия</span>'
                            : '<button type="button" class="session-terminate-btn">Завершить сессию</button>'
                        ) +
                    '</div>' +
                '</div>';

            container.appendChild(item);
        });

        // — Раскрытие меню по клику на троеточие —
        container.querySelectorAll('.session-menu-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                // закрыть все остальные
                container.querySelectorAll('.session-dropdown').forEach(function(d) {
                    if (d !== btn.nextElementSibling) d.style.display = 'none';
                });
                var dropdown = btn.nextElementSibling;
                dropdown.style.display = dropdown.style.display === 'none' ? '' : 'none';
            });
        });

        // — Завершение сессии —
        container.querySelectorAll('.session-terminate-btn').forEach(function(terminateBtn) {
            terminateBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                var item = terminateBtn.closest('.session-item');
                var hash = item ? item.dataset.hash : '';
                if (!hash || !confirm('Завершить эту сессию?')) return;

                terminateBtn.textContent = 'Завершение...';
                terminateBtn.disabled = true;

                var fd = new FormData();
                fd.append('sessid', '<?= $sessid ?>');
                fd.append('session_hash', hash);

                fetch('/local/ajax/sessions.php', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            item.style.opacity = '0.4';
                            setTimeout(function() {
                                item.remove();
                                // если список пуст — показать заглушку
                                var remaining = document.querySelectorAll('.session-item');
                                if (remaining.length === 0) {
                                    var c = document.getElementById('security-sessions-list');
                                    if (c) c.innerHTML = '<p style="color:#888;font-size:14px">Нет активных сессий.</p>';
                                }
                            }, 300);
                        } else {
                            alert(data.message || 'Ошибка завершения сессии');
                            terminateBtn.textContent = 'Завершить сессию';
                            terminateBtn.disabled = false;
                        }
                    })
                    .catch(function() {
                        alert('Ошибка сети');
                        terminateBtn.textContent = 'Завершить сессию';
                        terminateBtn.disabled = false;
                    });
            });
        });

        // Закрыть dropdown при клике вне
        document.addEventListener('click', function() {
            container.querySelectorAll('.session-dropdown').forEach(function(d) {
                d.style.display = 'none';
            });
        });
    }

    function loadSessions() {
        var container = document.getElementById('security-sessions-list');
        if (!container) return;

        fetch('/local/ajax/sessions.php?sessid=<?= $sessid ?>')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    renderSessions(data.sessions);
                } else {
                    container.innerHTML = '<p style="color:#888;font-size:14px">Не удалось загрузить сессии.</p>';
                }
            })
            .catch(function() {
                container.innerHTML = '<p style="color:#888;font-size:14px">Ошибка загрузки сессий.</p>';
            });
    }

    function escHtml(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // Загружаем сессии при загрузке страницы
    loadSessions();

    // — Показ flash-сообщений после редиректа —
    var urlParams = new URLSearchParams(window.location.search);
    var status = urlParams.get('status');
    if (status === 'success') {
        var msg = document.getElementById('security-success-msg');
        if (msg) { msg.style.display = ''; msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
    } else if (status === 'error') {
        var err = document.getElementById('security-error-msg');
        if (err) { err.style.display = ''; err.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
    }
    // Очищаем URL от параметров
    if (window.history.replaceState && (status === 'success' || status === 'error')) {
        window.history.replaceState({}, '', window.location.pathname);
    }
})();
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
