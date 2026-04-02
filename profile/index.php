<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мой профиль");

if (!$USER->IsAuthorized()) {
    LocalRedirect('/authorization/?back_url=/profile/');
}

$userId = $USER->GetID();
$saveError = '';
$saveOk    = !empty($_GET['saved']);
$joinedOk  = !empty($_GET['joined']);

// — Загрузка данных пользователя —
$dbUser = CUser::GetByID($userId);
$arUser = $dbUser->Fetch() ?: [];

// — Обработчик сохранения профиля —
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_action'])) {
    $firstName   = trim($_POST['first_name']   ?? '');
    $lastName    = trim($_POST['last_name']    ?? '');
    $secondName  = trim($_POST['second_name']  ?? '');
    $telegram    = trim($_POST['telegram']     ?? '');
    $isGraduate  = ($_POST['is_graduate'] ?? '') === 'yes';
    $gradYear    = (int)($_POST['grad_year']   ?? 0);
    $gradDept    = trim($_POST['grad_dept']    ?? '');
    $diplomaSeries = trim($_POST['diploma_series'] ?? '');
    $diplomaNumber = trim($_POST['diploma_number'] ?? '');
    $diplomaDate   = trim($_POST['diploma_date']   ?? '');

    $oUser  = new CUser();
    $result = $oUser->Update($userId, [
        'NAME'        => $firstName,
        'LAST_NAME'   => $lastName,
        'SECOND_NAME' => $secondName,
        'UF_TELEGRAM'       => $telegram,
        'UF_GRADUATE_YEAR'  => $isGraduate ? ($gradYear ?: '') : '',
        'UF_GRADUATE_DEPT'  => $isGraduate ? $gradDept : '',
        'UF_DIPLOMA_SERIES' => $diplomaSeries,
        'UF_DIPLOMA_NUMBER' => $diplomaNumber,
        'UF_DIPLOMA_DATE'   => $diplomaDate,
    ]);

    if ($result) {
        LocalRedirect('/profile/?saved=1');
    } else {
        $saveError = $oUser->LAST_ERROR ?: 'Ошибка сохранения';
        // Обновляем arUser свежими данными из POST
        $arUser['NAME']        = $firstName;
        $arUser['LAST_NAME']   = $lastName;
        $arUser['SECOND_NAME'] = $secondName;
    }
}

// Вспомогательные переменные
$membershipType   = $arUser['UF_MEMBERSHIP_TYPE']   ?? '';
$membershipStatus = $arUser['UF_MEMBERSHIP_STATUS'] ?? '';
$membershipExpires = $arUser['UF_MEMBERSHIP_EXPIRES'] ?? '';
$isGrad           = !empty($arUser['UF_GRADUATE_YEAR']);
$isMember         = defined('PO_MEMBER_BASIC_ID') && $USER->IsInGroup([
    PO_MEMBER_BASIC_ID,
    PO_MEMBER_PREMIUM_ID,
    PO_PARTNER_ID,
]);

$typeLabels = [
    'basic'   => ['label' => 'Базовое',         'price' => '5 000 Р',          'class' => ''],
    'premium' => ['label' => 'Профессиональное', 'price' => '50 000 Р',         'class' => 'account__rate--proff'],
    'partner' => ['label' => 'Партнёрское',      'price' => 'Инд. условия',     'class' => 'account__rate--proff'],
    'honorary'=> ['label' => 'Почётное',         'price' => 'Безвозмездно',     'class' => ''],
];
$statusLabels = [
    'pending'  => ['label' => 'На рассмотрении', 'class' => 'account__rate-status--pending'],
    'active'   => ['label' => 'Активный',         'class' => ''],
    'rejected' => ['label' => 'Отклонено',        'class' => 'account__rate-status--error'],
];
$currentType   = $typeLabels[$membershipType]   ?? null;
$currentStatus = $statusLabels[$membershipStatus] ?? null;
?>

<main>
    <section class="account">
        <div class="container">
            <div class="account__wrapper">
                <div class="account__sidebar">
                    <div class="account__menu">
                        <a href="/profile/" class="account__menu-item account__menu-item--active">Мой профиль</a>
                        <a href="#" class="account__menu-item">Безопасность</a>
                        <a href="#" class="account__menu-item">Мои активности</a>
                        <a href="#" class="account__menu-item">Мои заявки</a>
                    </div>
                </div>

                <div class="account__main">
                    <div class="account__block">
                        <h2 class="account__title">Мой профиль</h2>

                        <?php if ($saveOk): ?>
                            <div class="authorization__alert authorization__alert--success" style="margin-bottom:16px">
                                <p>Данные успешно сохранены</p>
                            </div>
                        <?php endif; ?>
                        <?php if ($joinedOk): ?>
                            <div class="authorization__alert authorization__alert--success" style="margin-bottom:16px">
                                <p>Ваша заявка на вступление принята и находится на рассмотрении</p>
                            </div>
                        <?php endif; ?>
                        <?php if ($saveError): ?>
                            <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                                <p><?= htmlspecialchars($saveError) ?></p>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/profile/" enctype="multipart/form-data">
                            <input type="hidden" name="update_action" value="1">

                            <div class="account__photo">
                                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/avatar.png" alt="" class="account__photo-image">
                                <div class="account__photo-content">
                                    <label class="account__photo-upload">
                                        Загрузить аватар
                                        <input type="file" name="photo" class="account__photo-input" accept="image/png, image/jpeg">
                                    </label>
                                    <p>Изображение 300x300, формат jpg, png</p>
                                </div>
                            </div>

                            <div class="account__personal">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Личные данные</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list account__grid">
                                    <input type="text" name="last_name"   placeholder="Фамилия"
                                           value="<?= htmlspecialchars($arUser['LAST_NAME']   ?? '') ?>">
                                    <input type="text" name="first_name"  placeholder="Имя"
                                           value="<?= htmlspecialchars($arUser['NAME']        ?? '') ?>">
                                    <input type="text" name="second_name" placeholder="Отчество"
                                           value="<?= htmlspecialchars($arUser['SECOND_NAME'] ?? '') ?>">
                                    <input type="email" placeholder="Электропочта"
                                           value="<?= htmlspecialchars($arUser['EMAIL'] ?? '') ?>" readonly
                                           title="Email изменяется через раздел Безопасность">
                                </div>
                            </div>

                            <div class="account__graduate">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Выпускник МГТУ?</h3>
                                </div>
                                <div class="account__graduate-choice">
                                    <label class="account__graduate-item">
                                        <input type="radio" name="is_graduate" value="yes" class="account__graduate-input" id="grad-yes"
                                               <?= $isGrad ? 'checked' : '' ?>>
                                        <span class="account__graduate-box"></span>Да
                                    </label>
                                    <label class="account__graduate-item">
                                        <input type="radio" name="is_graduate" value="no"  class="account__graduate-input" id="grad-no"
                                               <?= !$isGrad ? 'checked' : '' ?>>
                                        <span class="account__graduate-box"></span>Нет
                                    </label>
                                </div>
                            </div>

                            <div class="account__personal" id="graduate-data" style="<?= !$isGrad ? 'display:none' : '' ?>">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Данные выпускника</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list account__personal-list--short account__grid">
                                    <input type="number" name="grad_year" placeholder="Год окончания" min="1900" max="2099"
                                           value="<?= htmlspecialchars($arUser['UF_GRADUATE_YEAR'] ?? '') ?>">
                                    <input type="text" name="grad_dept" placeholder="Выпускающая кафедра"
                                           value="<?= htmlspecialchars($arUser['UF_GRADUATE_DEPT'] ?? '') ?>">
                                    <input type="text" name="telegram" placeholder="Telegram"
                                           value="<?= htmlspecialchars($arUser['UF_TELEGRAM'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="account__personal" id="diploma-data" style="<?= !$isGrad ? 'display:none' : '' ?>">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">Сведения о дипломе</h3>
                                    <button type="button" class="account__chapter-edit" data-toggle-edit>Редактировать</button>
                                </div>
                                <div class="account__personal-list account__personal-list--short account__grid">
                                    <input type="text" name="diploma_series" placeholder="Серия бланка"
                                           value="<?= htmlspecialchars($arUser['UF_DIPLOMA_SERIES'] ?? '') ?>">
                                    <input type="text" name="diploma_number" placeholder="Номер бланка"
                                           value="<?= htmlspecialchars($arUser['UF_DIPLOMA_NUMBER'] ?? '') ?>">
                                    <input type="text" name="diploma_date"   placeholder="Дата выдачи"
                                           value="<?= htmlspecialchars($arUser['UF_DIPLOMA_DATE']   ?? '') ?>">
                                </div>
                            </div>

                            <button type="submit" class="btn authorization__btn" style="margin-top:24px">Сохранить</button>
                        </form>

                        <!-- Блок тарифа -->
                        <div class="account__chapter" style="margin-top:40px">
                            <h3 class="account__subtitle">Ваш тариф</h3>
                        </div>

                        <?php if ($currentType && $membershipStatus): ?>
                        <div class="account__rate <?= $currentType['class'] ?>">
                            <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/rate-conus.png" alt="" class="account__rate-conus">
                            <div class="account__rate-info">
                                <span class="account__rate-status <?= $currentStatus['class'] ?? '' ?>">
                                    <?= htmlspecialchars($currentStatus['label'] ?? $membershipStatus) ?>
                                </span>
                                <?php if ($membershipExpires && $membershipStatus === 'active'): ?>
                                <div class="account__rate-date">
                                    <span>Срок действия</span> до <?= htmlspecialchars($membershipExpires) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <h4 class="account__rate-plan"><?= htmlspecialchars($currentType['label']) ?></h4>
                            <p class="account__rate-price"><?= htmlspecialchars($currentType['price']) ?></p>
                            <p class="account__rate-when">ежегодно</p>
                            <?php if ($membershipStatus === 'active'): ?>
                            <div class="account__rate-buttons account__grid">
                                <button class="account__rate-btn btn">Продлить</button>
                                <a href="/join/" class="account__rate-btn account__rate-btn--changes btn">Изменить тариф</a>
                            </div>
                            <?php elseif ($membershipStatus === 'pending'): ?>
                            <p style="margin-top:12px;color:#666">
                                Заявка принята. После проверки модератором членство будет активировано.
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="account__rate">
                            <p>У вас пока нет активного членства.</p>
                            <a href="/join/" class="btn" style="margin-top:12px">Вступить в общество</a>
                        </div>
                        <?php endif; ?>

                        <!-- Привязка соцсетей (статичная заготовка) -->
                        <div class="account__log" style="margin-top:40px">
                            <div class="account__chapter">
                                <h3 class="account__subtitle">Привязка аккаунта для быстрого входа</h3>
                            </div>
                            <div class="account__log-wrapper">
                                <button class="account__log-btn" disabled>
                                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/yandex-icon.png" alt="">
                                    Войти через Яндекс
                                </button>
                                <button class="account__log-btn" disabled>
                                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/gos-icon.png" alt="">
                                    Войти через Госуслуги
                                </button>
                                <button class="account__log-btn" disabled>
                                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/vk-icon.png" alt="">
                                    Войти через Вконтакте
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.querySelectorAll('[name="is_graduate"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var show = this.value === 'yes';
        ['graduate-data','diploma-data'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.style.display = show ? '' : 'none';
        });
    });
});
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
