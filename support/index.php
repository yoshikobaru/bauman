<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поддержать");

use Bitrix\Main\Loader;
$hlOk     = Loader::includeModule('highloadblock');
$iblockOk = Loader::includeModule('iblock');

$d2Done  = false;
$d2Error = '';

// D2: Поддержка проектов — без оплаты, запись в HL-блок
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d2_action'])) {
    $amount    = trim($_POST['amount']     ?? '');
    $project   = trim($_POST['project']   ?? '');
    $frequency = in_array($_POST['frequency'] ?? '', ['month', 'once']) ? $_POST['frequency'] : 'once';
    $donorType = trim($_POST['donor_type'] ?? 'fiz');
    $fn        = trim($_POST['first_name'] ?? '');
    $ln        = trim($_POST['last_name']  ?? '');
    $email     = trim($_POST['email']      ?? '');
    $phone     = trim($_POST['phone']      ?? '');
    $company   = trim($_POST['company']    ?? '');
    $site      = trim($_POST['site']       ?? '');
    $agreeDoc  = ($_POST['agree_doc']      ?? '') === 'yes';
    $agreePd   = ($_POST['agree_pd']       ?? '') === 'yes';

    if (!$fn || !$email) {
        $d2Error = 'Заполните обязательные поля: Имя, e-mail.';
    } elseif (!$agreeDoc || !$agreePd) {
        $d2Error = 'Необходимо согласие с Уставом и политикой ПДн.';
    } else {
        $saved = false;
        if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
            $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
            if ($hlEntity) {
                $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                $res = $hlClass::add([
                    'UF_USER_ID'     => $USER->IsAuthorized() ? (int)$USER->GetID() : 0,
                    'UF_TYPE'        => 'project_support',
                    'UF_STATUS'      => 'new',
                    'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                    'UF_DATA'        => json_encode([
                        'amount'     => $amount,    'project'    => $project,
                        'frequency'  => $frequency, 'donor_type' => $donorType,
                        'first_name' => $fn,        'last_name'  => $ln,
                        'email'      => $email,     'phone'      => $phone,
                        'company'    => $company,   'site'       => $site,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
                if ($res->isSuccess()) $saved = true;
                else $d2Error = 'Ошибка сохранения. Попробуйте позже.';
            }
        } else {
            $saved = true;
        }
        if ($saved) {
            $d2Done = true;
            po_logAction('form_submit', 'application', 0, 'D2 поддержка проекта: ' . $project . ', ' . $amount);
            $d2Data = [
                'first_name' => $fn,    'last_name'  => $ln,
                'email'      => $email, 'phone'      => $phone,
                'project'    => $project, 'amount'   => $amount,
                'donor_type' => $donorType, 'company' => $company,
            ];
            po_sendAdminEmail('project_support', $d2Data);
            po_createCrmLead('project_support', $d2Data);
        }
    }
}

// Список проектов для выпадающего списка
$arProjects = [];
if ($iblockOk && defined('IBLOCK_PROJECTS_ID') && IBLOCK_PROJECTS_ID > 0) {
    $dbProj = CIBlockElement::GetList(
        ['SORT' => 'ASC'],
        ['IBLOCK_ID' => IBLOCK_PROJECTS_ID, 'ACTIVE' => 'Y'],
        false, false, ['ID', 'NAME']
    );
    while ($row = $dbProj->GetNext()) {
        $arProjects[] = $row;
    }
}

// Предзаполнение данных для авторизованных
$prefill = [
    'first_name' => $USER->IsAuthorized() ? $USER->GetParam('NAME')      : '',
    'last_name'  => $USER->IsAuthorized() ? $USER->GetParam('LAST_NAME') : '',
    'email'      => $USER->IsAuthorized() ? $USER->GetParam('EMAIL')     : '',
];
?>

<main>
    <!-- D2: Форма поддержки проекта -->
    <section class="project-programm" style="padding-top:140px;">
        <div class="container">
            <div class="project-programm__wrapper">
                <div class="project-programm__preview">
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-donate-img.png" alt="" class="project-programm__preview-image">
                    <h2 class="project-programm__preview-title">
                        Даже небольшое регулярное пожертвование поможет нашей работе
                    </h2>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-donate-img-ellips.png" alt="" class="project-programm__preview-ellips">
                </div>
                <div class="project-programm__donate">
                    <?php if ($d2Done): ?>
                    <div class="authorization__alert authorization__alert--success" style="margin:24px 0;padding:24px">
                        <h3>Заявка принята!</h3>
                        <p style="margin-top:8px">Спасибо за поддержку. Мы свяжемся с вами в ближайшее время.</p>
                        <a href="/projects/" class="btn" style="margin-top:16px">К проектам</a>
                    </div>
                    <?php else: ?>
                    <?php if ($d2Error): ?>
                    <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                        <p><?= htmlspecialchars($d2Error) ?></p>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="/support/" id="form-d2">
                        <input type="hidden" name="d2_action"  value="1">
                        <input type="hidden" name="amount"     id="d2_amount"     value="">
                        <input type="hidden" name="project"    id="d2_project"    value="">
                        <input type="hidden" name="donor_type" id="d2_donor_type" value="fiz">
                        <input type="hidden" name="frequency"  id="d2_frequency"  value="month">

                        <style>
                            .project-programm__item-selector > div,
                            .project-programm__item-price > div {
                                cursor: pointer;
                                user-select: none;
                                -webkit-user-select: none;
                            }
                            .project-programm__item-price > div:hover { opacity: .85; }
                            .d2-custom-row {
                                display: flex;
                                flex-direction: column;
                                gap: 6px;
                                align-items: flex-start;
                                padding: 10px 14px !important;
                                min-height: unset !important;
                            }
                            .d2-custom-row span { font-size: 13px; pointer-events: none; }
                            .d2-custom-row input {
                                width: 100%;
                                border: none;
                                border-bottom: 1px solid currentColor;
                                background: transparent;
                                font-size: 15px;
                                font-weight: 600;
                                outline: none;
                                padding: 2px 0;
                            }
                        </style>

                        <div class="project-programm__tabs">
                            <ul class="project-programm__navs">
                                <li class="main-tabs-click main-tabs-click--active" data-tab="summ">Сумма</li>
                                <li class="main-tabs-click" data-tab="programm">Программы</li>
                                <li class="main-tabs-click" data-tab="data">Данные</li>
                                <li class="main-tabs-click" data-tab="pay">Оплата</li>
                            </ul>
                        </div>
                        <div class="project-programm__content">

                            <!-- Шаг 1: Сумма -->
                            <div class="project-programm__item main-tabs-pane main-tabs-pane--active" data-tab="summ">
                                <div class="project-programm__item-selector">
                                    <div class="active" data-period="month">Ежемесячное</div>
                                    <div data-period="once">Разовое</div>
                                </div>
                                <div class="project-programm__item-price" id="d2_price_list">
                                    <div class="active" data-val="300">300 Р</div>
                                    <div data-val="500">500 Р</div>
                                    <div data-val="1000">1000 Р</div>
                                    <div data-val="3000">3000 Р</div>
                                    <div data-val="5000">5000 Р</div>
                                    <div data-val="10000">10 000 Р</div>
                                    <div data-val="30000">30 000 Р</div>
                                    <div data-val="custom" class="d2-custom-row">
                                        <span>Другая сумма</span>
                                        <input type="number" id="d2_custom_amount" placeholder="Введите сумму, руб." min="1" onclick="event.stopPropagation()">
                                    </div>
                                </div>
                                <div class="project-programm__buttons">
                                    <button type="button" class="btn project-programm__btn" id="d2_next_summ">Продолжить</button>
                                </div>
                            </div>

                            <!-- Шаг 2: Проект -->
                            <div class="project-programm__item main-tabs-pane" data-tab="programm">
                                <div class="project-programm__all">
                                    <select id="d2_project_select" name="project">
                                        <option value="">— Выберите программу —</option>
                                        <option value="Пожертвование на ведение уставной деятельности">Пожертвование на ведение уставной деятельности</option>
                                        <?php if (!empty($arProjects)): ?>
                                            <?php foreach ($arProjects as $projectItem): ?>
                                                <option value="<?= htmlspecialchars($projectItem['NAME']) ?>"><?= htmlspecialchars($projectItem['NAME']) ?></option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="Реставрация Ротонды">Реставрация Ротонды</option>
                                            <option value="Конференция PolytechExpo">Конференция PolytechExpo</option>
                                            <option value="Конференция Встреча выпускников">Конференция Встреча выпускников</option>
                                            <option value="Попечительский совет МТ4">Попечительский совет МТ4</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="project-programm__buttons">
                                    <button type="button" class="btn project-programm__btn project-programm__btn--back" id="d2_back_prog">Назад</button>
                                    <button type="button" class="btn project-programm__btn" id="d2_next_prog">Продолжить</button>
                                </div>
                            </div>

                            <!-- Шаг 3: Данные -->
                            <div class="project-programm__item main-tabs-pane" data-tab="data">
                                <div class="project-programm__item-selector">
                                    <div class="main-tabs-click-project main-tabs-click-project--active" data-tab="fiz" data-donor="fiz">Физ. лицо</div>
                                    <div class="main-tabs-click-project" data-tab="your" data-donor="ur">Юр. лицо</div>
                                </div>

                                <!-- Физ. лицо -->
                                <div class="main-tabs-pane-project main-tabs-pane-project--active" data-tab="fiz">
                                    <div class="account__personal">
                                        <div class="account__chapter">
                                            <h3 class="account__subtitle">Личные данные</h3>
                                        </div>
                                        <div class="account__personal-list account__grid--tripl">
                                            <input type="text"  name="last_name"  placeholder="Фамилия"
                                                   value="<?= htmlspecialchars($prefill['last_name']) ?>">
                                            <input type="text"  name="first_name" placeholder="Имя *" required
                                                   value="<?= htmlspecialchars($prefill['first_name']) ?>">
                                            <input type="email" name="email"      placeholder="e-mail *" required
                                                   value="<?= htmlspecialchars($prefill['email']) ?>">
                                            <input type="tel"   name="phone"      placeholder="Номер телефона">
                                        </div>
                                    </div>
                                    <div class="join__politic">
                                        <div class="join__politic-question">
                                            <p class="join__politic-link">Ознакомлен с <a href="<?= defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#' ?>" target="_blank">Уставом</a> и <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
                                            <div class="account__graduate-choice">
                                                <label class="account__graduate-item">
                                                    <input type="radio" name="agree_doc" value="yes" class="account__graduate-input">
                                                    <span class="account__graduate-box"></span>Да
                                                </label>
                                                <label class="account__graduate-item">
                                                    <input type="radio" name="agree_doc" value="no" class="account__graduate-input">
                                                    <span class="account__graduate-box"></span>Нет
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Юр. лицо -->
                                <div class="main-tabs-pane-project" data-tab="your">
                                    <div class="account__personal">
                                        <div class="account__chapter">
                                            <h3 class="account__subtitle">Данные представителя</h3>
                                        </div>
                                        <div class="account__personal-list account__personal-list--project">
                                            <input type="text"  name="last_name"  placeholder="Фамилия">
                                            <input type="text"  name="first_name" placeholder="Имя *" required>
                                            <input type="email" name="email"      placeholder="e-mail *" required>
                                            <input type="tel"   name="phone"      placeholder="Номер телефона">
                                            <input type="text"  name="company"    placeholder="Компания">
                                            <input type="text"  name="site"       placeholder="Сайт">
                                        </div>
                                    </div>
                                    <div class="join__politic">
                                        <div class="join__politic-question">
                                            <p class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
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
                                </div>

                                <div class="project-programm__buttons">
                                    <button type="button" class="btn project-programm__btn project-programm__btn--back" id="d2_back_data">Назад</button>
                                    <button type="button" class="btn project-programm__btn" id="d2_next_data">Продолжить</button>
                                </div>
                            </div>

                            <!-- Шаг 4: Оплата -->
                            <div class="project-programm__item main-tabs-pane" data-tab="pay">
                                <div style="padding:24px 0 8px;text-align:center">
                                    <div style="font-size:40px;margin-bottom:12px">💳</div>
                                    <p style="font-size:15px;color:#555;line-height:1.6;margin-bottom:20px">
                                        Онлайн-оплата будет доступна после подключения эквайринга.<br>
                                        Чтобы поддержать проект сейчас — оставьте заявку и мы свяжемся с вами.
                                    </p>
                                </div>
                                <div class="project-programm__buttons">
                                    <button type="button" class="btn project-programm__btn project-programm__btn--back" id="d2_back_pay">Назад</button>
                                    <button type="submit" class="btn project-programm__btn">Отправить заявку</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
// Авто-выбор проекта/фонда из URL (?project=...)
(function() {
    var urlProject = new URLSearchParams(location.search).get('project');
    if (urlProject) {
        var pf = document.getElementById('d2_project');
        if (pf) pf.value = urlProject;
        var sel = document.getElementById('d2_project_select');
        if (sel) {
            for (var i = 0; i < sel.options.length; i++) {
                if (sel.options[i].text === urlProject || sel.options[i].value === urlProject) {
                    sel.selectedIndex = i;
                    break;
                }
            }
        }
        // Сразу переключаем на шаг «Программы»
        var prog = document.querySelector('.main-tabs-click[data-tab="programm"]');
        if (prog) prog.click();
    }
})();

// D2 multi-step form logic
(function() {
    var priceList    = document.getElementById('d2_price_list');
    var amountField  = document.getElementById('d2_amount');
    var projectField = document.getElementById('d2_project');
    var donorField   = document.getElementById('d2_donor_type');
    var customInput  = document.getElementById('d2_custom_amount');
    var freqField    = document.getElementById('d2_frequency');

    if (!priceList) return;

    // Period selector (Ежемесячное / Разовое)
    document.querySelectorAll('[data-period]').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('[data-period]').forEach(function(e) { e.classList.remove('active'); });
            el.classList.add('active');
            if (freqField) freqField.value = el.getAttribute('data-period') === 'once' ? 'once' : 'month';
        });
    });

    // Select price
    priceList.querySelectorAll('[data-val]').forEach(function(el) {
        el.addEventListener('click', function() {
            priceList.querySelectorAll('[data-val]').forEach(function(e) { e.classList.remove('active'); });
            el.classList.add('active');
            var val = el.getAttribute('data-val');
            if (val === 'custom') {
                amountField.value = (customInput ? customInput.value : '') + ' руб. (другая)';
                if (customInput) customInput.addEventListener('input', function() {
                    amountField.value = customInput.value + ' руб.';
                });
            } else {
                amountField.value = val + ' руб.';
            }
        });
    });
    // Init default
    amountField.value = '300 руб.';

    // Next: summ → programm
    var btnNextSumm = document.getElementById('d2_next_summ');
    if (btnNextSumm) btnNextSumm.addEventListener('click', function() {
        switchTab('programm');
    });

    // Back: programm → summ
    var btnBackProg = document.getElementById('d2_back_prog');
    if (btnBackProg) btnBackProg.addEventListener('click', function() { switchTab('summ'); });

    // Next: programm → data
    var btnNextProg = document.getElementById('d2_next_prog');
    if (btnNextProg) btnNextProg.addEventListener('click', function() {
        var sel = document.getElementById('d2_project_select');
        if (sel) projectField.value = sel.value || 'Общий фонд';
        switchTab('data');
    });

    // Back: data → programm
    var btnBackData = document.getElementById('d2_back_data');
    if (btnBackData) btnBackData.addEventListener('click', function() { switchTab('programm'); });

    // Next: data → pay
    var btnNextData = document.getElementById('d2_next_data');
    if (btnNextData) btnNextData.addEventListener('click', function() { switchTab('pay'); });

    // Back: pay → data
    var btnBackPay = document.getElementById('d2_back_pay');
    if (btnBackPay) btnBackPay.addEventListener('click', function() { switchTab('data'); });

    // Donor type toggle
    document.querySelectorAll('[data-donor]').forEach(function(el) {
        el.addEventListener('click', function() {
            donorField.value = el.getAttribute('data-donor');
        });
    });

    function switchTab(tab) {
        document.querySelectorAll('.project-programm__tabs .main-tabs-click').forEach(function(li) {
            li.classList.toggle('main-tabs-click--active', li.getAttribute('data-tab') === tab);
        });
        document.querySelectorAll('#form-d2 .project-programm__item').forEach(function(pane) {
            pane.classList.toggle('main-tabs-pane--active', pane.getAttribute('data-tab') === tab);
        });
    }
})();
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
