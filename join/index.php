<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Вступить в общество");
$APPLICATION->SetPageProperty('description', 'Вступите в Политехническое общество выпускников МГТУ им. Н.Э. Баумана. Выберите тип членства: Базовое, Профессиональное, Партнёрское или Почётное.');

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

// ─── Почётный тариф — AJAX ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['honorary_action'])) {
    $hFio   = trim($_POST['honorary_fio']   ?? '');
    $hEmail = trim($_POST['honorary_email'] ?? '');
    $hPhone = trim($_POST['honorary_phone'] ?? '');
    $hMsg   = trim($_POST['honorary_msg']   ?? '');
    if ($hFio && $hEmail) {
        po_sendAdminEmail('honorary', ['fio' => $hFio, 'email' => $hEmail, 'phone' => $hPhone, 'msg' => $hMsg]);
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
    } else {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Заполните ФИО и email']);
    }
    exit;
}

// ─── #form-membership modal — AJAX ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['modal_membership_action'])) {
    $mType  = in_array($_POST['modal_type'] ?? '', ['basic','premium','partner','honorary']) ? $_POST['modal_type'] : 'basic';
    $mLname = trim($_POST['modal_lname'] ?? '');
    $mFname = trim($_POST['modal_fname'] ?? '');
    $mSname = trim($_POST['modal_sname'] ?? '');
    $mPhone = trim($_POST['modal_phone'] ?? '');
    $mEmail = trim($_POST['modal_email'] ?? '');
    $mDept  = trim($_POST['modal_dept']  ?? '');
    $mYear  = trim($_POST['modal_year']  ?? '');
    if (!$mLname || !$mFname || !$mEmail) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Заполните Фамилию, Имя и Email']);
        exit;
    }
    $typeLabels = ['basic' => 'Базовое', 'premium' => 'Профессиональное', 'partner' => 'Партнёрское', 'honorary' => 'Почётное'];
    if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
        $hlData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
        if ($hlData) {
            $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlData)->getDataClass();
            $hlClass::add([
                'UF_USER_ID'     => $USER->IsAuthorized() ? (int)$USER->GetID() : 0,
                'UF_TYPE'        => 'membership',
                'UF_STATUS'      => 'new',
                'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                'UF_DATA'        => json_encode([
                    'membership_type' => $mType,
                    'last_name'  => $mLname, 'first_name' => $mFname, 'second_name' => $mSname,
                    'email' => $mEmail, 'phone' => $mPhone, 'dept' => $mDept, 'year' => $mYear,
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
    po_sendAdminEmail('membership', [
        'membership_type' => $typeLabels[$mType] ?? $mType,
        'last_name'  => $mLname, 'first_name' => $mFname,
        'email' => $mEmail, 'phone' => $mPhone,
    ]);
    po_logAction('form_submit', 'application', 0, 'D1 modal membership ' . $mType);
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true]);
    exit;
}

// ─── D7: Индустриальное партнёрство ──────────────────────────────────────
$d7Done  = false;
$d7Error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d7_action'])) {
    $d7Company = trim($_POST['d7_company'] ?? '');
    $d7Contact = trim($_POST['d7_contact'] ?? '');
    $d7Site    = trim($_POST['d7_site']    ?? '');
    $d7Email   = trim($_POST['d7_email']   ?? '');
    $d7Phone   = trim($_POST['d7_phone']   ?? '');
    $d7Count   = trim($_POST['d7_count']   ?? '');
    $d7AgreePd = ($_POST['d7_agree_pd'] ?? '') === 'yes';
    if (!$d7Company || !$d7Contact || !$d7Email) {
        $d7Error = 'Заполните обязательные поля: Компания, ФИО, Email.';
    } elseif (!$d7AgreePd) {
        $d7Error = 'Необходимо согласие с политикой ПДн.';
    } else {
        $saved = false;
        if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
            $hlData = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
            if ($hlData) {
                $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlData)->getDataClass();
                $res = $hlClass::add([
                    'UF_USER_ID'     => $USER->IsAuthorized() ? (int)$USER->GetID() : 0,
                    'UF_TYPE'        => 'partnership',
                    'UF_STATUS'      => 'new',
                    'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                    'UF_DATA'        => json_encode([
                        'company' => $d7Company, 'contact_name' => $d7Contact,
                        'site' => $d7Site, 'email' => $d7Email,
                        'phone' => $d7Phone, 'planned_count' => $d7Count,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
                $saved = $res->isSuccess();
                if (!$saved) $d7Error = 'Ошибка сохранения. Попробуйте позже.';
            }
        } else {
            $saved = true;
        }
        if ($saved) {
            $d7Done = true;
            po_logAction('form_submit', 'application', 0, 'D7 industrial partnership');
            po_sendAdminEmail('partnership', [
                'company' => $d7Company, 'contact_name' => $d7Contact,
                'email' => $d7Email, 'phone' => $d7Phone, 'site' => $d7Site,
            ]);
        }
    }
}
$isAuthorized = $USER->IsAuthorized();
$arCurUser = [];
if ($isAuthorized) {
    $dbU = CUser::GetByID($USER->GetID());
    $arCurUser = $dbU->Fetch() ?: [];
}
?>

<main>
    <!-- ── culture section ── -->
    <section class="culture culture--subtitles">
        <div class="container">
            <h2 class="main-title culture__title">
                Новые резиденты принимают эстафету лидерства и продолжают традиции успеха
            </h2>
            <div class="culture__wrapper">
                <div class="culture__box">
                    <div class="culture__card">
                        <h3>Крупные предприятия и организации</h3>
                        <p>Внесшие значительный вклад в развитие Общества.</p>
                    </div>
                    <div class="culture__card">
                        <h3>Люди, оказавшие важные услуги</h3>
                        <p>Развитию технического образования в России.</p>
                    </div>
                    <div class="culture__card">
                        <h3>Учёные, прославившиеся трудами</h3>
                        <p>В технической литературе.</p>
                    </div>
                    <div class="culture__card">
                        <h3>Активные участники добровольческих проектов</h3>
                        <p>Общества социальной направленности</p>
                    </div>
                </div>
                <div class="culture__card culture__card--big culture__card--man">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/subscriptions-page/culture-bg-card.png" alt="" class="culture__card-image">
                    <div class="culture__card-overlay">
                        <h3>Выпускники МГТУ (МВТУ)</h3>
                        <p>и его филиалов</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── membership slider section ── -->
    <section class="membership">
        <div class="container">
            <h2 class="main-title membership__title">
                Новые резиденты укрепляют связи внутри сообщества и способствуют его развитию
            </h2>
            <div class="membership-slider swiper">
                <div class="swiper-wrapper">
                    <!-- Базовое -->
                    <div class="swiper-slide membership-slider__card">
                        <h3 class="membership-slider__title">Базовое</h3>
                        <p class="membership-slider__name">5 000 Р</p>
                        <p class="membership-slider__time">ежегодно</p>
                        <ul class="membership-slider__list">
                            <li class="membership-slider__item">Возможность размещения резюме на карьерной платформе Политехнического общества;</li>
                            <li class="membership-slider__item">Доступ в закрытый карьерный канал с вакансиями от профильных компаний;</li>
                            <li class="membership-slider__item">Участие в активностях, выставках и мероприятиях Политехнического общества;</li>
                            <li class="membership-slider__item">Доступ в электронную библиотеку МГТУ (в разработке);</li>
                            <li class="membership-slider__item">Доступ к витрине компетенций партнёров Политехнического общества, кафедр, студенческих конструкторских бюро и научно-образовательных центров МГТУ.</li>
                        </ul>
                        <button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership" data-plan="basic">Вступить</button>
                    </div>
                    <!-- Профессиональное -->
                    <div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
                        <h3 class="membership-slider__title">Профессиональное</h3>
                        <p class="membership-slider__name">50 000 Р</p>
                        <p class="membership-slider__time">ежегодно</p>
                        <button class="membership-slider__advantages">+ Возможности Базового</button>
                        <ul class="membership-slider__list">
                            <li class="membership-slider__item">Участие в закрытом чате членов общества уровня «Бизнес»;</li>
                            <li class="membership-slider__item">Размещение информации и новостей о компании на площадках Политехнического общества;</li>
                            <li class="membership-slider__item">Возможность предложить собственный проект для поиска спонсоров и поддержки Политехнического общества;</li>
                            <li class="membership-slider__item">Участие в бизнес-мероприятиях Политехнического общества в онлайн и очном форматах;</li>
                            <li class="membership-slider__item">Доступ к базе резюме выпускников на карьерной платформе Политехнического общества.</li>
                        </ul>
                        <button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership" data-plan="premium">Вступить</button>
                    </div>
                    <!-- Партнёрское -->
                    <div class="swiper-slide membership-slider__card membership-slider__card--honorary">
                        <h3 class="membership-slider__title">Партнёрское</h3>
                        <p class="membership-slider__name membership-slider__name--small">Индивидуальные условия</p>
                        <p class="membership-slider__time">обсуждается индивидуально</p>
                        <button class="membership-slider__advantages">+ Возможности профессионального</button>
                        <ul class="membership-slider__list">
                            <li class="membership-slider__item">Участие в закрытых мероприятиях Политехнического общества;</li>
                            <li class="membership-slider__item">Право стать членом правления Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана;</li>
                            <li class="membership-slider__item">Участие в закрытом чате почётных членов Политехнического общества.</li>
                        </ul>
                        <button type="button" class="membership-slider__join btn btn-empty" onclick="showPartnerForm()">Стать партнером</button>
                    </div>
                    <!-- Почётное -->
                    <div class="swiper-slide membership-slider__card membership-slider__card--gratuitous">
                        <h3 class="membership-slider__title">Почётное</h3>
                        <p class="membership-slider__name">Бесценно</p>
                        <p class="membership-slider__time">по результатам заполненной анкеты</p>
                        <button class="membership-slider__advantages">+ Возможности Базового</button>
                        <ul class="membership-slider__list">
                            <li class="membership-slider__item">Для тех, кто внёс значительный вклад в развитие технической науки, образования, технологий и деятельности Политехнического общества.</li>
                        </ul>
                        <button type="button" class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-honorary">Вступить</button>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- ── partner section ── -->
    <section class="partner">
        <div class="container">
            <div class="partner__wrapper">
                <div class="partner__info">
                    <h2 class="main-title partner__title">Индустриальное партнерство</h2>
                    <p class="main-text partner__text">Для юридических лиц</p>
                    <button class="btn partner__btn desk-block" onclick="showPartnerForm()">Стать партнером</button>
                </div>
                <div class="partner__discription">
                    <ul class="partner__list">
                        <li class="partner__item">Все преимущества базового и бизнес членства</li>
                        <li class="partner__item">Возможность состоять в индустриальном клубе Политехнического общества</li>
                        <li class="partner__item">Доступ к витрине компетенций, возможность разместить заказ/взять задачу</li>
                        <li class="partner__item">Рекламные возможности площадок и мероприятий Политехнического общества</li>
                    </ul>
                    <p class="partner__discription-text">Стоимость обсуждается индивидуально.</p>
                </div>
                <button class="btn partner__btn desk-none" onclick="showPartnerForm()">Стать партнером</button>
            </div>
        </div>
    </section>

    <!-- ── D7: форма партнёрства ── -->
    <section id="join-ur-block" style="display:none">
        <div class="container" style="padding-top:48px;padding-bottom:48px">
            <div class="join__wrapper">
                <?php if ($d7Done): ?>
                <div style="text-align:center;padding:40px 0">
                    <div style="font-size:48px;margin-bottom:12px">🤝</div>
                    <h2 class="account__title main-title">Заявка на партнёрство отправлена!</h2>
                    <p style="margin-top:12px;color:#666;max-width:480px;margin-left:auto;margin-right:auto">
                        Мы свяжемся с вами в течение 5 рабочих дней для обсуждения условий партнёрства.
                    </p>
                    <a href="/" class="btn" style="margin-top:20px">На главную</a>
                </div>
                <?php else: ?>
                <h2 class="account__title main-title">Индустриальное партнёрство</h2>
                <p style="margin-bottom:24px;color:#666">Для компаний, НИИ и организаций. После отправки заявки мы свяжемся с вами в течение 5 рабочих дней.</p>
                <?php if ($d7Error): ?>
                <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                    <p><?= htmlspecialchars($d7Error) ?></p>
                </div>
                <?php endif; ?>
                <form method="POST" action="/join/#join-ur-block">
                    <input type="hidden" name="d7_action" value="1">
                    <div class="account__personal">
                        <div class="account__chapter"><h3 class="account__subtitle">Данные компании</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="d7_company" placeholder="Название компании *" required
                                   value="<?= htmlspecialchars($_POST['d7_company'] ?? '') ?>">
                            <input type="url"  name="d7_site"    placeholder="Сайт компании"
                                   value="<?= htmlspecialchars($_POST['d7_site'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Контакты представителя</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text"   name="d7_contact" placeholder="ФИО представителя *" required
                                   value="<?= htmlspecialchars($_POST['d7_contact'] ?? '') ?>">
                            <input type="email"  name="d7_email"   placeholder="Email *" required
                                   value="<?= htmlspecialchars($_POST['d7_email'] ?? ($arCurUser['EMAIL'] ?? '')) ?>">
                            <input type="tel"    name="d7_phone"   placeholder="Телефон"
                                   value="<?= htmlspecialchars($_POST['d7_phone'] ?? '') ?>">
                            <input type="number" name="d7_count"   placeholder="Планируемое кол-во представителей *" min="1" required
                                   value="<?= htmlspecialchars($_POST['d7_count'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <p class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="d7_agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="d7_agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">Отправить заявку на партнёрство</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<!-- ── Модаль: Почётный тариф ── -->
<div id="form-honorary" style="display:none;max-width:480px;padding:32px 24px">
    <h3 style="margin-bottom:12px;font-size:22px;font-weight:700">Почётное членство</h3>
    <p style="color:#666;margin-bottom:24px;font-size:14px;line-height:1.6">
        Почётное членство присваивается за особые заслуги перед Политехническим обществом. Оставьте заявку — мы свяжемся с вами.
    </p>
    <div id="honorary-fields">
        <p id="honorary-error" style="display:none;color:#c0392b;margin-bottom:12px;font-size:13px"></p>
        <input type="text"  id="honorary-fio"   placeholder="Фамилия Имя Отчество" style="display:block;width:100%;margin-bottom:12px;box-sizing:border-box">
        <input type="email" id="honorary-email" placeholder="Электропочта" style="display:block;width:100%;margin-bottom:12px;box-sizing:border-box">
        <input type="tel"   id="honorary-phone" placeholder="Телефон" style="display:block;width:100%;margin-bottom:12px;box-sizing:border-box">
        <textarea id="honorary-msg" placeholder="Дополнительно (по желанию)" style="display:block;width:100%;height:80px;margin-bottom:20px;resize:vertical;box-sizing:border-box"></textarea>
        <button id="honorary-submit" class="btn" style="width:100%">Отправить заявку</button>
    </div>
    <div id="honorary-success" style="display:none;text-align:center;padding:24px 0">
        <div style="font-size:48px;margin-bottom:12px">✅</div>
        <p style="font-size:16px;font-weight:600">Заявка отправлена!</p>
        <p style="font-size:14px;color:#666;margin-top:8px">Мы свяжемся с вами в ближайшее время.</p>
    </div>
</div>

<script>
// Показать форму партнёрства
function showPartnerForm() {
    var block = document.getElementById('join-ur-block');
    if (block) {
        block.style.display = '';
        block.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Установить тип тарифа перед открытием #form-membership
document.querySelectorAll('[data-fancybox][data-src="#form-membership"][data-plan]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var plan = this.getAttribute('data-plan');
        var field = document.getElementById('modal-membership-type');
        if (field) field.value = plan;
    });
});

// AJAX-отправка #form-membership
(function() {
    var submitBtn = document.getElementById('modal-membership-submit');
    if (!submitBtn) return;
    submitBtn.addEventListener('click', function() {
        var type   = (document.getElementById('modal-membership-type')  || {}).value || 'basic';
        var lname  = (document.getElementById('modal-membership-lname') || {}).value || '';
        var fname  = (document.getElementById('modal-membership-fname') || {}).value || '';
        var sname  = (document.getElementById('modal-membership-sname') || {}).value || '';
        var phone  = (document.getElementById('modal-membership-phone') || {}).value || '';
        var email  = (document.getElementById('modal-membership-email') || {}).value || '';
        var dept   = (document.getElementById('modal-membership-dept')  || {}).value || '';
        var year   = (document.getElementById('modal-membership-year')  || {}).value || '';
        var errEl  = document.getElementById('modal-membership-error');
        if (errEl) errEl.style.display = 'none';
        if (!lname.trim() || !fname.trim() || !email.trim()) {
            if (errEl) { errEl.textContent = 'Заполните Фамилию, Имя и Email'; errEl.style.display = ''; }
            return;
        }
        submitBtn.disabled = true;
        var fd = new FormData();
        fd.append('modal_membership_action', '1');
        fd.append('modal_type',  type);
        fd.append('modal_lname', lname.trim());
        fd.append('modal_fname', fname.trim());
        fd.append('modal_sname', sname.trim());
        fd.append('modal_phone', phone.trim());
        fd.append('modal_email', email.trim());
        fd.append('modal_dept',  dept.trim());
        fd.append('modal_year',  year.trim());
        fetch('/join/', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var form = document.getElementById('modal-membership-form');
                    var ok   = document.getElementById('modal-membership-ok');
                    if (form) form.style.display = 'none';
                    if (ok)   ok.style.display   = 'block';
                } else {
                    if (errEl) { errEl.textContent = data.message || 'Ошибка. Попробуйте снова.'; errEl.style.display = ''; }
                    submitBtn.disabled = false;
                }
            })
            .catch(function() {
                if (errEl) { errEl.textContent = 'Ошибка соединения.'; errEl.style.display = ''; }
                submitBtn.disabled = false;
            });
    });
})();

// Почётный тариф — AJAX
(function() {
    var btn = document.getElementById('honorary-submit');
    if (!btn) return;
    btn.addEventListener('click', function() {
        var fio   = (document.getElementById('honorary-fio')   || {}).value || '';
        var email = (document.getElementById('honorary-email') || {}).value || '';
        var phone = (document.getElementById('honorary-phone') || {}).value || '';
        var msg   = (document.getElementById('honorary-msg')   || {}).value || '';
        var errEl = document.getElementById('honorary-error');
        errEl.style.display = 'none';
        if (!fio.trim() || !email.trim()) { errEl.textContent = 'Заполните ФИО и email'; errEl.style.display = ''; return; }
        btn.disabled = true;
        var fd = new FormData();
        fd.append('honorary_action', '1');
        fd.append('honorary_fio',   fio.trim());
        fd.append('honorary_email', email.trim());
        fd.append('honorary_phone', phone.trim());
        fd.append('honorary_msg',   msg.trim());
        fetch('/join/', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var fields  = document.getElementById('honorary-fields');
                    var success = document.getElementById('honorary-success');
                    if (fields)  fields.style.display  = 'none';
                    if (success) success.style.display = 'block';
                } else {
                    errEl.textContent = data.message || 'Ошибка. Попробуйте снова.';
                    errEl.style.display = '';
                    btn.disabled = false;
                }
            })
            .catch(function() {
                errEl.textContent = 'Ошибка соединения.'; errEl.style.display = ''; btn.disabled = false;
            });
    });
})();

// Показать D7-блок если POST вернул ошибку/успех
<?php if ($d7Done || $d7Error): ?>
showPartnerForm();
<?php endif; ?>
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
