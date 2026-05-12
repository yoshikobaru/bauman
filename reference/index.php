<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Референс-визиты");

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$_userGroups = $USER->IsAuthorized() ? $USER->GetUserGroupArray() : [];
$_isMember   = defined('PO_MEMBER_BASIC_ID') && (
    in_array(PO_MEMBER_BASIC_ID,   $_userGroups) ||
    in_array(PO_MEMBER_PREMIUM_ID, $_userGroups) ||
    in_array(PO_PARTNER_ID,        $_userGroups)
);

// Вспомогательная функция: записать заявку в HL-блок
function po_hlSave($type, $userId, array $data, $elementId = 0)
{
    if (!defined('HL_APPLICATIONS_ID') || HL_APPLICATIONS_ID <= 0) return false;
    $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
    if (!$hlEntity) return false;
    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
    $res = $hlClass::add([
        'UF_USER_ID'     => (int)$userId,
        'UF_TYPE'        => $type,
        'UF_STATUS'      => 'new',
        'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
        'UF_DATA'        => json_encode($data, JSON_UNESCAPED_UNICODE),
        'UF_ELEMENT_ID'  => (int)$elementId,
    ]);
    return $res->isSuccess();
}

$d5Done  = false; $d5Error  = '';
$d5Form = [
    'company' => '',
    'about' => '',
    'what_show' => '',
    'audience' => '',
    'd5_last_name' => '',
    'd5_first_name' => '',
    'd5_phone' => '',
    'd5_email' => '',
    'd5_site' => '',
    'd5_agree' => '',
];
$d5Flash = function_exists('po_flash_get') ? po_flash_get('d5_reference_org') : null;
if (is_array($d5Flash)) {
    $d5Done = !empty($d5Flash['done']);
    $d5Error = (string)($d5Flash['error'] ?? '');
    if (!empty($d5Flash['form']) && is_array($d5Flash['form'])) {
        $d5Form = array_merge($d5Form, $d5Flash['form']);
    }
}

// D5: Организация референс-визита (все)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d5_action'])) {
    $d5Form = [
        'company'      => trim($_POST['company']       ?? ''),
        'about'        => trim($_POST['about']         ?? ''),
        'what_show'    => trim($_POST['what_show']     ?? ''),
        'audience'     => trim($_POST['audience']      ?? ''),
        'd5_last_name' => trim($_POST['d5_last_name']  ?? ''),
        'd5_first_name'=> trim($_POST['d5_first_name'] ?? ''),
        'd5_phone'     => trim($_POST['d5_phone']      ?? ''),
        'd5_email'     => trim($_POST['d5_email']      ?? ''),
        'd5_site'      => trim($_POST['d5_site']       ?? ''),
        'd5_agree'     => (string)($_POST['d5_agree']  ?? ''),
    ];
    $company = $d5Form['company'];
    $about = $d5Form['about'];
    $show = $d5Form['what_show'];
    $audience = $d5Form['audience'];
    $fn = $d5Form['d5_first_name'];
    $ln = $d5Form['d5_last_name'];
    $em = $d5Form['d5_email'];
    $phone = $d5Form['d5_phone'];
    $site = $d5Form['d5_site'];
    $agreePd = $d5Form['d5_agree'] === 'yes';
    if (!$company || !$fn || !$em) {
        $d5Error = 'Заполните обязательные поля: Компания, Имя, Email.';
    } elseif ($phone !== '' && !po_is_valid_phone_chars($phone)) {
        $d5Error = 'Телефон может содержать только цифры, пробел, + и -.';
    } elseif (!$agreePd) {
        $d5Error = 'Необходимо согласие с политикой обработки ПДн.';
    } else {
        $saved = $hlOk ? po_hlSave('reference_org', $USER->IsAuthorized() ? $USER->GetID() : 0, [
            'company'    => $company, 'about'     => $about,
            'what_show'  => $show,   'audience'  => $audience,
            'last_name'  => $ln,     'first_name'=> $fn,
            'email'      => $em,     'phone'     => $phone,
            'site'       => $site,
            'agree_pd'   => $agreePd ? 'yes' : 'no',
        ]) : false;
        if (!$hlOk || $saved) {
            $d5Done = true;
            po_logAction('form_submit', 'application', 0, 'D5 организация референс-визита');
            $d5Data = [
                'first_name' => $fn,      'last_name' => $ln,
                'email'      => $em,      'phone'     => $phone,
                'company'    => $company, 'about'     => $about,
                'what_show'  => $show,    'audience'  => $audience,
                'site'       => $site,    'agree_pd'  => $agreePd ? 'yes' : 'no',
            ];
            po_sendAdminEmail('reference_org', $d5Data);
            po_createCrmLead('reference_org', $d5Data);
            if (function_exists('po_flash_set')) {
                po_flash_set('d5_reference_org', ['done' => true, 'error' => '', 'form' => []]);
            }
            LocalRedirect('/reference/?d5=success#form-reference-visits');
            exit;
        } else {
            $d5Error = 'Ошибка сохранения. Попробуйте позже.';
        }
    }
    if ($d5Error !== '') {
        if (function_exists('po_flash_set')) {
            po_flash_set('d5_reference_org', ['done' => false, 'error' => $d5Error, 'form' => $d5Form]);
        }
        LocalRedirect('/reference/?d5=error#form-reference-visits');
        exit;
    }
}
?><main>
        <!-- banner-other -->
		<section class="banner-other">
            <div class="container">
                <div class="banner-other__wrapper">
                    <div class="banner-other__content">
                        <div class="banner-other__info">
                            <h1 class="banner-other__title main-title">
                                Референс- визиты Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана
                            </h1>
                            <div class="banner-other__list">
                                <div class="banner-other__item">
                                    <h2>
                                        Референс-визит
                                    </h2>
                                    <p>
                                        Экскурсия по вашему предприятию для потенциальных и действующих клиентов, партнёров, экспертов из области.
                                    </p>
                                </div>
                                <div class="banner-other__item">
                                    <h2>
                                        Формат
                                    </h2>
                                    <p>
                                        До 12 участников смотрят ваше производство, обсуждают технологии и вызовы области.

                                    </p>
                                </div>
                            </div>
							<div class="banner-other__buttons">
								<a href="#culture" class="banner-other__btn btn">Подробнее</a>
								<a href="#" class="banner-other__btn btn resume-select__btn" data-fancybox data-src="#form-reference-visits">Стать принимающей стороной</a>
							</div>
                        </div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference_visits_hero.jpg" alt="" class="banner-other__image">
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.banner-other -->

        <!-- visits dynamic (из инфоблока "Референс-визиты") -->
        <?php
        $iblockRefOk = \Bitrix\Main\Loader::includeModule('iblock');
        $arActiveVisits = [];
        $arPastVisits   = [];

        if ($iblockRefOk && defined('IBLOCK_REFERENCE_ID') && IBLOCK_REFERENCE_ID > 0) {
            $dbRef = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                ['IBLOCK_ID' => IBLOCK_REFERENCE_ID, 'ACTIVE' => 'Y'],
                false, false,
                ['ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'PREVIEW_PICTURE']
            );
            while ($el = $dbRef->GetNext()) {
                $dbP = CIBlockElement::GetProperty(
                    IBLOCK_REFERENCE_ID, $el['ID'], 'sort', 'asc', []
                );
                $elProps = []; $statusXml = '';
                $refCodes = ['REF_STATUS', 'REF_DATE', 'REF_LOCATION', 'REF_DURATION', 'REF_REGISTER_URL'];
                while ($p = $dbP->Fetch()) {
                    if (!in_array($p['CODE'], $refCodes)) continue;
                    $elProps[$p['CODE']] = $p['VALUE_ENUM'] ?: $p['VALUE'];
                    if ($p['CODE'] === 'REF_STATUS') $statusXml = $p['VALUE_XML_ID'] ?? '';
                }
                $el['_PROPS']      = $elProps;
                $el['_STATUS_XML'] = $statusXml;
                if ($statusXml === 'completed') {
                    $arPastVisits[]   = $el;
                } else {
                    $arActiveVisits[] = $el;
                }
            }
        }

        $visitsTab = $_GET['visits_tab'] ?? 'active';
        $hasAny = !empty($arActiveVisits) || !empty($arPastVisits);
        ?>

        <section class="visits">
            <div class="container">
                <h2 class="main-title visits__title">Визиты</h2>
                <?php if ($hasAny): ?>
                <div style="display:flex;gap:12px;margin-bottom:32px;flex-wrap:wrap;margin-top:24px">
                    <a href="?visits_tab=active" class="btn <?= $visitsTab !== 'active' ? 'btn-empty' : '' ?>"
                       style="padding:10px 24px">Активные (<?= count($arActiveVisits) ?>)</a>
                    <a href="?visits_tab=past" class="btn <?= $visitsTab !== 'past' ? 'btn-empty' : '' ?>"
                       style="padding:10px 24px">Завершённые (<?= count($arPastVisits) ?>)</a>
                </div>
                <?php $visitsToShow = ($visitsTab === 'past') ? $arPastVisits : $arActiveVisits; ?>
                <?php if (empty($visitsToShow)): ?>
                <p style="color:#888">
                    <?= $visitsTab === 'past' ? 'Завершённых визитов пока нет.' : 'Активных визитов пока нет.' ?>
                </p>
                <?php else: ?>
                <div class="visits__list">
                    <?php foreach ($visitsToShow as $visit):
                        $vProps   = $visit['_PROPS'] ?? [];
                        $imgSrc   = !empty($visit['PREVIEW_PICTURE'])
                            ? CFile::GetPath($visit['PREVIEW_PICTURE'])
                            : SITE_TEMPLATE_PATH . '/assets/img/reference-page/reference-main-img-1.png';
                        $detailUrl = '/reference/' . ($visit['CODE'] ?: $visit['ID']) . '/';
                    ?>
                    <div class="visits__card">
                        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($visit['NAME']) ?>" class="visits__image">
                        <div class="visits__content">
                            <div class="visits__date">
                                <div class="visits__date-current">
                                    <?php if (!empty($vProps['REF_DATE'])): ?>
                                    <p><span>Дата: </span><?= htmlspecialchars($vProps['REF_DATE']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($vProps['REF_LOCATION'])): ?>
                                    <p><?= htmlspecialchars($vProps['REF_LOCATION']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($vProps['REF_DURATION'])): ?>
                                    <p><?= htmlspecialchars($vProps['REF_DURATION']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <h3 class="visits__subtitle"><?= htmlspecialchars($visit['NAME']) ?></h3>
                            <?php if (!empty($visit['PREVIEW_TEXT'])): ?>
                            <p class="visits__text"><?= htmlspecialchars($visit['PREVIEW_TEXT']) ?></p>
                            <?php endif; ?>
                            <a href="<?= htmlspecialchars($detailUrl) ?>" class="btn visits__btn btn-transparent">Подробнее</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <p style="color:#888;margin-top:16px">Визиты пока не добавлены.</p>
                <?php endif; ?>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.visits -->

        <!-- culture -->
		<section class="culture culture-reference" id="culture">
			<div class="container">
				<h2 class="main-title culture__title">
					Что дают референс-визиты 
				</h2>
				<div class="culture__wrapper">
					
					<div class="culture__box">
						<div class="culture__card">
							<h3>
								Визуализация вашего продукта или услуги в действии
							</h3>
							<p>
								Наглядное доказательство эффективности.
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Прямой контакт с партнером или клиентом
							</h3>
							<p>
								Обратная связь + элемент доверия и прозрачности.
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Профессиональный рост и развитие
							</h3>
							<p>
								компании и ее сотрудников.
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Рыночные преимущества
							</h3>
							<p>
								Расширение рынков сбыта, привлечение новых заказчиков/партнеров.
							</p>
						</div>
					</div>
                    <div class="culture__card culture__card--big culture__card--red">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/culture-bg-card.png" alt="" class="culture__card-image">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/culture__card--red-ellips.png" alt="" class="culture__card-ellips">
                        <div class="culture__card-overlay">
                            <h3>
                               Репутационные преимущества.
                            </h3>
                            <p>
                               Увеличение узнаваемости бренда, положительный имидж. Развитие бизнеса в долгосрочной перспективе.
                            </p>
                        </div>						
				</div>
				</div>
			</div>
			<!-- /.container -->
		</section>
        <!-- participants -->
        <section class="participants">
            <div class="container">
                <div class="participants__wrapper">
                    <div class="participants__left">
                        <h2 class="main-title participants__title">
                            Участники визита
                        </h2>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/participants-img.png" alt="">
                    </div>
                    <div class="participants__right">
                        <div class="participants__card">
                            <p class="participants__number">
                                01
                            </p>
                            <p class="participants__position">
                                Топ менеджмент 
                            </p>
                            <ul class="participants__list">
                                <li class="participants__item">
                                    Генеральные директора
                                </li>
                                <li class="participants__item">
                                    Коммерческие директора
                                </li>
                                <li class="participants__item">
                                    Руководители профильных отделов отделов/направлений
                                </li>
                            </ul>
                        </div>
                        <div class="participants__card">
                            <p class="participants__number">
                                02
                            </p>
                            <p class="participants__position">
                                Отраслевые эксперты 
                            </p>
                            <ul class="participants__list">
                                <li class="participants__item">
                                    Эксперты от МВТУ (МГТУ) им. Н.Э. Баумана
                                </li>
                                <li class="participants__item">
                                    Эксперты от Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана
                                </li>
                            </ul>
                        </div>
                        <div class="participants__card">
                            <p class="participants__number">
                                03
                            </p>
                            <p class="participants__position">
                                Технические специалисты
                            </p>
                            <ul class="participants__list">
                                <li class="participants__item">
                                    Технические директора
                                </li>
                                <li class="participants__item">
                                    Представители отдела развития
                                </li>
                                <li class="participants__item">
                                    Представители отдела закупок
                                </li>
                                <li class="participants__item">
                                    Главные инженеры 
                                </li>
                                <li class="participants__item">
                                    Специалисты по внедрению
                                </li>
                                <li class="participants__item">
                                    Сервисные инженеры
                                </li>
                                <li class="participants__item">
                                    И другие 
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
               
            </div>
            <!-- /.container -->
        </section>
        <!-- /.participants -->
        <!-- programm -->
        <section class="programm">
            <div class="container">
                <div class="programm__info">
                    <h2 class="main-title programm__title">

                    </h2>
                    <p class="main-text programm__text">

                    </p>
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.programm -->

        <!-- opportunities -->
		<section class="opportunities opportunities-reference">
			<div class="container">
				<div class="opportunities__wrapper">
					<div class="opportunities__info">
						<h2 class="opportunities__title main-title">
							Программа и затраты
						</h2>
						<p class="main-text">
                            Программа корректируется под вашу специфику, масштабы и свободное время

                        </p>
					</div>
					<div class="opportunities__missions">
						<h3>
							Что от вас требуется:
						</h3>
						<ul class="opportunities__missions-list">
                            <li>
                                Согласовать дату визита, программу
                            </li>
                            <li>
                                Назначить куратора от компании
                            </li>
                            <li>
                                Подготовить помещения, уведомить персонал об экскурсии
                            </li>
                            <li>
                                Провести визит в выбранный день
                            </li>
                        </ul>
					</div>
					<div class="opportunities__target">
						<h3>
							Пример программы
						</h3>
						<div class="opportunities__target-plan">
                            <h4 class="opportunities__target-time">
                                10:00 — 10:30
                            </h4>
                            <p class="opportunities__target-event">
                                Встреча участников, приветствие, знакомство, краткая информация о компании.

                            </p>
                        </div>
						<div class="opportunities__target-plan">
                            <h4 class="opportunities__target-time">
                                10:30 — 12:00
                            </h4>
                            <p class="opportunities__target-event">
                                Экскурсия. Показываете то, чем гордитесь: цеха, технологии, продукты, процессы.
                            </p>
                        </div>
						<div class="opportunities__target-plan">
                            <h4 class="opportunities__target-time">
                                12:30 — 13:30
                            </h4>
                            <p class="opportunities__target-event">
                                Нетворкинг/ кофе-брейк. Неформальное общение.
                            </p>
                        </div>
						<div class="opportunities__target-plan">
                            <h4 class="opportunities__target-time">
                                13:30 — 14:30
                            </h4>
                            <p class="opportunities__target-event">
                                Кейс-сессия. Вы рассказываете о реальном вызове — обсуждаем решения

                            </p>
                        </div>
						<div class="opportunities__target-plan">
                            <h4 class="opportunities__target-time">
                                14:30 — 15:00 
                            </h4>
                            <p class="opportunities__target-event">
                                Подведение итогов. Вопросы, фото, обмен контактами
                            </p>
                        </div>
				</div>
				</div>
			</div>
			<!-- /.container -->
		</section>
		<!-- /.opportunities -->
         <!-- new-project -->
		<section class="new-project new-project-faq">
			<div class="container">
				<h2 class="main-title new-project__title">
					Частные вопросы
				</h2>
				<div class="new-project__slider swiper">
					<div class="swiper-wrapper">
						<div class="swiper-slide new-project__item">
							<div class="new-project__card">
								<h3>
									Сколько раз нужно принимать?
								</h3>
								<p>
									Один раз. Дальше — по желанию. Никаких обязательств.
								</p>
							</div>
							<div class="new-project__card">
								<h3>
									А если у нас коммерческая тайна?
								</h3>
								<p>
									Вы решаете, что показывать, без раскрытия секретов.
								</p>
							</div>
							<div class="new-project__card">
								<h3>
									Сколько времени займёт визит?
								</h3>
                                <p>
                                    От 4 до 8 часов, с учётом ваших ресурсов и возможностей.
                                </p>
							</div>
						</div>
						<div class="swiper-slide new-project__item">
							<div class="new-project__card">
								<h3>
									Это платно?
								</h3>
								<p>
									Нет. Все затраты во время визита к вам — на ваше усмотрение.
								</p>
							</div>
							<div class="new-project__card">
								<h3>
									Кто участники?
								</h3>
								<p>
									Выпускники МВТУ (МГТУ) им. Н.Э. Баумана с опытом. Отбираем 8–12 человек под профиль вашей компании.
								</p>
							</div>
							<div class="new-project__card new-project__card--faq">
								<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/faq-bg.png" alt="">
                                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/faq-icon.png" alt="" class="new-project__icon-empty">
							</div>
						</div>
					</div>
					<div class="swiper-pagination"></div>
				</div>
			</div>
			<!-- /.container -->
		</section>
	</main>
<div class="form-reference-visits" id="form-reference-visits" style="display:none;">
	<div class="join__wrapper">
		<?php if ($d5Done): ?>
			<h2 class="account__title main-title">Заявка принята!</h2>
			<p style="margin-top:16px">Мы свяжемся с вами в ближайшее время.</p>
		<?php else: ?>
		<h2 class="account__title main-title">Заявка на организацию референс-визита</h2>
		<?php if ($d5Error): ?>
		<div class="authorization__alert authorization__alert--error" style="margin:12px 0">
			<p><?= htmlspecialchars($d5Error) ?></p>
		</div>
		<?php endif; ?>
		<form method="POST" action="/reference/#form-reference-visits" id="form-d5">
			<input type="hidden" name="d5_action" value="1">
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">Данные о компании</h3>
				</div>
				<div class="account__personal-list account__personal-list--form">
					<input type="text" name="company"   placeholder="Компания *" required value="<?= htmlspecialchars($d5Form['company']) ?>">
					<input type="text" name="about"     placeholder="Чем занимается компания?" value="<?= htmlspecialchars($d5Form['about']) ?>">
					<input type="text" name="what_show" placeholder="Что хотите показать?" value="<?= htmlspecialchars($d5Form['what_show']) ?>">
					<input type="text" name="audience"  placeholder="Для какой аудитории?" value="<?= htmlspecialchars($d5Form['audience']) ?>">
				</div>
			</div>
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">Контактное лицо</h3>
				</div>
				<div class="account__personal-list account__grid">
					<input type="text"  name="d5_last_name"  placeholder="Фамилия" value="<?= htmlspecialchars($d5Form['d5_last_name']) ?>">
					<input type="text"  name="d5_first_name" placeholder="Имя *" required value="<?= htmlspecialchars($d5Form['d5_first_name']) ?>">
					<input type="tel"   name="d5_phone"      placeholder="Номер телефона" value="<?= htmlspecialchars($d5Form['d5_phone']) ?>">
					<input type="email" name="d5_email"      placeholder="e-mail *" required value="<?= htmlspecialchars($d5Form['d5_email']) ?>">
					<input type="text"  name="d5_site"       placeholder="Сайт" value="<?= htmlspecialchars($d5Form['d5_site']) ?>">
				</div>
			</div>
			<div class="join__politic">
				<div class="join__politic-question">
					<p class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
					<div class="account__graduate-choice">
						<label class="account__graduate-item">
							<input type="radio" name="d5_agree" value="yes" class="account__graduate-input" required <?= $d5Form['d5_agree'] === 'yes' ? 'checked' : '' ?>>
							<span class="account__graduate-box"></span>Да
						</label>
						<label class="account__graduate-item">
							<input type="radio" name="d5_agree" value="no" class="account__graduate-input" <?= $d5Form['d5_agree'] === 'no' ? 'checked' : '' ?>>
							<span class="account__graduate-box"></span>Нет
						</label>
					</div>
				</div>
			</div>
			<p class="form-required-note">* Обязательные поля</p>
			<button type="submit" class="btn authorization__btn">Отправить</button>
		</form>
		<?php endif; ?>
	</div>
</div>

<?php if ($d5Done || $d5Error): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.Fancybox) Fancybox.show([{src: '#form-reference-visits', type: 'inline'}]);
});
</script>
<?php endif; ?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>