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

$allowedExts = ['pdf', 'doc', 'docx'];

function po_checkFileExt($file, $allowed) {
    if (empty($file['name'])) return true;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return in_array($ext, $allowed);
}

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
    $agreePd   = !empty($_POST['vac_agree_pd']);

    if (!$company || !$position) {
        $vacError = 'Заполните обязательные поля: Компания, Должность.';
    } elseif (!$agreePd) {
        $vacError = 'Необходимо согласие с политикой обработки ПДн.';
    } elseif (!empty($_FILES['vac_attachment']['name']) && !po_checkFileExt($_FILES['vac_attachment'], $allowedExts)) {
        $vacError = 'Недопустимый формат файла. Разрешены: PDF, DOC, DOCX.';
    } elseif (empty($_FILES['vac_attachment']['name']) || $_FILES['vac_attachment']['error'] !== UPLOAD_ERR_OK) {
        $vacError = 'Прикрепите файл вакансии (PDF, DOC, DOCX).';
    } else {
        $attachFileId = CFile::SaveFile(
            CFile::MakeFileArray($_FILES['vac_attachment']['tmp_name'], $_FILES['vac_attachment']['name']),
            'applications'
        );
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
    // res_dob_hidden содержит дату в формате DD.MM.ГГГГ из JS, res_dob — нативное type=date значение
    $dob      = trim($_POST['res_dob_hidden'] ?? trim($_POST['res_dob'] ?? ''));
    $dept     = trim($_POST['res_dept']     ?? '');
    $year     = trim($_POST['res_year']     ?? '');
    $sphere   = trim($_POST['res_sphere']   ?? '');
    $exp      = (int)($_POST['res_exp']     ?? 0);
    $position = trim($_POST['res_position'] ?? '');
    $agreePd  = !empty($_POST['res_agree_pd']);

    if (!$fname && !$lname) {
        $resError = 'Введите хотя бы имя или фамилию.';
    } elseif (!$dob) {
        $resError = 'Укажите дату рождения.';
    } elseif (!$year) {
        $resError = 'Укажите год выпуска.';
    } elseif (!$agreePd) {
        $resError = 'Необходимо согласие с политикой обработки ПДн.';
    } elseif (!empty($_FILES['res_attachment']['name']) && !po_checkFileExt($_FILES['res_attachment'], $allowedExts)) {
        $resError = 'Недопустимый формат файла. Разрешены: PDF, DOC, DOCX.';
    } elseif (empty($_FILES['res_attachment']['name']) || $_FILES['res_attachment']['error'] !== UPLOAD_ERR_OK) {
        $resError = 'Прикрепите файл резюме (PDF, DOC, DOCX).';
    } else {
        $resumeFileId = CFile::SaveFile(
            CFile::MakeFileArray($_FILES['res_attachment']['tmp_name'], $_FILES['res_attachment']['name']),
            'applications'
        );
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

<style>
.po-file-name {
    display: block;
    font-size: 13px;
    color: #555;
    margin-top: 6px;
    min-height: 18px;
    word-break: break-all;
}
.po-field-error {
    color: #e74c3c;
    font-size: 13px;
    margin-top: 4px;
    display: none;
}
.po-form-error-box {
    background: #fdecea;
    border: 1px solid #e74c3c;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 16px;
    color: #c0392b;
    display: none;
}
</style>

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

                <div class="po-form-error-box" id="res-error-box"></div>
                <?php if ($resError): ?>
                <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                    <p><?= htmlspecialchars($resError) ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="/resume-form/" enctype="multipart/form-data" id="form-resume" novalidate>
                    <input type="hidden" name="resume_action" value="1">
                    <input type="hidden" name="res_dob_hidden" id="res_dob_hidden">

                    <div class="account__personal">
                        <div class="account__chapter"><h3 class="account__subtitle">Личные данные</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="res_lname"  placeholder="Фамилия"
                                   value="<?= htmlspecialchars($_POST['res_lname'] ?? ($USER->IsAuthorized() ? $USER->GetParam('LAST_NAME') : '')) ?>">
                            <input type="text" name="res_fname"  placeholder="Имя"
                                   value="<?= htmlspecialchars($_POST['res_fname'] ?? ($USER->IsAuthorized() ? $USER->GetParam('NAME') : '')) ?>">
                            <input type="text" name="res_sname"  placeholder="Отчество"
                                   value="<?= htmlspecialchars($_POST['res_sname'] ?? ($USER->IsAuthorized() ? $USER->GetParam('SECOND_NAME') : '')) ?>">
                            <div>
                                <input type="date" name="res_dob" id="res_dob"
                                       placeholder="Дата рождения. Обязательное поле"
                                       required
                                       value="<?= htmlspecialchars($_POST['res_dob'] ?? '') ?>"
                                       style="width:100%">
                                <span class="po-field-error" id="res-dob-err">Укажите дату рождения</span>
                            </div>
                        </div>
                    </div>

                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Образование</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="res_dept" placeholder="Выпускающая кафедра"
                                   value="<?= htmlspecialchars($_POST['res_dept'] ?? '') ?>">
                            <div>
                                <select name="res_year" id="res_year" required>
                                    <option value="">Год выпуска *</option>
                                    <?php for ($y = date('Y'); $y >= 1950; $y--): ?>
                                    <option value="<?=$y?>" <?= ($_POST['res_year'] ?? '') == $y ? 'selected' : '' ?>><?=$y?></option>
                                    <?php endfor; ?>
                                </select>
                                <span class="po-field-error" id="res-year-err">Укажите год выпуска</span>
                            </div>
                        </div>
                    </div>

                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Данные об опыте работы</h3></div>
                        <div class="account__personal-list account__grid--range">
                            <input type="text" name="res_sphere"   placeholder="Сфера деятельности"
                                   value="<?= htmlspecialchars($_POST['res_sphere'] ?? '') ?>">
                            <input type="number" name="res_exp" id="res_exp" placeholder="Стаж (лет)" min="0"
                                   value="<?= htmlspecialchars($_POST['res_exp'] ?? '') ?>">
                            <input type="text" name="res_position" placeholder="Желаемая должность"
                                   value="<?= htmlspecialchars($_POST['res_position'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="account__file" style="margin-top:24px">
                        <div class="account__file-info">
                            <div class="account__file-content account__photo-content">
                                <label class="account__photo-upload" for="res_attachment">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none">
                                        <path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="black"/>
                                    </svg>
                                    Перетащите или <span>загрузите файл</span> (PDF, DOC, DOCX) *
                                    <input type="file" name="res_attachment" id="res_attachment" class="account__photo-input" accept=".pdf,.doc,.docx">
                                </label>
                                <span class="po-file-name" id="res-file-name">Файл не выбран</span>
                                <span class="po-field-error" id="res-file-err">Прикрепите файл резюме (PDF, DOC, DOCX)</span>
                            </div>
                        </div>
                    </div>

                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                                <input type="checkbox" name="res_agree_pd" id="res_agree_pd" required style="width:18px;height:18px;flex-shrink:0">
                                <span class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></span>
                            </label>
                            <span class="po-field-error" id="res-agree-err">Необходимо согласие с политикой обработки ПДн</span>
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

                <div class="po-form-error-box" id="vac-error-box"></div>
                <?php if ($vacError): ?>
                <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                    <p><?= htmlspecialchars($vacError) ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="/resume-form/" enctype="multipart/form-data" id="form-vacancy" novalidate>
                    <input type="hidden" name="vacancy_action" value="1">

                    <div class="account__personal">
                        <div class="account__chapter"><h3 class="account__subtitle">Данные о компании</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="vac_company" placeholder="Компания *" required
                                   value="<?= htmlspecialchars($_POST['vac_company'] ?? '') ?>">
                            <input type="text" name="vac_site"    placeholder="Сайт"
                                   value="<?= htmlspecialchars($_POST['vac_site'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Данные о вакансии</h3></div>
                        <input type="text" name="vac_position" placeholder="Название должности *" required style="width:100%"
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
                            <input type="tel" name="vac_phone" id="vac_phone" placeholder="Номер телефона"
                                   value="<?= htmlspecialchars($_POST['vac_phone'] ?? '') ?>">
                            <input type="email" name="vac_email" placeholder="e-mail"
                                   value="<?= htmlspecialchars($_POST['vac_email'] ?? ($USER->IsAuthorized() ? $USER->GetParam('EMAIL') : '')) ?>">
                        </div>
                    </div>

                    <div class="account__file" style="margin-top:24px">
                        <div class="account__file-info">
                            <div class="account__file-content account__photo-content">
                                <label class="account__photo-upload" for="vac_attachment">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none">
                                        <path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="black"/>
                                    </svg>
                                    Перетащите или <span>загрузите файл</span> (PDF, DOC, DOCX) *
                                    <input type="file" name="vac_attachment" id="vac_attachment" class="account__photo-input" accept=".pdf,.doc,.docx">
                                </label>
                                <span class="po-file-name" id="vac-file-name">Файл не выбран</span>
                                <span class="po-field-error" id="vac-file-err">Прикрепите файл вакансии (PDF, DOC, DOCX)</span>
                            </div>
                        </div>
                    </div>

                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                                <input type="checkbox" name="vac_agree_pd" id="vac_agree_pd" required style="width:18px;height:18px;flex-shrink:0">
                                <span class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></span>
                            </label>
                            <span class="po-field-error" id="vac-agree-err">Необходимо согласие с политикой обработки ПДн</span>
                        </div>
                    </div>

                    <p class="form-required-note">* Обязательные поля</p>
                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">Разместить вакансию</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
(function() {
    // ── Открытие форм ──────────────────────────────────────────────────────────
    function showForm(id) {
        ['section-vacancy', 'section-resume'].forEach(function(s) {
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

    // ── Отображение имени выбранного файла ─────────────────────────────────────
    var allowedExts = ['pdf', 'doc', 'docx'];
    function setupFileInput(inputId, nameId, errId) {
        var inp = document.getElementById(inputId);
        var nameEl = document.getElementById(nameId);
        var errEl = document.getElementById(errId);
        if (!inp) return;
        inp.addEventListener('change', function() {
            if (!inp.files || !inp.files[0]) {
                if (nameEl) nameEl.textContent = 'Файл не выбран';
                return;
            }
            var file = inp.files[0];
            var ext = file.name.split('.').pop().toLowerCase();
            if (allowedExts.indexOf(ext) === -1) {
                if (errEl) { errEl.textContent = 'Недопустимый формат. Разрешены: PDF, DOC, DOCX'; errEl.style.display = 'block'; }
                if (nameEl) nameEl.textContent = '';
                inp.value = '';
            } else {
                if (errEl) errEl.style.display = 'none';
                if (nameEl) nameEl.textContent = '📎 ' + file.name;
            }
        });
    }
    setupFileInput('res_attachment', 'res-file-name', 'res-file-err');
    setupFileInput('vac_attachment', 'vac-file-name', 'vac-file-err');

    // ── Маска телефона ─────────────────────────────────────────────────────────
    function setupPhoneMask(inputId) {
        var inp = document.getElementById(inputId);
        if (!inp) return;
        inp.addEventListener('input', function() {
            var val = inp.value.replace(/[^\d+\-\s]/g, '');
            if (val.length > 18) val = val.slice(0, 18);
            inp.value = val;
        });
        inp.addEventListener('keypress', function(e) {
            if (!/[\d+\-\s]/.test(e.key) && !['Backspace','Delete','Tab','Enter','ArrowLeft','ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }
    setupPhoneMask('vac_phone');

    // ── Конвертация даты в DD.MM.ГГГГ перед submit ─────────────────────────────
    function dateToRu(val) {
        if (!val) return '';
        var parts = val.split('-');
        if (parts.length === 3) return parts[2] + '.' + parts[1] + '.' + parts[0];
        return val;
    }

    // ── Валидация формы резюме ─────────────────────────────────────────────────
    var formRes = document.getElementById('form-resume');
    if (formRes) {
        formRes.addEventListener('submit', function(e) {
            var errors = [];
            var dobInp   = document.getElementById('res_dob');
            var yearInp  = document.getElementById('res_year');
            var fileInp  = document.getElementById('res_attachment');
            var agreeInp = document.getElementById('res_agree_pd');
            var errorBox = document.getElementById('res-error-box');

            document.getElementById('res-dob-err').style.display  = 'none';
            document.getElementById('res-year-err').style.display = 'none';
            document.getElementById('res-file-err').style.display = 'none';
            document.getElementById('res-agree-err').style.display = 'none';

            if (!dobInp || !dobInp.value) {
                errors.push('Дата рождения');
                if (document.getElementById('res-dob-err')) document.getElementById('res-dob-err').style.display = 'block';
            } else {
                // Заполнить скрытое поле
                var hidden = document.getElementById('res_dob_hidden');
                if (hidden) hidden.value = dateToRu(dobInp.value);
            }
            if (!yearInp || !yearInp.value) {
                errors.push('Год выпуска');
                if (document.getElementById('res-year-err')) document.getElementById('res-year-err').style.display = 'block';
            }
            if (!fileInp || !fileInp.files || !fileInp.files[0]) {
                errors.push('Файл резюме');
                if (document.getElementById('res-file-err')) document.getElementById('res-file-err').style.display = 'block';
            } else {
                var ext = fileInp.files[0].name.split('.').pop().toLowerCase();
                if (allowedExts.indexOf(ext) === -1) {
                    errors.push('Формат файла резюме');
                    if (document.getElementById('res-file-err')) { document.getElementById('res-file-err').textContent = 'Недопустимый формат. Разрешены: PDF, DOC, DOCX'; document.getElementById('res-file-err').style.display = 'block'; }
                }
            }
            if (!agreeInp || !agreeInp.checked) {
                errors.push('Согласие с политикой ПДн');
                if (document.getElementById('res-agree-err')) document.getElementById('res-agree-err').style.display = 'block';
            }
            if (errors.length > 0) {
                e.preventDefault();
                if (errorBox) {
                    errorBox.textContent = 'Пожалуйста, заполните обязательные поля: ' + errors.join(', ') + '.';
                    errorBox.style.display = 'block';
                    errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            } else {
                // Заполнить скрытое поле датой
                var hidden2 = document.getElementById('res_dob_hidden');
                if (hidden2 && dobInp) hidden2.value = dateToRu(dobInp.value);
            }
        });
    }

    // ── Валидация формы вакансии ───────────────────────────────────────────────
    var formVac = document.getElementById('form-vacancy');
    if (formVac) {
        formVac.addEventListener('submit', function(e) {
            var errors = [];
            var fileInp  = document.getElementById('vac_attachment');
            var agreeInp = document.getElementById('vac_agree_pd');
            var errorBox = document.getElementById('vac-error-box');

            document.getElementById('vac-file-err').style.display = 'none';
            document.getElementById('vac-agree-err').style.display = 'none';

            var company = formVac.querySelector('[name="vac_company"]');
            var position = formVac.querySelector('[name="vac_position"]');
            if (!company || !company.value.trim()) errors.push('Компания');
            if (!position || !position.value.trim()) errors.push('Должность');

            if (!fileInp || !fileInp.files || !fileInp.files[0]) {
                errors.push('Файл вакансии');
                if (document.getElementById('vac-file-err')) document.getElementById('vac-file-err').style.display = 'block';
            } else {
                var ext = fileInp.files[0].name.split('.').pop().toLowerCase();
                if (allowedExts.indexOf(ext) === -1) {
                    errors.push('Формат файла вакансии');
                    if (document.getElementById('vac-file-err')) { document.getElementById('vac-file-err').textContent = 'Недопустимый формат. Разрешены: PDF, DOC, DOCX'; document.getElementById('vac-file-err').style.display = 'block'; }
                }
            }
            if (!agreeInp || !agreeInp.checked) {
                errors.push('Согласие с политикой ПДн');
                if (document.getElementById('vac-agree-err')) document.getElementById('vac-agree-err').style.display = 'block';
            }
            if (errors.length > 0) {
                e.preventDefault();
                if (errorBox) {
                    errorBox.textContent = 'Пожалуйста, заполните обязательные поля: ' + errors.join(', ') + '.';
                    errorBox.style.display = 'block';
                    errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        });
    }
})();
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
