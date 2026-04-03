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

$d4Done  = false; $d4Error  = '';
$d5Done  = false; $d5Error  = '';

// D4: Участие в референс-визите (только члены)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d4_action'])) {
    if (!$_isMember) {
        $d4Error = 'Участие в референс-визитах доступно только членам общества.';
    } else {
        $fn = trim($_POST['first_name'] ?? '');
        $ln = trim($_POST['last_name']  ?? '');
        $em = trim($_POST['email']      ?? '');
        if (!$fn || !$ln || !$em) {
            $d4Error = 'Заполните обязательные поля: Имя, Фамилия, Email.';
        } else {
            $saved = $hlOk ? po_hlSave('reference_visit', $USER->GetID(), [
                'last_name'  => $ln, 'first_name' => $fn,
                'email'      => $em, 'phone'      => trim($_POST['phone'] ?? ''),
                'telegram'   => trim($_POST['telegram'] ?? ''),
            ]) : false;
            if (!$hlOk || $saved) {
                $d4Done = true;
                po_logAction('form_submit', 'application', 0, 'D4 участие в референс-визите');
                $d4Data = [
                    'first_name' => $fn, 'last_name' => $ln,
                    'email'      => $em, 'phone'     => trim($_POST['phone'] ?? ''),
                    'telegram'   => trim($_POST['telegram'] ?? ''),
                ];
                po_sendAdminEmail('reference_visit', $d4Data);
                po_createCrmLead('reference_visit', $d4Data);
            } else {
                $d4Error = 'Ошибка сохранения. Попробуйте позже.';
            }
        }
    }
}

// D5: Организация референс-визита (все)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d5_action'])) {
    $company = trim($_POST['company']     ?? '');
    $about   = trim($_POST['about']       ?? '');
    $show    = trim($_POST['what_show']   ?? '');
    $audience= trim($_POST['audience']    ?? '');
    $fn      = trim($_POST['d5_first_name']?? '');
    $ln      = trim($_POST['d5_last_name'] ?? '');
    $em      = trim($_POST['d5_email']     ?? '');
    if (!$company || !$fn || !$em) {
        $d5Error = 'Заполните обязательные поля: Компания, Имя, Email.';
    } else {
        $saved = $hlOk ? po_hlSave('reference_org', $USER->IsAuthorized() ? $USER->GetID() : 0, [
            'company'    => $company, 'about'     => $about,
            'what_show'  => $show,   'audience'  => $audience,
            'last_name'  => $ln,     'first_name'=> $fn,
            'email'      => $em,     'phone'     => trim($_POST['d5_phone'] ?? ''),
            'site'       => trim($_POST['d5_site']  ?? ''),
        ]) : false;
        if (!$hlOk || $saved) {
            $d5Done = true;
            po_logAction('form_submit', 'application', 0, 'D5 организация референс-визита');
            $d5Data = [
                'first_name' => $fn,      'last_name' => $ln,
                'email'      => $em,      'phone'     => trim($_POST['d5_phone'] ?? ''),
                'company'    => $company, 'about'     => $about,
                'what_show'  => $show,    'audience'  => $audience,
            ];
            po_sendAdminEmail('reference_org', $d5Data);
            po_createCrmLead('reference_org', $d5Data);
        } else {
            $d5Error = 'Ошибка сохранения. Попробуйте позже.';
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
                                Референс- визиты Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана
                            </h1>
                            <div class="banner-other__list">
                                <div class="banner-other__item">
                                    <h2>
                                        Референс-визит
                                    </h2>
                                    <p>
                                        Экскурсия по вашему предприятию для потенциальных и действующих клиентов, партнёров, экспертов из области.
                                    </p>
                                </div>
                                <div class="banner-other__item">
                                    <h2>
                                        Формат
                                    </h2>
                                    <p>
                                        До 12 участников смотрят ваше производство, обсуждают технологии и вызовы области.

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
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-img.png" alt="" class="banner-other__image">
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.banner-other -->
        <!-- visits dynamic -->
        <?php
        $iblockRefOk = \Bitrix\Main\Loader::includeModule('iblock');
        $arActiveVisits = [];
        $arPastVisits   = [];
        $today = date('Y-m-d');

        if ($iblockRefOk && defined('IBLOCK_EVENTS_ID') && IBLOCK_EVENTS_ID > 0) {
            $dbRef = CIBlockElement::GetList(
                ['DATE_ACTIVE_TO' => 'DESC'],
                [
                    'IBLOCK_ID' => IBLOCK_EVENTS_ID,
                    'ACTIVE'    => 'Y',
                    'PROPERTY_TYPE' => 'reference',
                ],
                false, false,
                ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', 'DETAIL_PAGE_URL']
            );
            while ($el = $dbRef->GetNext()) {
                $dateTo = $el['DATE_ACTIVE_TO'] ?? '';
                if ($dateTo && strtotime($dateTo) < strtotime($today)) {
                    $arPastVisits[]   = $el;
                } else {
                    $arActiveVisits[] = $el;
                }
            }
        }

        $visitsTab = $_GET['visits_tab'] ?? 'active';
        ?>
        <?php if (!empty($arActiveVisits) || !empty($arPastVisits)): ?>
        <section style="padding:40px 0;background:#f8f8f8">
            <div class="container">
                <h2 class="main-title" style="margin-bottom:24px">Визиты</h2>
                <div style="display:flex;gap:12px;margin-bottom:32px;flex-wrap:wrap">
                    <a href="?visits_tab=active" class="btn <?= $visitsTab !== 'active' ? 'btn-empty' : '' ?>"
                       style="padding:10px 24px">Активные визиты (<?= count($arActiveVisits) ?>)</a>
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
                        $imgSrc = !empty($visit['PREVIEW_PICTURE'])
                            ? CFile::GetPath($visit['PREVIEW_PICTURE'])
                            : SITE_TEMPLATE_PATH . '/assets/img/reference-page/reference-main-img-1.png';
                        $dateFrom = !empty($visit['DATE_ACTIVE_FROM']) ? date('d.m.Y', strtotime($visit['DATE_ACTIVE_FROM'])) : '';
                        $dateTo   = !empty($visit['DATE_ACTIVE_TO'])   ? date('d.m.Y', strtotime($visit['DATE_ACTIVE_TO']))   : '';
                    ?>
                    <div class="visits__card">
                        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($visit['NAME']) ?>" class="visits__image">
                        <div class="visits__content">
                            <div class="visits__date">
                                <div class="visits__date-current">
                                    <?php if ($dateFrom): ?>
                                    <p><span>Дата: </span><?= $dateFrom ?><?= $dateTo && $dateTo !== $dateFrom ? ' — ' . $dateTo : '' ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <h3 class="visits__title"><?= htmlspecialchars($visit['NAME']) ?></h3>
                            <?php if (!empty($visit['PREVIEW_TEXT'])): ?>
                            <p class="visits__text"><?= htmlspecialchars($visit['PREVIEW_TEXT']) ?></p>
                            <?php endif; ?>
                            <?php if ($visitsTab === 'active' && $_isMember): ?>
                            <a href="#" class="btn" data-fancybox data-src="#form-d4-modal" style="margin-top:16px">
                                Записаться на визит
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- visits (статичные заглушки, если инфоблок не настроен) -->
        <section class="visits">
            <div class="container">
                <h2 class="main-title visits__title">
                    <?= (empty($arActiveVisits) && empty($arPastVisits)) ? 'Визиты' : 'Ближайшие мероприятия (из шаблона)' ?>
                </h2>
                 <div class="visits__list">
                    <div class="visits__card">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/reference-main-img-1.png" alt="" class="visits__image">
                        <div class="visits__content">
                            <div class="visits__date">
								<div class="visits__date-current">
									<p><span>Дата: </span>5 февраля 2026, 10:30</p>
								</div>
							</div>
                            <h3 class="visits__subtitle">
                                Референс-визит в СЕМАТ
                            </h3>
                            <p class="visits__text">
                               Авиастроение, машиностроение, медицина, приборка, робототехника, электроника — если вы внутри этих процессов, вам точно будет о чём поговорить.
                            </p>
                            <a href="/reference/visit-semat/" class="btn visits__btn btn-transparent">Подробнее</a>
                        </div>
                    </div>
                    <div class="visits__card">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/reference-main-img-2.png" alt="" class="visits__image">
                        <div class="visits__content">
							<div class="visits__date">
								<div class="visits__date-current">
									<p><span>Дата: </span>27 февраля, 10:00</p>
								</div>
							</div>
                            <h3 class="visits__subtitle">
                                Референс-визит в МАШ ЮНИТ
                            </h3>
                            <p class="visits__text">
                               Едем к разработчикам отечественной электроники МАШ ЮНИТ — резиденту Сколково, который собирает ИТ-оборудование от платы до промышленного компьютера.
                            </p>
                            <a href="/reference/visit-mash/" class="btn visits__btn btn-transparent">Подробнее</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.visits -->

        <!-- D4: Участие в референс-визите (только члены) -->
        <?php if ($USER->IsAuthorized()): ?>
        <section class="account" style="padding-top:0">
            <div class="container">
                <div class="account__block" style="max-width:700px;margin:0 auto 60px">
                    <h3 class="account__subtitle">
                        <?= $_isMember ? 'Записаться на референс-визит' : 'Доступно для членов общества' ?>
                    </h3>
                    <?php if ($d4Done): ?>
                        <div class="authorization__alert authorization__alert--success" style="margin-top:16px">
                            <p>Заявка принята! Мы свяжемся с вами для подтверждения.</p>
                        </div>
                    <?php elseif ($_isMember): ?>
                        <?php if ($d4Error): ?>
                        <div class="authorization__alert authorization__alert--error" style="margin-top:16px">
                            <p><?= htmlspecialchars($d4Error) ?></p>
                        </div>
                        <?php endif; ?>
                        <form method="POST" action="/reference/">
                            <input type="hidden" name="d4_action" value="1">
                            <div class="account__personal-list account__grid" style="margin-top:16px">
                                <input type="text"  name="last_name"  placeholder="Фамилия *" required
                                       value="<?= htmlspecialchars($USER->GetParam('LAST_NAME')) ?>">
                                <input type="text"  name="first_name" placeholder="Имя *" required
                                       value="<?= htmlspecialchars($USER->GetParam('NAME')) ?>">
                                <input type="email" name="email"      placeholder="Электропочта *" required
                                       value="<?= htmlspecialchars($USER->GetParam('EMAIL')) ?>">
                                <input type="tel"   name="phone"      placeholder="Телефон">
                                <input type="text"  name="telegram"   placeholder="Telegram">
                            </div>
                            <button type="submit" class="btn authorization__btn" style="margin-top:16px">Подать заявку на участие</button>
                        </form>
                    <?php else: ?>
                        <p style="color:#888;margin-top:12px">
                            Запись на референс-визиты доступна только членам Политехнического общества.
                            <a href="/join/">Вступить</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

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
								Визуализация вашего продукта или услуги в действии
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
								Обратная связь + элемент доверия и прозрачности.
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Профессиональный рост и развитие
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
                               Увеличение узнаваемости бренда, положительный имидж. Развитие бизнеса в долгосрочной перспективе.
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
                                    Эксперты от МГТУ им. Н.Э. Баумана
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
							Программа и затраты
						</h2>
						<p class="main-text">
                            Программа корректируется под вашу специфику, масштабы и свободное время

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
                                Назначить куратора от компании
                            </li>
                            <li>
                                Подготовить помещения, уведомить персонал об экскурсии
                            </li>
                            <li>
                                Провести визит в выбранный день
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
									Сколько раз нужно принимать?
								</h3>
								<p>
									Один раз. Дальше — по желанию. Никаких обязательств.
								</p>
							</div>
							<div class="new-project__card">
								<h3>
									А если у нас коммерческая тайна?
								</h3>
								<p>
									Вы решаете, что показывать, без раскрытия секретов.
								</p>
							</div>
							<div class="new-project__card">
								<h3>
									Сколько времени займёт визит?
								</h3>
                                <p>
                                    От 4 до 8 часов, с учётом ваших ресурсов и возможностей.
                                </p>
							</div>
						</div>
						<div class="swiper-slide new-project__item">
							<div class="new-project__card">
								<h3>
									Это платно?
								</h3>
								<p>
									Нет. Все затраты во время визита к вам — на ваше усмотрение.
								</p>
							</div>
							<div class="new-project__card">
								<h3>
									Кто участники?
								</h3>
								<p>
									Выпускники МГТУ им. Н.Э. Баумана с опытом. Отбираем 8–12 человек под профиль вашей компании.
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
					<input type="text" name="company"   placeholder="Компания *" required>
					<input type="text" name="about"     placeholder="Чем занимается компания?">
					<input type="text" name="what_show" placeholder="Что хотите показать?">
					<input type="text" name="audience"  placeholder="Для какой аудитории?">
				</div>
			</div>
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">Контактное лицо</h3>
				</div>
				<div class="account__personal-list account__grid">
					<input type="text"  name="d5_last_name"  placeholder="Фамилия">
					<input type="text"  name="d5_first_name" placeholder="Имя *" required>
					<input type="tel"   name="d5_phone"      placeholder="Номер телефона">
					<input type="email" name="d5_email"      placeholder="Электропочта *" required>
					<input type="text"  name="d5_site"       placeholder="Сайт">
				</div>
			</div>
			<div class="join__politic">
				<div class="join__politic-question">
					<p class="join__politic-link">Согласен с <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">политикой обработки ПДн</a></p>
					<div class="account__graduate-choice">
						<label class="account__graduate-item">
							<input type="radio" name="d5_agree" value="yes" class="account__graduate-input" required>
							<span class="account__graduate-box"></span>Да
						</label>
						<label class="account__graduate-item">
							<input type="radio" name="d5_agree" value="no" class="account__graduate-input">
							<span class="account__graduate-box"></span>Нет
						</label>
					</div>
				</div>
			</div>
			<button type="submit" class="btn authorization__btn">Отправить</button>
		</form>
		<?php endif; ?>
	</div>
</div>

<?php if ($d5Done): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.Fancybox) Fancybox.show([{src: '#form-reference-visits', type: 'inline'}]);
});
</script>
<?php endif; ?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>