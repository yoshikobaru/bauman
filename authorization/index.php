<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Войти");

if ($USER->IsAuthorized()) {
    LocalRedirect('/profile/');
}

$errors      = [];
$messages    = [];
$activeSection = 'login'; // login | forgot | reset

// — Сброс пароля по ссылке из письма —
if (isset($_GET['checkword'], $_GET['USER_ID'])) {
    $activeSection = 'reset';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset') {
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        if ($newPass !== $confirmPass) {
            $errors[] = 'Пароли не совпадают';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'Пароль должен содержать не менее 6 символов';
        } else {
            $dbUser = CUser::GetByID((int)$_GET['USER_ID']);
            $arUser = $dbUser->Fetch();
            if ($arUser) {
                $oUser = new CUser();
                $res = $oUser->ChangePassword(
                    $arUser['LOGIN'],
                    htmlspecialcharsEx($_GET['checkword']),
                    $newPass,
                    $confirmPass
                );
                if ($res === true) {
                    $messages[]    = 'Пароль успешно изменён. Войдите с новым паролем.';
                    $activeSection = 'login';
                } else {
                    $errors[] = $res->GetMessage();
                }
            } else {
                $errors[] = 'Пользователь не найден';
            }
        }
    }
}

// — Вход —
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']) ? 'Y' : 'N';

    if (!$email || !$password) {
        $errors[] = 'Заполните email и пароль';
    } else {
        $result = $USER->Login($email, $password, $remember);
        if ($result === true) {
            $backUrl = !empty($_REQUEST['back_url']) ? (string)$_REQUEST['back_url'] : '/profile/';
            LocalRedirect($backUrl);
        } else {
            $errors[] = 'Неверный email или пароль';
        }
    }
}

// — Экстренное восстановление (нет доступа к email) —
$emergencyDone = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'emergency_recovery') {
    $activeSection = 'emergency';
    $emName   = trim($_POST['em_name']   ?? '');
    $emEmail  = trim($_POST['em_email']  ?? '');
    $emDesc   = trim($_POST['em_desc']   ?? '');
    $emAgreePd = ($_POST['em_agree_pd'] ?? '') === 'yes';
    if (!$emName || !$emEmail || !$emDesc) {
        $errors[] = 'Заполните все обязательные поля.';
    } elseif (!$emAgreePd) {
        $errors[] = 'Необходимо согласие с политикой ПДн.';
    } else {
        $saved = false;
        $hlOk = \Bitrix\Main\Loader::includeModule('highloadblock');
        if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
            $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
            if ($hlEntity) {
                $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                $res = $hlClass::add([
                    'UF_USER_ID'     => 0,
                    'UF_TYPE'        => 'access_recovery',
                    'UF_STATUS'      => 'new',
                    'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                    'UF_DATA'        => json_encode([
                        'name'        => $emName,
                        'old_email'   => $emEmail,
                        'description' => $emDesc,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
                $saved = $res->isSuccess();
            }
        } else {
            $saved = true;
        }
        if ($saved) {
            po_sendAdminEmail('access_recovery', [
                'name'        => $emName,
                'old_email'   => $emEmail,
                'description' => $emDesc,
            ]);
            $emergencyDone = true;
        } else {
            $errors[] = 'Ошибка сохранения. Попробуйте позже.';
        }
    }
}

// — Восстановление пароля —
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'forgot') {
    $activeSection = 'forgot';
    $forgotEmail = trim($_POST['forgot_email'] ?? '');
    if (!$forgotEmail) {
        $errors[] = 'Введите email';
    } else {
        $dbUser = CUser::GetList('id', 'asc', ['=EMAIL' => $forgotEmail, 'ACTIVE' => 'Y']);
        $arUser = $dbUser->Fetch();
        if (!$arUser) {
            $errors[] = 'Пользователь с таким email не найден';
        } else {
            $res = CUser::SendPassword($arUser['LOGIN'], $forgotEmail, '/authorization/');
            if ($res === true || (is_array($res) && $res['TYPE'] === 'OK')) {
                $messages[] = 'Письмо с инструкциями отправлено на ' . htmlspecialchars($forgotEmail);
            } else {
                $errors[] = is_array($res) ? $res['MESSAGE'] : 'Ошибка отправки письма. Попробуйте позже.';
            }
        }
    }
}
?>

<main>
    <section class="authorization">
        <div class="container">

            <?php if (!empty($errors)): ?>
                <div class="authorization__alert authorization__alert--error">
                    <?php foreach ($errors as $msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($messages)): ?>
                <div class="authorization__alert authorization__alert--success">
                    <?php foreach ($messages as $msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Секция: Вход -->
            <div class="authorization__wrapper" id="section-login"<?= $activeSection !== 'login' ? ' style="display:none"' : '' ?>>
                <h2 class="authorization-title main-title">Войти</h2>
                <form method="POST" action="/authorization/">
                    <input type="hidden" name="action" value="login">
                    <div class="authorization__row">
                        <input type="email" name="email" placeholder="e-mail"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        <input type="password" name="password" placeholder="Пароль" required>
                    </div>
                    <p class="authorization__fogot">
                        Не помню пароль,
                        <a href="#" class="authorization__link authorization__link--fogot" data-show="forgot">восстановить</a>
                    </p>
                    <button type="submit" class="btn authorization__btn">Войти</button>
                </form>
            </div>

            <!-- Секция: Восстановление пароля -->
            <div class="authorization__wrapper" id="section-forgot"<?= $activeSection !== 'forgot' ? ' style="display:none"' : '' ?>>
                <h2 class="authorization-title main-title">Восстановление пароля</h2>
                <form method="POST" action="/authorization/">
                    <input type="hidden" name="action" value="forgot">
                    <div class="account__chapter">
                        <h3 class="account__subtitle">e-mail</h3>
                    </div>
                    <div class="authorization__row">
                        <input type="email" name="forgot_email" placeholder="e-mail"
                               value="<?= htmlspecialchars($_POST['forgot_email'] ?? '') ?>">
                    </div>
                    <a href="#" class="authorization__link" data-show="login">← Вернуться к входу</a>
                    <button type="submit" class="btn authorization__btn">Отправить ссылку</button>
                </form>
                <p style="margin-top:20px;font-size:13px;color:#888">
                    Нет доступа к email?
                    <a href="#" class="authorization__link" data-show="emergency" style="color:#e31e24">Обратиться к администратору →</a>
                </p>
            </div>

            <!-- Секция: Экстренное восстановление -->
            <div class="authorization__wrapper" id="section-emergency"<?= $activeSection !== 'emergency' ? ' style="display:none"' : '' ?>>
                <h2 class="authorization-title main-title">Обращение к администратору</h2>
                <?php if ($emergencyDone): ?>
                <div class="authorization__alert authorization__alert--success">
                    <p>Ваша заявка принята. Администратор свяжется с вами после проверки личности.</p>
                </div>
                <a href="/authorization/" class="btn" style="margin-top:16px">Вернуться ко входу</a>
                <?php else: ?>
                <p style="color:#666;margin-bottom:20px;font-size:14px">
                    Заполните форму. После ручной проверки личности администратор сбросит привязки и выдаст временный пароль.
                </p>
                <form method="POST" action="/authorization/">
                    <input type="hidden" name="action" value="emergency_recovery">
                    <div class="authorization__row">
                        <input type="text" name="em_name" placeholder="ФИО *" required
                               value="<?= htmlspecialchars($_POST['em_name'] ?? '') ?>">
                        <input type="email" name="em_email" placeholder="Старый email от аккаунта *" required
                               value="<?= htmlspecialchars($_POST['em_email'] ?? '') ?>">
                    </div>
                    <div style="margin-top:12px">
                        <textarea name="em_desc" placeholder="Опишите ситуацию: когда утратили доступ, через какие каналы можно связаться *"
                                  required style="width:100%;min-height:100px;padding:12px;border:1px solid #ccc;border-radius:4px;font-size:14px"><?= htmlspecialchars($_POST['em_desc'] ?? '') ?></textarea>
                    </div>
                    <div class="join__politic" style="margin-top:16px">
                        <div class="join__politic-question">
                            <p class="join__politic-link">Согласен с <a href="#">политикой обработки ПДн</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="em_agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="em_agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="authorization__link" data-show="forgot" style="display:block;margin-top:12px">← Назад</a>
                    <button type="submit" class="btn authorization__btn" style="margin-top:16px">Отправить заявку</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Секция: Новый пароль (по ссылке из письма) -->
            <div class="authorization__wrapper" id="section-reset"<?= $activeSection !== 'reset' ? ' style="display:none"' : '' ?>>
                <h2 class="authorization-title main-title">Новый пароль</h2>
                <form method="POST"
                      action="/authorization/?checkword=<?= htmlspecialchars($_GET['checkword'] ?? '') ?>&USER_ID=<?= (int)($_GET['USER_ID'] ?? 0) ?>">
                    <input type="hidden" name="action" value="reset">
                    <div class="authorization__row">
                        <input type="password" name="new_password" placeholder="Придумайте пароль" required>
                        <input type="password" name="confirm_password" placeholder="Повторите пароль" required>
                    </div>
                    <button type="submit" class="btn authorization__btn">Сохранить</button>
                </form>
            </div>

        </div>
    </section>
</main>

<script>
document.querySelectorAll('[data-show]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        var target = this.getAttribute('data-show');
        document.querySelectorAll('.authorization__wrapper').forEach(function(w) { w.style.display = 'none'; });
        var section = document.getElementById('section-' + target);
        if (section) section.style.display = '';
    });
});
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
