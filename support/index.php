<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поддержать");

use Bitrix\Main\Loader;
$hlOk     = Loader::includeModule('highloadblock');
$iblockOk = Loader::includeModule('iblock');

$d2Done  = false;
$d2Error = '';
$payResult = in_array((string)($_GET['pay'] ?? ''), ['success', 'fail'], true) ? (string)$_GET['pay'] : '';
$paykeeperConfig = function_exists('po_get_paykeeper_config') ? po_get_paykeeper_config() : [];
$paykeeperReady = function_exists('po_is_paykeeper_configured') ? po_is_paykeeper_configured($paykeeperConfig) : false;
$d2Flash = function_exists('po_flash_get') ? po_flash_get('d2_support') : null;
if (is_array($d2Flash)) {
    $d2Done = !empty($d2Flash['done']);
    $d2Error = (string)($d2Flash['error'] ?? '');
    if (!empty($d2Flash['form']) && is_array($d2Flash['form'])) {
        $_POST = array_merge($_POST, $d2Flash['form']);
    }
}

// D2: Поддержка проектов — инициализация оплаты через PayKeeper
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d2_action'])) {
    $amount    = trim($_POST['amount']     ?? '');
    $project   = trim($_POST['project']   ?? '');
    $frequency = in_array($_POST['frequency'] ?? '', ['month', 'once']) ? $_POST['frequency'] : 'once';
    $donorType = trim($_POST['donor_type'] ?? 'fiz');
    if ($donorType === 'ur') {
        $fn      = trim($_POST['ur_first_name'] ?? '');
        $ln      = trim($_POST['ur_last_name']  ?? '');
        $email   = trim($_POST['ur_email']      ?? '');
        $phone   = trim($_POST['ur_phone']      ?? '');
        $company = trim($_POST['ur_company']    ?? '');
        $site    = trim($_POST['ur_site']       ?? '');
    } else {
        $fn      = trim($_POST['first_name'] ?? '');
        $ln      = trim($_POST['last_name']  ?? '');
        $email   = trim($_POST['email']      ?? '');
        $phone   = trim($_POST['phone']      ?? '');
        $company = '';
        $site    = '';
    }
    $comment   = trim($_POST['payment_comment'] ?? '');
    $agreePd   = ($_POST['agree_pd']       ?? '') === 'yes';

    if (!$project) {
        $d2Error = 'Выберите проект.';
    } elseif (!$ln || !$fn || !$email || !$phone) {
        $d2Error = 'Заполните обязательные поля: Фамилия, Имя, e-mail, Номер телефона.';
    } elseif (!po_is_valid_phone_chars($phone)) {
        $d2Error = 'Телефон может содержать только цифры, пробел, + и -.';
    } elseif ($donorType === 'ur' && (!$company || !$site)) {
        $d2Error = 'Для юр. лица заполните обязательные поля: Компания, Сайт.';
    } elseif ($donorType === 'fiz' && !$comment) {
        $d2Error = 'Заполните обязательное поле: Комментарий к платежу.';
    } elseif (!$agreePd) {
        $d2Error = 'Необходимо согласие с политикой обработки ПДн.';
    } else {
        $saved = false;
        $applicationId = 0;
        $hlClass = null;
        $paymentMeta = [
            'status' => 'new',
        ];
        $d2Data = [
            'first_name' => $fn,
            'last_name'  => $ln,
            'email'      => $email,
            'phone'      => $phone,
            'project'    => $project,
            'amount'     => $amount,
            'frequency'  => $frequency,
            'donor_type' => $donorType,
            'company'    => $company,
            'site'       => $site,
            'payment_comment' => $comment,
            'agree_pd'   => $agreePd ? 'yes' : 'no',
            'payment'    => $paymentMeta,
        ];

        if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
            $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
            if ($hlEntity) {
                $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                $res = $hlClass::add([
                    'UF_USER_ID'     => $USER->IsAuthorized() ? (int)$USER->GetID() : 0,
                    'UF_TYPE'        => 'project_support',
                    'UF_STATUS'      => 'new',
                    'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                    'UF_DATA'        => json_encode($d2Data, JSON_UNESCAPED_UNICODE),
                ]);
                if ($res->isSuccess()) {
                    $saved = true;
                    $applicationId = (int)$res->getId();
                } else {
                    $d2Error = 'Ошибка сохранения. Попробуйте позже.';
                }
            }
        } else {
            $saved = true;
        }

        if ($saved) {
            if (!$paykeeperReady) {
                $d2Error = 'Онлайн-оплата временно недоступна. Попробуйте позже.';
            } else {
                $payAmount = function_exists('po_paykeeper_normalize_amount')
                    ? po_paykeeper_normalize_amount($amount)
                    : null;
                if ($payAmount === null) {
                    $d2Error = 'Укажите корректную сумму пожертвования.';
                } elseif ($applicationId <= 0 || $hlClass === null) {
                    $d2Error = 'Не удалось подготовить заявку к оплате. Попробуйте позже.';
                } else {
                    $serviceName = $project === 'Пожертвование на ведение уставной деятельности'
                        ? $project
                        : ('Пожертвование на проект ' . $project);
                    $clientId = trim($ln . ' ' . $fn);
                    $orderId = function_exists('po_paykeeper_build_support_order_id')
                        ? po_paykeeper_build_support_order_id($applicationId)
                        : ('SUPPORT-' . $applicationId . '-' . date('YmdHis'));
                    $paymentRequest = [
                        'pay_amount' => $payAmount,
                        'clientid' => $clientId,
                        'orderid' => $orderId,
                        'service_name' => $serviceName,
                        'client_email' => $email,
                        'client_phone' => $phone,
                    ];

                    $apiError = '';
                    $invoice = function_exists('po_paykeeper_create_invoice')
                        ? po_paykeeper_create_invoice($paykeeperConfig, $paymentRequest, $apiError)
                        : null;

                    if (!$invoice || empty($invoice['invoice_url'])) {
                        $d2Error = 'Не удалось инициализировать оплату. Попробуйте позже.';
                        if ($apiError !== '') {
                            po_logAction('form_submit', 'application', $applicationId, 'PayKeeper init error: ' . $apiError);
                        }
                    } else {
                        $d2Data['payment'] = [
                            'status' => 'pending',
                            'order_id' => $orderId,
                            'invoice_id' => (string)$invoice['invoice_id'],
                            'invoice_url' => (string)$invoice['invoice_url'],
                            'amount_rub' => $payAmount,
                            'created_at' => date('c'),
                        ];
                        $hlClass::update($applicationId, [
                            'UF_DATA' => json_encode($d2Data, JSON_UNESCAPED_UNICODE),
                        ]);

                        po_logAction('form_submit', 'application', $applicationId, 'D2 PayKeeper init: ' . $orderId . ', ' . $payAmount);
                        po_sendAdminEmail('project_support', $d2Data);
                        po_createCrmLead('project_support', $d2Data);

                        LocalRedirect((string)$invoice['invoice_url']);
                        exit;
                    }
                }
            }
        }
    }

    if (function_exists('po_flash_set')) {
        po_flash_set('d2_support', [
            'done' => $d2Done,
            'error' => $d2Error,
            'form' => $_POST,
        ]);
    }
    LocalRedirect('/support/?d2=error');
    exit;
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
                    <?php if ($payResult === 'success'): ?>
                    <div class="authorization__alert authorization__alert--success" style="margin:0 0 16px;padding:18px 20px">
                        <h3 style="margin-bottom:8px">Спасибо! Оплата успешно завершена.</h3>
                        <p>Платеж обрабатывается автоматически. Если статус не обновился сразу, это произойдет в течение минуты.</p>
                    </div>
                    <?php elseif ($payResult === 'fail'): ?>
                    <div class="authorization__alert authorization__alert--error" style="margin:0 0 16px;padding:18px 20px">
                        <h3 style="margin-bottom:8px">Оплата не завершена.</h3>
                        <p>Проверьте данные карты или попробуйте еще раз.</p>
                    </div>
                    <?php endif; ?>
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
                                font-size: 26px;
                                font-weight: 600;
                                outline: none;
                                padding: 2px 0;
                            }
                            .d2-pay-summary {
                                margin: 0 auto;
                                max-width: 420px;
                                text-align: left;
                                background: #f8f8f8;
                                border-radius: 12px;
                                padding: 14px 18px;
                            }
                            .d2-pay-summary p {
                                margin: 0 0 8px;
                                font-size: 14px;
                                color: #333;
                            }
                            .d2-pay-summary p:last-child { margin-bottom: 0; }
                        </style>

                        <div class="project-programm__tabs">
                            <ul class="project-programm__navs">
                                <li class="main-tabs-click main-tabs-click--active" data-tab="summ">Сумма</li>
                                <li class="main-tabs-click" data-tab="programm">Проекты</li>
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
                                    <p style="margin-bottom:10px;color:#666;">Просим выбрать проект</p>
                                    <select id="d2_project_select" name="project">
                                        <option value="">— Выберите проект —</option>
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
                                            <input type="text"  name="last_name"  placeholder="Фамилия *" required
                                                   value="<?= htmlspecialchars($prefill['last_name']) ?>">
                                            <input type="text"  name="first_name" placeholder="Имя *" required
                                                   value="<?= htmlspecialchars($prefill['first_name']) ?>">
                                            <input type="email" name="email"      placeholder="e-mail *" required
                                                   value="<?= htmlspecialchars($prefill['email']) ?>">
                                            <input type="tel"   name="phone"      placeholder="Номер телефона *" required>
                                            <input type="text"  name="payment_comment" id="d2_payment_comment" placeholder="Комментарий к платежу *" required>
                                        </div>
                                    </div>
                                    <div class="join__politic">
                                        <div class="join__politic-question">
                                            <p class="join__politic-link">Ознакомлен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
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

                                <!-- Юр. лицо -->
                                <div class="main-tabs-pane-project" data-tab="your">
                                    <div class="account__personal">
                                        <div class="account__chapter">
                                            <h3 class="account__subtitle">Данные представителя</h3>
                                        </div>
                                        <div class="account__personal-list account__personal-list--project">
                                            <input type="text"  name="ur_last_name"  placeholder="Фамилия *" required>
                                            <input type="text"  name="ur_first_name" placeholder="Имя *" required>
                                            <input type="email" name="ur_email"      placeholder="e-mail *" required>
                                            <input type="tel"   name="ur_phone"      placeholder="Номер телефона *" required>
                                            <input type="text"  name="ur_company"    placeholder="Компания *" required>
                                            <input type="text"  name="ur_site"       placeholder="Сайт *" required>
                                        </div>
                                    </div>
                                    <div class="join__politic">
                                        <div class="join__politic-question">
                                            <p class="join__politic-link">Ознакомлен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
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
                                        Проверьте данные платежа и нажмите кнопку оплаты.
                                    </p>
                                    <div class="d2-pay-summary">
                                        <p><strong>Сумма:</strong> <span id="d2_pay_amount_text">300 руб.</span></p>
                                        <p><strong>Проект:</strong> <span id="d2_pay_project_text">не выбран</span></p>
                                        <p><strong>Тип:</strong> <span id="d2_pay_donor_text">Физ. лицо</span></p>
                                    </div>
                                    <?php if (!$paykeeperReady): ?>
                                    <p style="font-size:14px;color:#b42318;line-height:1.5;margin:18px 0 0">
                                        Эквайринг еще не настроен: заполните конфиг PayKeeper на сервере.
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <div class="project-programm__buttons">
                                    <button type="button" class="btn project-programm__btn project-programm__btn--back" id="d2_back_pay">Назад</button>
                                    <button type="submit" class="btn project-programm__btn"<?= $paykeeperReady ? '' : ' disabled' ?>>Перейти к оплате</button>
                                </div>
                            </div>
                        </div>
                    </form>
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
        // Сразу переключаем на шаг «Проекты»
        var prog = document.querySelector('.main-tabs-click[data-tab="programm"]');
        if (prog) prog.click();
    }
})();

// D2 multi-step form logic
(function() {
    var priceList    = document.getElementById('d2_price_list');
    var amountField  = document.getElementById('d2_amount');
    var projectField = document.getElementById('d2_project');
    var projectSelect = document.getElementById('d2_project_select');
    var commentField  = document.getElementById('d2_payment_comment');
    var donorField   = document.getElementById('d2_donor_type');
    var customInput  = document.getElementById('d2_custom_amount');
    var freqField    = document.getElementById('d2_frequency');
    var payAmountText = document.getElementById('d2_pay_amount_text');
    var payProjectText = document.getElementById('d2_pay_project_text');
    var payDonorText = document.getElementById('d2_pay_donor_text');

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
                    updatePaySummary();
                });
            } else {
                amountField.value = val + ' руб.';
            }
            updatePaySummary();
        });
    });
    // Init default
    amountField.value = '300 руб.';

    function updatePaySummary() {
        if (payAmountText && amountField) {
            payAmountText.textContent = amountField.value || '-';
        }
        if (payProjectText) {
            var summaryProject = projectField && projectField.value ? projectField.value : '';
            if (!summaryProject && projectSelect) {
                summaryProject = projectSelect.value || '';
            }
            payProjectText.textContent = summaryProject || 'не выбран';
        }
        if (payDonorText && donorField) {
            payDonorText.textContent = donorField.value === 'ur' ? 'Юр. лицо' : 'Физ. лицо';
        }
    }
    updatePaySummary();

    function updatePaymentComment() {
        if (!commentField) return;
        var projectName = projectField && projectField.value ? projectField.value : '';
        if (!projectName && projectSelect && projectSelect.value) {
            projectName = projectSelect.value;
        }
        if (!projectName) {
            projectName = 'выбранный проект';
        }
        if (projectName === 'Пожертвование на ведение уставной деятельности') {
            commentField.value = projectName;
        } else {
            commentField.value = 'Пожертвование на проект ' + projectName;
        }
    }
    updatePaymentComment();

    // Next: summ → programm
    var btnNextSumm = document.getElementById('d2_next_summ');
    if (btnNextSumm) btnNextSumm.addEventListener('click', function() {
        if (!validateSummStep()) return;
        switchTab('programm');
    });

    // Back: programm → summ
    var btnBackProg = document.getElementById('d2_back_prog');
    if (btnBackProg) btnBackProg.addEventListener('click', function() { switchTab('summ'); });

    // Next: programm → data
    var btnNextProg = document.getElementById('d2_next_prog');
    if (btnNextProg) btnNextProg.addEventListener('click', function() {
        if (!validateProjectStep()) return;
        updatePaymentComment();
        updatePaySummary();
        switchTab('data');
    });

    if (projectSelect) {
        projectSelect.addEventListener('change', function() {
            if (projectField) projectField.value = projectSelect.value || '';
            updatePaymentComment();
            updatePaySummary();
        });
    }

    // Back: data → programm
    var btnBackData = document.getElementById('d2_back_data');
    if (btnBackData) btnBackData.addEventListener('click', function() { switchTab('programm'); });

    // Next: data → pay
    var btnNextData = document.getElementById('d2_next_data');
    if (btnNextData) btnNextData.addEventListener('click', function() {
        if (!validateDataStep()) return;
        updatePaySummary();
        switchTab('pay');
    });

    function validateSummStep() {
        if (!priceList) return true;
        var activePrice = priceList.querySelector('[data-val].active');
        if (activePrice && activePrice.getAttribute('data-val') === 'custom') {
            var customVal = customInput ? (customInput.value || '').trim() : '';
            if (!customVal || Number(customVal) <= 0) {
                alert('Введите корректную сумму.');
                if (customInput) customInput.focus();
                return false;
            }
        }
        return true;
    }

    function validateProjectStep() {
        var sel = document.getElementById('d2_project_select');
        if (!sel || !sel.value) {
            alert('Выберите проект.');
            if (sel) sel.focus();
            return false;
        }
        if (projectField) projectField.value = sel.value || 'Общий фонд';
        return true;
    }

    function validateDataStep() {
        var donor = donorField && donorField.value ? donorField.value : 'fiz';
        var paneSelector = donor === 'ur' ? '[data-tab="your"]' : '[data-tab="fiz"]';
        var pane = document.querySelector('.main-tabs-pane-project' + paneSelector);
        if (pane) {
            var requiredInputs = pane.querySelectorAll('input[required]');
            for (var i = 0; i < requiredInputs.length; i++) {
                var input = requiredInputs[i];
                if (!input.checkValidity()) {
                    input.reportValidity();
                    return false;
                }
            }
        }
        var agree = document.querySelector('input[name="agree_pd"]:checked');
        if (!agree || agree.value !== 'yes') {
            alert('Подтвердите ознакомление с политикой обработки ПДн.');
            return false;
        }
        return true;
    }

    // Prevent bypassing validation by clicking top tabs.
    document.querySelectorAll('.project-programm__tabs .main-tabs-click').forEach(function(tabEl) {
        tabEl.addEventListener('click', function(e) {
            var targetTab = tabEl.getAttribute('data-tab');
            var currentTabEl = document.querySelector('.project-programm__tabs .main-tabs-click.main-tabs-click--active');
            var currentTab = currentTabEl ? currentTabEl.getAttribute('data-tab') : 'summ';
            var order = { summ: 1, programm: 2, data: 3, pay: 4 };
            if (!targetTab || !order[targetTab] || !order[currentTab]) return;

            // Backward navigation is allowed.
            if (order[targetTab] <= order[currentTab]) return;

            var ok = true;
            if (order[targetTab] >= 2) ok = ok && validateSummStep();
            if (order[targetTab] >= 3) ok = ok && validateProjectStep();
            if (order[targetTab] >= 4) ok = ok && validateDataStep();
            if (!ok) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return;
            }
            switchTab(targetTab);
            e.preventDefault();
            e.stopImmediatePropagation();
        }, true);
    });

    // Back: pay → data
    var btnBackPay = document.getElementById('d2_back_pay');
    if (btnBackPay) btnBackPay.addEventListener('click', function() { switchTab('data'); });

    // Donor type toggle
    document.querySelectorAll('[data-donor]').forEach(function(el) {
        el.addEventListener('click', function() {
            donorField.value = el.getAttribute('data-donor');
            applyRequiredByDonor();
            updatePaySummary();
        });
    });

    function applyRequiredByDonor() {
        var donor = donorField && donorField.value ? donorField.value : 'fiz';
        var fizInputs = document.querySelectorAll('[data-tab="fiz"] input[name]');
        var urInputs  = document.querySelectorAll('[data-tab="your"] input[name]');

        fizInputs.forEach(function(input) {
            var must = ['last_name', 'first_name', 'email', 'phone', 'payment_comment'].indexOf(input.name) !== -1;
            input.required = donor === 'fiz' && must;
        });
        urInputs.forEach(function(input) {
            var must = ['ur_last_name', 'ur_first_name', 'ur_email', 'ur_phone', 'ur_company', 'ur_site'].indexOf(input.name) !== -1;
            input.required = donor === 'ur' && must;
        });
    }
    applyRequiredByDonor();

    function switchTab(tab) {
        if (tab === 'pay') {
            updatePaySummary();
        }
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
