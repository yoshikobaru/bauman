<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Карьерная платформа");
$APPLICATION->SetPageProperty('description', 'Карьерная платформа Политехнического общества выпускников МГТУ им. Н.Э. Баумана: вакансии от партнёров и резюме выпускников.');

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$_userGroups  = $USER->IsAuthorized() ? $USER->GetUserGroupArray() : [];
$_isMember    = defined('PO_MEMBER_BASIC_ID') && (
    in_array(PO_MEMBER_BASIC_ID,   $_userGroups) ||
    in_array(PO_MEMBER_PREMIUM_ID, $_userGroups) ||
    in_array(PO_PARTNER_ID,        $_userGroups)
);
$_isPartner   = defined('PO_PARTNER_ID')    && in_array(PO_PARTNER_ID,   $_userGroups);
$_isModerator = $USER->IsAdmin() || (defined('PO_MODERATOR_ID') && in_array(PO_MODERATOR_ID, $_userGroups));
$_canPostVac  = $_isPartner || $_isModerator;
$_canViewRes  = $_isPartner || $_isModerator;

$vacDone  = false;
$resDone  = false;
$errors   = [];
$activeForm = $_GET['form'] ?? '';   // 'vacancy' | 'resume'

// —— Обработчик: разместить вакансию ——
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['vacancy_action'])) {
    if (!$_canPostVac) {
        $errors[] = 'Размещение вакансий доступно только партнёрам общества.';
    } else {
        $company  = trim($_POST['company']        ?? '');
        $position = trim($_POST['position']       ?? '');
        $desc     = trim($_POST['description']    ?? '');
        $req      = trim($_POST['requirements']   ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');

        if (!$company || !$position) {
            $errors[] = 'Заполните обязательные поля: Компания, Должность.';
        } else {
            $saved = false;
            if ($hlOk && defined('HL_VACANCIES_ID') && HL_VACANCIES_ID > 0) {
                $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_VACANCIES_ID)->fetch();
                if ($hlEntity) {
                    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                    $res = $hlClass::add([
                        'UF_USER_ID'       => (int)$USER->GetID(),
                        'UF_COMPANY'       => $company,
                        'UF_POSITION'      => $position,
                        'UF_DESCRIPTION'   => $desc,
                        'UF_REQUIREMENTS'  => $req,
                        'UF_CONTACT_EMAIL' => $contactEmail,
                        'UF_STATUS'        => 'pending',
                        'UF_DATE_CREATE'   => new \Bitrix\Main\Type\DateTime(),
                    ]);
                    $saved = $res->isSuccess();
                }
            } else {
                $saved = true; // HL не настроен — принимаем
            }
            if ($saved) {
                $vacDone = true;
                po_logAction('form_submit', 'application', 0, 'Вакансия: ' . $position . ' (' . $company . ')');
                po_sendAdminEmail('vacancy', [
                    'company'  => $company, 'position' => $position,
                    'email'    => $contactEmail,
                ]);
            } else {
                $errors[] = 'Ошибка сохранения. Попробуйте позже.';
            }
        }
    }
    $activeForm = 'vacancy';
}

// —— Обработчик: разместить резюме ——
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resume_action'])) {
    if (!$_isMember) {
        $errors[] = 'Размещение резюме доступно только членам общества.';
    } else {
        $position   = trim($_POST['position']      ?? '');
        $skills     = trim($_POST['skills']        ?? '');
        $experience = trim($_POST['experience']    ?? '');
        $contact    = trim($_POST['contact_email'] ?? '');
        $agreePd    = ($_POST['agree_pd'] ?? '') === 'yes';

        if (!$position)  $errors[] = 'Укажите желаемую должность.';
        if (!$agreePd)   $errors[] = 'Необходимо согласие с политикой ПДн.';

        if (empty($errors)) {
            $saved = false;
            if ($hlOk && defined('HL_RESUMES_ID') && HL_RESUMES_ID > 0) {
                $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_RESUMES_ID)->fetch();
                if ($hlEntity) {
                    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                    $res = $hlClass::add([
                        'UF_USER_ID'       => (int)$USER->GetID(),
                        'UF_POSITION'      => $position,
                        'UF_SKILLS'        => $skills,
                        'UF_EXPERIENCE'    => $experience,
                        'UF_CONTACT_EMAIL' => $contact,
                        'UF_STATUS'        => 'pending',
                        'UF_DATE_CREATE'   => new \Bitrix\Main\Type\DateTime(),
                    ]);
                    $saved = $res->isSuccess();
                }
            } else {
                $saved = true;
            }
            if ($saved) {
                $resDone = true;
                po_logAction('form_submit', 'application', 0, 'Резюме: ' . $position);
            } else {
                $errors[] = 'Ошибка сохранения. Попробуйте позже.';
            }
        }
    }
    $activeForm = 'resume';
}

// —— Загрузка вакансий (публичный список) ——
$arVacancies = [];
if ($hlOk && defined('HL_VACANCIES_ID') && HL_VACANCIES_ID > 0) {
    $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_VACANCIES_ID)->fetch();
    if ($hlEntity) {
        $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
        $dbVac = $hlClass::getList([
            'filter' => ['UF_STATUS' => 'approved'],
            'order'  => ['UF_DATE_CREATE' => 'DESC'],
        ]);
        while ($row = $dbVac->fetch()) {
            $arVacancies[] = $row;
        }
    }
}

// —— Загрузка резюме (только для партнёров/модераторов) ——
$arResumes = [];
if ($_canViewRes && $hlOk && defined('HL_RESUMES_ID') && HL_RESUMES_ID > 0) {
    $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_RESUMES_ID)->fetch();
    if ($hlEntity) {
        $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
        $dbRes = $hlClass::getList([
            'filter' => ['UF_STATUS' => 'approved'],
            'order'  => ['UF_DATE_CREATE' => 'DESC'],
        ]);
        while ($row = $dbRes->fetch()) {
            $arResumes[] = $row;
        }
    }
}
?>

<main>
    <!-- Баннер -->
    <section class="banner-other">
        <div class="container">
            <div class="banner-other__wrapper">
                <div class="banner-other__content">
                    <div class="banner-other__info">
                        <h1 class="banner-other__title main-title">Карьерная платформа</h1>
                        <p style="margin-top:12px;color:#fff;font-size:16px;max-width:480px">
                            Вакансии от компаний-партнёров и резюме выпускников МГТУ им. Н.Э. Баумана
                        </p>
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                </div>
                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-1.png" alt="" class="banner-other__image">
            </div>
        </div>
    </section>

    <!-- Кнопки действий -->
    <section class="resume-select">
        <div class="container">
            <div class="resume-select__wrapper">
                <div class="resume-select__card">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-1.png" alt="" class="resume-select__image desk-block">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-mob-1.png" alt="" class="resume-select__image desk-none">
                    <div>
                        <h2 class="main-title">Вакансия от компании</h2>
                        <?php if ($_canPostVac): ?>
                        <a href="?form=vacancy" class="btn resume-select__btn">Разместить вакансию</a>
                        <?php else: ?>
                        <p style="margin-top:8px;font-size:13px;color:#888">Доступно для партнёров общества</p>
                        <a href="/join/" class="btn resume-select__btn btn-empty" style="margin-top:8px">Стать партнёром</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="resume-select__card">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-2.png" alt="" class="resume-select__image desk-block">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-mob-2.png" alt="" class="resume-select__image desk-none">
                    <div>
                        <h2 class="main-title">Резюме выпускника</h2>
                        <?php if ($_isMember): ?>
                        <a href="?form=resume" class="btn resume-select__btn resume-select__btn--blue">Разместить моё резюме</a>
                        <?php else: ?>
                        <p style="margin-top:8px;font-size:13px;color:#888">Доступно для членов общества</p>
                        <a href="/join/" class="btn resume-select__btn resume-select__btn--blue btn-empty" style="margin-top:8px">Вступить</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($errors)): ?>
    <section><div class="container">
        <div class="authorization__alert authorization__alert--error" style="margin:16px 0">
            <?php foreach ($errors as $msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endforeach; ?>
        </div>
    </div></section>
    <?php endif; ?>

    <!-- Форма: разместить вакансию -->
    <?php if ($activeForm === 'vacancy'): ?>
    <section class="join">
        <div class="container">
            <div class="join__wrapper">
                <?php if ($vacDone): ?>
                <div style="text-align:center;padding:40px 0">
                    <div style="font-size:48px;margin-bottom:12px">✅</div>
                    <h2 class="account__title main-title">Вакансия отправлена на модерацию!</h2>
                    <p style="margin-top:12px;color:#666">После проверки модератором вакансия появится в общем листинге.</p>
                    <a href="/resume-form/" class="btn" style="margin-top:20px">Вернуться к платформе</a>
                </div>
                <?php elseif (!$_canPostVac): ?>
                <h2 class="account__title main-title">Разместить вакансию</h2>
                <p style="margin-top:16px;color:#666">Размещение вакансий доступно только <strong>партнёрам</strong> Политехнического общества.
                    <a href="/join/">Узнать о партнёрстве</a>
                </p>
                <?php else: ?>
                <h2 class="account__title main-title">Разместить вакансию</h2>
                <form method="POST" action="/resume-form/?form=vacancy">
                    <input type="hidden" name="vacancy_action" value="1">
                    <div class="account__personal">
                        <div class="account__chapter"><h3 class="account__subtitle">Данные о компании</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="company"  placeholder="Название компании *" required
                                   value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
                            <input type="email" name="contact_email" placeholder="Email для откликов"
                                   value="<?= htmlspecialchars($_POST['contact_email'] ?? $USER->GetParam('EMAIL')) ?>">
                        </div>
                    </div>
                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">О вакансии</h3></div>
                        <input type="text" name="position" placeholder="Название должности *" required
                               style="width:100%;margin-bottom:12px"
                               value="<?= htmlspecialchars($_POST['position'] ?? '') ?>">
                        <textarea name="description" placeholder="Описание вакансии"
                                  style="width:100%;min-height:100px;padding:12px;border:1px solid #ccc;border-radius:4px;margin-bottom:12px;font-size:14px"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        <textarea name="requirements" placeholder="Требования к кандидату"
                                  style="width:100%;min-height:80px;padding:12px;border:1px solid #ccc;border-radius:4px;font-size:14px"><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">Отправить на модерацию</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Форма: разместить резюме -->
    <?php elseif ($activeForm === 'resume'): ?>
    <section class="join">
        <div class="container">
            <div class="join__wrapper">
                <?php if ($resDone): ?>
                <div style="text-align:center;padding:40px 0">
                    <div style="font-size:48px;margin-bottom:12px">✅</div>
                    <h2 class="account__title main-title">Резюме отправлено на модерацию!</h2>
                    <p style="margin-top:12px;color:#666">После проверки резюме будет доступно партнёрам общества.</p>
                    <a href="/resume-form/" class="btn" style="margin-top:20px">Вернуться к платформе</a>
                </div>
                <?php elseif (!$USER->IsAuthorized()): ?>
                <h2 class="account__title main-title">Разместить резюме</h2>
                <p style="margin-top:16px">Для размещения резюме необходимо <a href="/authorization/?back_url=/resume-form/?form=resume">войти</a> или <a href="/join/">вступить в общество</a>.</p>
                <?php elseif (!$_isMember): ?>
                <h2 class="account__title main-title">Разместить резюме</h2>
                <p style="margin-top:16px">Размещение резюме доступно только членам Политехнического общества. <a href="/join/">Вступить</a></p>
                <?php else: ?>
                <h2 class="account__title main-title">Разместить резюме</h2>
                <form method="POST" action="/resume-form/?form=resume">
                    <input type="hidden" name="resume_action" value="1">
                    <div class="account__personal">
                        <div class="account__chapter"><h3 class="account__subtitle">О себе</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="position" placeholder="Желаемая должность *" required
                                   value="<?= htmlspecialchars($_POST['position'] ?? '') ?>">
                            <input type="email" name="contact_email" placeholder="Контактный email"
                                   value="<?= htmlspecialchars($_POST['contact_email'] ?? $USER->GetParam('EMAIL')) ?>">
                        </div>
                    </div>
                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Навыки и опыт</h3></div>
                        <textarea name="skills" placeholder="Ключевые навыки (через запятую)"
                                  style="width:100%;min-height:80px;padding:12px;border:1px solid #ccc;border-radius:4px;margin-bottom:12px;font-size:14px"><?= htmlspecialchars($_POST['skills'] ?? '') ?></textarea>
                        <textarea name="experience" placeholder="Опыт работы"
                                  style="width:100%;min-height:100px;padding:12px;border:1px solid #ccc;border-radius:4px;font-size:14px"><?= htmlspecialchars($_POST['experience'] ?? '') ?></textarea>
                    </div>
                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <p class="join__politic-link">Согласен с <a href="#">политикой обработки ПДн</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">Разместить резюме</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Листинг вакансий -->
    <section class="join" style="background:#f8f8f8;padding-top:40px;padding-bottom:40px">
        <div class="container">
            <h2 class="main-title" style="margin-bottom:24px">Актуальные вакансии</h2>
            <?php if (empty($arVacancies)): ?>
            <p style="color:#888">Вакансий пока нет. Если вы партнёр — <a href="?form=vacancy">разместите первую вакансию</a>.</p>
            <?php else: ?>
            <div style="display:grid;gap:16px">
                <?php foreach ($arVacancies as $vac): ?>
                <div style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,.06)">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px">
                        <div>
                            <h3 style="font-size:18px;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($vac['UF_POSITION'] ?? '') ?></h3>
                            <p style="color:#555;font-size:14px"><?= htmlspecialchars($vac['UF_COMPANY'] ?? '') ?></p>
                        </div>
                        <?php if (!empty($vac['UF_DATE_CREATE'])): ?>
                        <span style="color:#aaa;font-size:12px"><?= $vac['UF_DATE_CREATE']->format('d.m.Y') ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($vac['UF_DESCRIPTION'])): ?>
                    <p style="margin-top:12px;color:#444;font-size:14px;line-height:1.6"><?= nl2br(htmlspecialchars($vac['UF_DESCRIPTION'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($vac['UF_REQUIREMENTS'])): ?>
                    <p style="margin-top:8px;color:#555;font-size:13px"><strong>Требования:</strong> <?= htmlspecialchars($vac['UF_REQUIREMENTS']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($vac['UF_CONTACT_EMAIL'])): ?>
                    <p style="margin-top:10px">
                        <a href="mailto:<?= htmlspecialchars($vac['UF_CONTACT_EMAIL']) ?>" class="btn" style="font-size:13px;padding:8px 16px">
                            Откликнуться
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Листинг резюме (только партнёры/модераторы) -->
    <?php if ($_canViewRes): ?>
    <section class="join" style="padding-top:40px;padding-bottom:40px">
        <div class="container">
            <h2 class="main-title" style="margin-bottom:24px">База резюме выпускников</h2>
            <?php if (empty($arResumes)): ?>
            <p style="color:#888">Резюме пока не размещены.</p>
            <?php else: ?>
            <div style="display:grid;gap:16px">
                <?php foreach ($arResumes as $res): ?>
                <div style="background:#f0f8ff;border-radius:12px;padding:20px;border-left:3px solid #2980b9">
                    <h3 style="font-size:16px;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($res['UF_POSITION'] ?? '') ?></h3>
                    <?php if (!empty($res['UF_SKILLS'])): ?>
                    <p style="color:#444;font-size:13px;margin-top:6px"><strong>Навыки:</strong> <?= htmlspecialchars($res['UF_SKILLS']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($res['UF_EXPERIENCE'])): ?>
                    <p style="color:#444;font-size:13px;margin-top:4px"><strong>Опыт:</strong> <?= htmlspecialchars($res['UF_EXPERIENCE']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($res['UF_CONTACT_EMAIL'])): ?>
                    <p style="margin-top:10px">
                        <a href="mailto:<?= htmlspecialchars($res['UF_CONTACT_EMAIL']) ?>" style="color:#2980b9;text-decoration:none;font-size:13px">
                            <?= htmlspecialchars($res['UF_CONTACT_EMAIL']) ?>
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
