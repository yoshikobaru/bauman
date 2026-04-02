<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Витрина компетенций");

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$_userGroups = $USER->IsAuthorized() ? $USER->GetUserGroupArray() : [];
$_isMember   = defined('PO_MEMBER_BASIC_ID') && (
    in_array(PO_MEMBER_BASIC_ID,   $_userGroups) ||
    in_array(PO_MEMBER_PREMIUM_ID, $_userGroups) ||
    in_array(PO_PARTNER_ID,        $_userGroups)
);

$d6Done  = false;
$d6Error = '';

// D6: Запрос в витрине компетенций (только члены)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d6_action'])) {
    if (!$_isMember) {
        $d6Error = 'Доступно только для членов общества.';
    } else {
        $company = trim($_POST['company']     ?? '');
        $fn      = trim($_POST['first_name']  ?? '');
        $em      = trim($_POST['email']       ?? '');
        if (!$fn || !$em) {
            $d6Error = 'Заполните обязательные поля: Имя, Email.';
        } else {
            if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
                $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
                if ($hlEntity) {
                    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                    $res = $hlClass::add([
                        'UF_USER_ID'     => (int)$USER->GetID(),
                        'UF_TYPE'        => 'competency_request',
                        'UF_STATUS'      => 'new',
                        'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                        'UF_DATA'        => json_encode([
                            'company'    => $company,
                            'first_name' => $fn,
                            'last_name'  => trim($_POST['last_name'] ?? ''),
                            'email'      => $em,
                            'phone'      => trim($_POST['phone']  ?? ''),
                            'request'    => trim($_POST['request'] ?? ''),
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
                    if ($res->isSuccess()) {
                        $d6Done = true;
                        $d6Data = [
                            'first_name' => $fn,     'last_name' => trim($_POST['last_name'] ?? ''),
                            'email'      => $em,     'phone'     => trim($_POST['phone'] ?? ''),
                            'company'    => $company,'request'   => trim($_POST['request'] ?? ''),
                        ];
                        po_sendAdminEmail('competency_request', $d6Data);
                        po_createCrmLead('competency_request', $d6Data);
                    } else {
                        $d6Error = 'Ошибка сохранения. Попробуйте позже.';
                    }
                }
            } else {
                $d6Done = true; // HL не настроен — просто принимаем
            }
        }
    }
}
?>

<main>
        <!-- banner-other -->
		<section class="banner-other">
            <div class="container">
                <div class="banner-other__wrapper">
                    <div class="banner-other__content">
                        <div class="banner-other__info">
                            <h1 class="banner-other__title main-title">
                               Витрина компетенций МВТУ (МГТУ) им. Н.Э. Баумана
                            </h1>
                            <a href="#" class="banner-other__btn btn" data-fancybox data-src="#form-competencies">Стать партнёром</a>
                        </div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/competencies-img.png" alt="" class="banner-other__image">
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.banner-other -->
        
        <section class="competencies">
            <div class="container">
                <div class="competencies__tabs">
                    <ul class="competencies__navs">
                        <li class="main-tabs-click main-tabs-click--active" data-tab="competencies__univer">
                            Компетенции университета
                        </li>
                        <li class="main-tabs-click" data-tab="competencies__student">
                            Студенческие конструкторские бюро
                        </li>
                        <li class="main-tabs-click" data-tab="competencies__partner">
                            Компетенции партнеров 
                        </li>
                    </ul>
                </div>
                <div class="competencies__content">
                    <div class="competencies__item main-tabs-pane main-tabs-pane--active" data-tab="competencies__univer">
                        <div class="competencies__list">
                            <div class="competencies__card">
                                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/competencies-img-1.png" alt="" class="competencies__card-image">
                                <div class="competencies__card-tags">
                                    <div>#ракетостроение</div>
                                    <div>#космические_аппараты</div>
                                    <div>#системный_анализ</div>
                                    <div>#газодинамика</div>
                                    <div>#прочность</div>
                                </div>
                                <p class="competencies__card-subtext main-text">
                                    Защита данных и цифровых систем, участие в кибербезопасности.
                                </p>
                                <h2 class="competencies__card-title">
                                    НОЦ «Перспективные исследования в ракетно-космической технике» (ПИРТ)
                                </h2>
                                <p class="main-text competencies__card-text">
                                    Центр занимается комплексными научными исследованиями и разработками в области проектирования перспективных образцов ракетно-космической техники. 
                                </p>
                                <a href="#" class="competencies__card-link">Скачать подробное описание в PDF</a>
                                <div class="competencies__card-overlay">
                                    <h3>
                                        Компетенции
                                    </h3>
                                    <ul>
                                        <li>
                                            Системное проектирование ракетно-космических комплексов;
                                        </li>
                                        <li>
                                            Газодинамические и тепловые расчёты двигательных установок;
                                        </li>
                                        <li>
                                            Прочностной анализ конструкций летательных аппаратов;
                                        </li>
                                        <li>
                                            Баллистическое проектирование и оптимизация траекторий;
                                        </li>
                                        <li>
                                            Создание математических моделей динамики полёта.
                                        </li>
                                    </ul>
                                </div>
                                <button class="btn" data-fancybox data-src="#form-competencies">Отправить запрос</button>
                            </div>
                            <div class="competencies__card">
                                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/competencies-img-1.png" alt="" class="competencies__card-image">
                                <div class="competencies__card-tags">
                                    <div>#ракетостроение</div>
                                    <div>#космические_аппараты</div>
                                    <div>#системный_анализ</div>
                                    <div>#газодинамика</div>
                                    <div>#прочность</div>
                                </div>
                                <p class="competencies__card-subtext main-text">
                                    Защита данных и цифровых систем, участие в кибербезопасности.
                                </p>
                                <h2 class="competencies__card-title">
                                    НОЦ «Перспективные исследования в ракетно-космической технике» (ПИРТ)
                                </h2>
                                <p class="main-text competencies__card-text">
                                    Центр занимается комплексными научными исследованиями и разработками в области проектирования перспективных образцов ракетно-космической техники. 
                                </p>
                                <a href="#" class="competencies__card-link">Скачать подробное описание в PDF</a>
                                <div class="competencies__card-overlay">
                                    <h3>
                                        Компетенции
                                    </h3>
                                    <ul>
                                        <li>
                                            Системное проектирование ракетно-космических комплексов;
                                        </li>
                                        <li>
                                            Газодинамические и тепловые расчёты двигательных установок;
                                        </li>
                                        <li>
                                            Прочностной анализ конструкций летательных аппаратов;
                                        </li>
                                        <li>
                                            Баллистическое проектирование и оптимизация траекторий;
                                        </li>
                                        <li>
                                            Создание математических моделей динамики полёта.
                                        </li>
                                    </ul>
                                </div>
                                <button class="btn" data-fancybox data-src="#form-competencies">Отправить запрос</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        
	</main>
<div class="form-reference-visits" id="form-competencies" style="display:none;">
	<div class="join__wrapper">
		<?php if ($d6Done): ?>
			<h2 class="account__title main-title">Заявка принята!</h2>
			<p style="margin-top:16px">Мы свяжемся с вами в ближайшее время.</p>
		<?php elseif (!$USER->IsAuthorized()): ?>
			<h2 class="account__title main-title">Запрос в витрину компетенций</h2>
			<p style="margin-top:16px">Для отправки запроса необходимо <a href="/authorization/">войти</a> или <a href="/join/">вступить в общество</a>.</p>
		<?php elseif (!$_isMember): ?>
			<h2 class="account__title main-title">Запрос в витрину компетенций</h2>
			<p style="margin-top:16px">Запрос доступен только для членов Политехнического общества. <a href="/join/">Вступить</a></p>
		<?php else: ?>
		<h2 class="account__title main-title">Запрос в витрину компетенций</h2>
		<?php if ($d6Error): ?>
		<div class="authorization__alert authorization__alert--error" style="margin:12px 0">
			<p><?= htmlspecialchars($d6Error) ?></p>
		</div>
		<?php endif; ?>
		<form method="POST" action="/competencies/">
			<input type="hidden" name="d6_action" value="1">
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">Ваши данные</h3>
				</div>
				<div class="account__personal-list account__grid">
					<input type="text"  name="last_name"  placeholder="Фамилия"
					       value="<?= htmlspecialchars($USER->GetParam('LAST_NAME')) ?>">
					<input type="text"  name="first_name" placeholder="Имя *" required
					       value="<?= htmlspecialchars($USER->GetParam('NAME')) ?>">
					<input type="email" name="email"      placeholder="Электропочта *" required
					       value="<?= htmlspecialchars($USER->GetParam('EMAIL')) ?>">
					<input type="tel"   name="phone"      placeholder="Телефон">
					<input type="text"  name="company"    placeholder="Компания">
				</div>
			</div>
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">Запрос</h3>
				</div>
				<div style="margin-top:8px">
					<textarea name="request" placeholder="Опишите ваш запрос к витрине компетенций"
					          style="width:100%;min-height:100px;padding:12px;border:1px solid #ccc"></textarea>
				</div>
			</div>
			<button type="submit" class="btn authorization__btn">Отправить</button>
		</form>
		<?php endif; ?>
		</div>
	</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>