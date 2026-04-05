<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Карьерная платформа");

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$vacDone  = false;
$resDone  = false;
$vacError = '';
$resError = '';
$activeForm = '';

// ── Вакансия ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['vacancy_action'])) {
    $company   = trim($_POST['vac_company']   ?? '');
    $site      = trim($_POST['vac_site']      ?? '');
    $position  = trim($_POST['vac_position']  ?? '');
    $lname     = trim($_POST['vac_lname']     ?? '');
    $fname     = trim($_POST['vac_fname']     ?? '');
    $sname     = trim($_POST['vac_sname']     ?? '');
    $phone     = trim($_POST['vac_phone']     ?? '');
    $email     = trim($_POST['vac_email']     ?? '');
    $agreePd   = ($_POST['vac_agree_pd'] ?? '') === 'yes';

    if (!$company || !$position) {
        $vacError = 'Заполните обязательные поля: Компания, Должность.';
    } elseif (!$agreePd) {
        $vacError = 'Необходимо согласие с политикой ПДн.';
    } else {
        $attachFileId = null;
        if (!empty($_FILES['vac_attachment']['name']) && $_FILES['vac_attachment']['error'] === UPLOAD_ERR_OK) {
            $attachFileId = CFile::SaveFile(
                CFile::MakeFileArray($_FILES['vac_attachment']['tmp_name'], $_FILES['vac_attachment']['name']),
                'applications'
            );
        }
        $vacDone = true;
        po_sendAdminEmail('vacancy', [
            'company'  => $company, 'site'   => $site,
            'position' => $position,
            'contact'  => "$lname $fname $sname",
            'phone'    => $phone, 'email' => $email,
        ]);
        po_logAction('form_submit', 'application', 0, 'Вакансия: ' . $position);
    }
    $activeForm = 'vacancy';
}

// ── Резюме ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resume_action'])) {
    $lname    = trim($_POST['res_lname']    ?? '');
    $fname    = trim($_POST['res_fname']    ?? '');
    $sname    = trim($_POST['res_sname']    ?? '');
    $dob      = trim($_POST['res_dob']      ?? '');
    $dept     = trim($_POST['res_dept']     ?? '');
    $year     = trim($_POST['res_year']     ?? '');
    $sphere   = trim($_POST['res_sphere']   ?? '');
    $exp      = trim($_POST['res_exp']      ?? '');
    $position = trim($_POST['res_position'] ?? '');
    $agreePd  = ($_POST['res_agree_pd'] ?? '') === 'yes';

    if (!$fname && !$lname) {
        $resError = 'Введите хотя бы имя или фамилию.';
    } elseif (!$agreePd) {
        $resError = 'Необходимо согласие с политикой ПДн.';
    } else {
        $resumeFileId = null;
        if (!empty($_FILES['res_attachment']['name']) && $_FILES['res_attachment']['error'] === UPLOAD_ERR_OK) {
            $resumeFileId = CFile::SaveFile(
                CFile::MakeFileArray($_FILES['res_attachment']['tmp_name'], $_FILES['res_attachment']['name']),
                'applications'
            );
        }
        $resDone = true;
        po_sendAdminEmail('resume', [
            'name'     => "$lname $fname $sname",
            'dob'      => $dob, 'dept'     => $dept,
            'year'     => $year, 'sphere'   => $sphere,
            'exp'      => $exp,  'position' => $position,
        ]);
        po_logAction('form_submit', 'application', 0, 'Резюме: ' . $position);
    }
    $activeForm = 'resume';
}
?>

<main>
    <!-- Карточки выбора -->
    <section class="resume-select">
        <div class="container">
            <div class="resume-select__wrapper">
                <div class="resume-select__card">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-1.png" alt="" class="resume-select__image desk-block">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-mob-1.png" alt="" class="resume-select__image desk-none">
                    <div>
                        <h2 class="main-title">Вакансия от компании</h2>
                        <button class="btn resume-select__btn" id="btn-open-vacancy">Разместить вакансию</button>
                    </div>
                </div>
                <div class="resume-select__card">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-2.png" alt="" class="resume-select__image desk-block">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-mob-2.png" alt="" class="resume-select__image desk-none">
                    <div>
                        <h2 class="main-title">Резюме выпускника</h2>
                        <button class="btn resume-select__btn resume-select__btn--blue" id="btn-open-resume">Разместить моё резюме</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Форма: Резюме выпускника -->
    <section class="join" id="section-resume"
             style="display:<?= $activeForm === 'resume' ? '' : 'none' ?>">
        <div class="container">
            <div class="join__wrapper">
                <?php if ($resDone): ?>
                <div style="text-align:center;padding:48px 0">
                    <div style="font-size:52px;margin-bottom:14px">✅</div>
                    <h2 class="account__title main-title">Резюме отправлено!</h2>
                    <p style="color:#666;margin-top:10px">Мы свяжемся с вами в ближайшее время.</p>
                    <button class="btn" onclick="document.getElementById('section-resume').style.display='none'" style="margin-top:20px">Закрыть</button>
                </div>
                <?php else: ?>

                <div class="join-have-acc">
                    <h3>Есть аккаунт?</h3>
                    <p>Войти или Зарегистрироваться, чтобы заполнить форму быстрее</p>
                    <div class="join-have-acc__buttons">
                        <a href="/registration/" class="btn join-have-acc__btn">Зарегистрироваться</a>
                        <a href="#" class="btn join-have-acc__btn join-have-acc__btn-sign" data-fancybox data-src="#form-login">Войти</a>
                    </div>
                </div>

                <h2 class="account__title main-title">Резюме выпускника</h2>

                <?php if ($resError): ?>
                <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                    <p><?= htmlspecialchars($resError) ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="/resume-form/" enctype="multipart/form-data">
                    <input type="hidden" name="resume_action" value="1">

                    <div class="account__personal">
                        <div class="account__chapter"><h3 class="account__subtitle">Личные данные</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="res_lname"  placeholder="Фамилия"
                                   value="<?= htmlspecialchars($_POST['res_lname'] ?? ($USER->IsAuthorized() ? $USER->GetParam('LAST_NAME') : '')) ?>">
                            <input type="text" name="res_fname"  placeholder="Имя"
                                   value="<?= htmlspecialchars($_POST['res_fname'] ?? ($USER->IsAuthorized() ? $USER->GetParam('NAME') : '')) ?>">
                            <input type="text" name="res_sname"  placeholder="Отчество"
                                   value="<?= htmlspecialchars($_POST['res_sname'] ?? ($USER->IsAuthorized() ? $USER->GetParam('SECOND_NAME') : '')) ?>">
                            <input type="text" name="res_dob"    placeholder="Дата рождения"
                                   value="<?= htmlspecialchars($_POST['res_dob'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Образование</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="res_dept" placeholder="Выпускающая кафедра"
                                   value="<?= htmlspecialchars($_POST['res_dept'] ?? '') ?>">
                            <select name="res_year">
                                <option value="">Год выпуска</option>
                                <?php for ($y = date('Y'); $y >= 1950; $y--): ?>
                                <option value="<?=$y?>" <?= ($_POST['res_year'] ?? '') == $y ? 'selected' : '' ?>><?=$y?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Данные об опыте работы</h3></div>
                        <div class="account__personal-list account__grid--range">
                            <input type="text" name="res_sphere"   placeholder="Сфера деятельности"
                                   value="<?= htmlspecialchars($_POST['res_sphere'] ?? '') ?>">
                            <input type="text" name="res_exp"      placeholder="Стаж"
                                   value="<?= htmlspecialchars($_POST['res_exp'] ?? '') ?>">
                            <input type="text" name="res_position" placeholder="Желаемая должность"
                                   value="<?= htmlspecialchars($_POST['res_position'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="account__file" style="margin-top:24px">
                        <div class="account__file-info">
                            <div class="account__file-content account__photo-content">
                                <label class="account__photo-upload">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none">
                                        <path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="black"/>
                                    </svg>
                                    Перетащите или <span>загрузите файл</span> PDF
                                    <input type="file" name="res_attachment" class="account__photo-input" accept=".pdf,.doc,.docx">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <p class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="res_agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="res_agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">Разместить моё резюме</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Форма: Вакансия от компании -->
    <section class="join" id="section-vacancy"
             style="display:<?= $activeForm === 'vacancy' ? '' : 'none' ?>">
        <div class="container">
            <div class="join__wrapper">
                <?php if ($vacDone): ?>
                <div style="text-align:center;padding:48px 0">
                    <div style="font-size:52px;margin-bottom:14px">✅</div>
                    <h2 class="account__title main-title">Вакансия отправлена!</h2>
                    <p style="color:#666;margin-top:10px">Мы свяжемся с вами в ближайшее время.</p>
                    <button class="btn" onclick="document.getElementById('section-vacancy').style.display='none'" style="margin-top:20px">Закрыть</button>
                </div>
                <?php else: ?>

                <div class="join-have-acc">
                    <h3>Есть аккаунт?</h3>
                    <p>Войти или Зарегистрироваться, чтобы заполнить форму быстрее</p>
                    <div class="join-have-acc__buttons">
                        <a href="/registration/" class="btn join-have-acc__btn">Зарегистрироваться</a>
                        <a href="#" class="btn join-have-acc__btn join-have-acc__btn-sign" data-fancybox data-src="#form-login">Войти</a>
                    </div>
                </div>

                <h2 class="account__title main-title">Вакансия от компании</h2>

                <?php if ($vacError): ?>
                <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                    <p><?= htmlspecialchars($vacError) ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="/resume-form/" enctype="multipart/form-data">
                    <input type="hidden" name="vacancy_action" value="1">

                    <div class="account__personal">
                        <div class="account__chapter"><h3 class="account__subtitle">Данные о компании</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="vac_company" placeholder="Компания" required
                                   value="<?= htmlspecialchars($_POST['vac_company'] ?? '') ?>">
                            <input type="text" name="vac_site"    placeholder="Сайт"
                                   value="<?= htmlspecialchars($_POST['vac_site'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Данные о вакансии</h3></div>
                        <input type="text" name="vac_position" placeholder="Название должности" required style="width:100%"
                               value="<?= htmlspecialchars($_POST['vac_position'] ?? '') ?>">
                    </div>

                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Контакты для отклика</h3></div>
                        <div class="account__personal-list account__grid--tripl">
                            <input type="text"  name="vac_lname" placeholder="Фамилия"
                                   value="<?= htmlspecialchars($_POST['vac_lname'] ?? ($USER->IsAuthorized() ? $USER->GetParam('LAST_NAME') : '')) ?>">
                            <input type="text"  name="vac_fname" placeholder="Имя"
                                   value="<?= htmlspecialchars($_POST['vac_fname'] ?? ($USER->IsAuthorized() ? $USER->GetParam('NAME') : '')) ?>">
                            <input type="text"  name="vac_sname" placeholder="Отчество"
                                   value="<?= htmlspecialchars($_POST['vac_sname'] ?? ($USER->IsAuthorized() ? $USER->GetParam('SECOND_NAME') : '')) ?>">
                            <input type="tel"   name="vac_phone" placeholder="Номер телефона"
                                   value="<?= htmlspecialchars($_POST['vac_phone'] ?? '') ?>">
                            <input type="email" name="vac_email" placeholder="Электропочта"
                                   value="<?= htmlspecialchars($_POST['vac_email'] ?? ($USER->IsAuthorized() ? $USER->GetParam('EMAIL') : '')) ?>">
                        </div>
                    </div>

                    <div class="account__file" style="margin-top:24px">
                        <div class="account__file-info">
                            <div class="account__file-content account__photo-content">
                                <label class="account__photo-upload">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none">
                                        <path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="black"/>
                                    </svg>
                                    Перетащите или <span>загрузите файл</span> PDF
                                    <input type="file" name="vac_attachment" class="account__photo-input" accept=".pdf,.doc,.docx">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <p class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="vac_agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="vac_agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">Разместить вакансию</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
(function() {
    function showForm(id) {
        var sections = ['section-vacancy', 'section-resume'];
        sections.forEach(function(s) {
            var el = document.getElementById(s);
            if (el) el.style.display = (s === id) ? '' : 'none';
        });
        var el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    var btnV = document.getElementById('btn-open-vacancy');
    var btnR = document.getElementById('btn-open-resume');
    if (btnV) btnV.addEventListener('click', function() { showForm('section-vacancy'); });
    if (btnR) btnR.addEventListener('click', function() { showForm('section-resume'); });
})();
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
