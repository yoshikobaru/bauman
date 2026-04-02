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
        $d2Error = 'Заполните обязательные поля: Имя, Электропочта.';
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
    <!-- banner-other -->
    <section class="banner-other banner-project-current">
        <div class="container">
            <div class="banner-other__wrapper banner-other__wrapper--current">
                <div class="banner-other__content">
                    <div class="banner-other__info banner-other__info--current">
                        <div class="banner-other__date">
                            <p class="banner-other__status">Активный</p>
                            <p class="banner-other__time"><span>Запущен</span> 11 Апреля 2025</p>
                        </div>
                        <h1 class="banner-other__title main-title">
                            Бауманский университет пилотирует создание Фонда целевого капитала
                        </h1>
                        <div class="banner-other__detail">
                            <div>
                                <p class="details-project__about-discription">Команда</p>
                                <div class="details-project__about-team">
                                    <div class="details-project__about-who">
                                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/foto-team-1.png" alt="" class="details-project__about-foto">
                                        <div class="details-project__about-person"><p>Дима Архипов</p><p>Менеджер проекта</p></div>
                                    </div>
                                    <div class="details-project__about-who">
                                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/foto-team-2.png" alt="" class="details-project__about-foto">
                                        <div class="details-project__about-person"><p>Алена Артемьева</p><p>Разработчик</p></div>
                                    </div>
                                    <div class="details-project__about-who">
                                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/foto-team-3.png" alt="" class="details-project__about-foto">
                                        <div class="details-project__about-person"><p>Олег Швец</p><p>Вдохновитель</p></div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="details-project__about-discription">Документация</p>
                                <div class="details-project__about-document">
                                    <a href="#" class="details-project__about-download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none"><path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="white"/></svg>
                                        document-plan.pdf
                                    </a>
                                </div>
                                <p class="details-project__about-discription">Ссылки</p>
                                <div class="details-project__about-links">
                                    <a href="#" target="_blank">Группа Вконтакте</a>
                                    <a href="#" target="_blank">Канал с новостями в Телеграм</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                </div>
                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-img.png" alt="" class="banner-other__image banner-other__image--current">
            </div>
        </div>
    </section>

    <!-- D2: Форма поддержки проекта -->
    <section class="project-programm">
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

                        <div class="project-programm__tabs">
                            <ul class="project-programm__navs">
                                <li class="main-tabs-click main-tabs-click--active" data-tab="summ">Сумма</li>
                                <li class="main-tabs-click" data-tab="programm">Программы</li>
                                <li class="main-tabs-click" data-tab="data">Данные</li>
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
                                    <div data-val="custom">
                                        Другая: <input type="number" id="d2_custom_amount" placeholder="руб." min="1" style="width:90px;padding:4px 8px;margin-left:6px">
                                    </div>
                                </div>
                                <div class="project-programm__buttons">
                                    <button type="button" class="btn project-programm__btn" id="d2_next_summ">Продолжить</button>
                                </div>
                            </div>

                            <!-- Шаг 2: Проект -->
                            <div class="project-programm__item main-tabs-pane" data-tab="programm">
                                <div class="project-programm__all">
                                    <select id="d2_project_select">
                                        <option value="">— Выберите проект —</option>
                                        <?php if (!empty($arProjects)):
                                            foreach ($arProjects as $proj): ?>
                                        <option value="<?= htmlspecialchars($proj['NAME']) ?>"><?= htmlspecialchars($proj['NAME']) ?></option>
                                        <?php endforeach;
                                        else: ?>
                                        <option value="Общий фонд">Общий фонд поддержки</option>
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
                                            <input type="email" name="email"      placeholder="Электропочта *" required
                                                   value="<?= htmlspecialchars($prefill['email']) ?>">
                                            <input type="tel"   name="phone"      placeholder="Номер телефона">
                                        </div>
                                    </div>
                                    <div class="join__politic">
                                        <div class="join__politic-question">
                                            <p class="join__politic-link">Ознакомлен с <a href="#">Уставом</a> и <a href="#">Офертой</a></p>
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
                                            <input type="email" name="email"      placeholder="Электропочта *" required>
                                            <input type="tel"   name="phone"      placeholder="Номер телефона">
                                            <input type="text"  name="company"    placeholder="Компания">
                                            <input type="text"  name="site"       placeholder="Сайт">
                                        </div>
                                    </div>
                                    <div class="join__politic">
                                        <div class="join__politic-question">
                                            <p class="join__politic-link">Согласен с <a href="#">политикой обработки ПДн</a></p>
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
                                </div>

                                <div class="project-programm__buttons">
                                    <button type="button" class="btn project-programm__btn project-programm__btn--back" id="d2_back_data">Назад</button>
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

    <!-- Раздел фондов -->
    <?php
    $arFunds = [];
    if ($iblockOk && defined('IBLOCK_FUNDS_ID') && IBLOCK_FUNDS_ID > 0) {
        $dbFunds = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => IBLOCK_FUNDS_ID, 'ACTIVE' => 'Y'],
            false, false,
            ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE']
        );
        while ($row = $dbFunds->GetNext()) {
            $arFunds[] = $row;
        }
    }
    if (!empty($arFunds)):
    ?>
    <section style="padding:60px 0;background:#f8f8f8">
        <div class="container">
            <h2 class="main-title" style="margin-bottom:8px">Наши фонды</h2>
            <p style="color:#666;margin-bottom:32px">Каждое пожертвование идёт в конкретный фонд. Выберите направление поддержки.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px">
                <?php foreach ($arFunds as $fund):
                    $fundImg = !empty($fund['PREVIEW_PICTURE']) ? CFile::GetPath($fund['PREVIEW_PICTURE']) : '';
                ?>
                <div style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07)">
                    <?php if ($fundImg): ?>
                    <img src="<?= htmlspecialchars($fundImg) ?>" alt="<?= htmlspecialchars($fund['NAME']) ?>"
                         style="width:100%;height:180px;object-fit:cover">
                    <?php endif; ?>
                    <div style="padding:24px">
                        <h3 style="font-size:18px;font-weight:700;margin-bottom:10px"><?= htmlspecialchars($fund['NAME']) ?></h3>
                        <p style="color:#555;font-size:14px;line-height:1.6;margin-bottom:20px"><?= htmlspecialchars($fund['PREVIEW_TEXT'] ?? '') ?></p>
                        <button type="button" class="btn" style="width:100%"
                                onclick="po_selectFund(<?= htmlspecialchars(json_encode($fund['NAME'])) ?>)">
                            Поддержать этот фонд
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
// Выбрать фонд из карточки — скроллит к форме и подставляет название
function po_selectFund(fundName) {
    var sel = document.getElementById('d2_project_select');
    if (sel) {
        // Ищем опцию с совпадающим текстом или value
        for (var i = 0; i < sel.options.length; i++) {
            if (sel.options[i].text === fundName || sel.options[i].value === fundName) {
                sel.selectedIndex = i;
                break;
            }
        }
    }
    var pf = document.getElementById('d2_project');
    if (pf) pf.value = fundName;
    var form = document.getElementById('form-d2');
    if (form) form.scrollIntoView({behavior:'smooth', block:'start'});
}

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
