<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Безопасность");

if (!$USER->IsAuthorized()) {
    LocalRedirect('/authorization/?back_url=/profile/security/');
}

$userId   = $USER->GetID();
$saveOk   = false;
$saveError = '';

// — Смена пароля —
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['security_action'])) {
    $currentPwd  = $_POST['current_password']  ?? '';
    $newPwd      = $_POST['new_password']      ?? '';
    $confirmPwd  = $_POST['confirm_password']  ?? '';

    if (!$currentPwd || !$newPwd || !$confirmPwd) {
        $saveError = 'Заполните все поля.';
    } elseif (strlen($newPwd) < 6) {
        $saveError = 'Новый пароль должен содержать не менее 6 символов.';
    } elseif ($newPwd !== $confirmPwd) {
        $saveError = 'Новый пароль и подтверждение не совпадают.';
    } else {
        // Проверяем текущий пароль через попытку входа
        $dbUser = CUser::GetByID($userId);
        $arUserData = $dbUser->Fetch();
        $login = $arUserData['LOGIN'] ?? $arUserData['EMAIL'];

        $checkResult = CUser::Login($login, $currentPwd, 'N');
        if ($checkResult !== true) {
            $saveError = 'Текущий пароль введён неверно.';
        } else {
            $oUser  = new CUser();
            $result = $oUser->Update($userId, [
                'PASSWORD'         => $newPwd,
                'CONFIRM_PASSWORD' => $confirmPwd,
            ]);
            if ($result) {
                $saveOk = true;
            } else {
                $saveError = $oUser->LAST_ERROR ?: 'Ошибка смены пароля.';
            }
        }
    }
}
?>

<main>
    <section class="account">
        <div class="container">
            <div class="account__wrapper">
                <div class="account__sidebar">
                    <div class="account__menu">
                        <a href="/profile/" class="account__menu-item">Мой профиль</a>
                        <a href="/profile/security/" class="account__menu-item account__menu-item--active">Безопасность</a>
                        <a href="/profile/?tab=applications" class="account__menu-item">Мои заявки</a>
                    </div>
                </div>

                <div class="account__main">
                    <div class="account__block">
                        <h2 class="account__title">Безопасность</h2>

                        <?php if ($saveOk): ?>
                        <div class="authorization__alert authorization__alert--success" style="margin-bottom:16px">
                            <p>Пароль успешно изменён.</p>
                        </div>
                        <?php endif; ?>
                        <?php if ($saveError): ?>
                        <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                            <p><?= htmlspecialchars($saveError) ?></p>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="/profile/security/">
                            <input type="hidden" name="security_action" value="1">

                            <div class="account__personal">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Смена пароля</h3>
                                </div>
                                <div class="account__personal-list account__grid" style="max-width:500px">
                                    <input type="password" name="current_password" placeholder="Текущий пароль" required>
                                    <input type="password" name="new_password"     placeholder="Новый пароль (мин. 6 символов)" required>
                                    <input type="password" name="confirm_password" placeholder="Подтвердите новый пароль" required>
                                </div>
                            </div>

                            <button type="submit" class="btn authorization__btn" style="margin-top:24px">Сохранить пароль</button>
                        </form>

                        <!-- Email (read-only) -->
                        <div class="account__personal" style="margin-top:40px">
                            <div class="account__chapter">
                                <h3 class="account__subtitle">Электронная почта</h3>
                            </div>
                            <p style="color:#666;margin-top:8px">
                                <?= htmlspecialchars($USER->GetParam('EMAIL')) ?>
                                <span style="font-size:12px;color:#999;margin-left:8px">(для изменения обратитесь к администратору)</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
