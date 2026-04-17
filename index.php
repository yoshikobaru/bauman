<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Политехническое Общество Выпускников");
$APPLICATION->SetPageProperty('description', 'Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана — объединение выпускников, партнёров и друзей университета. Проекты, события, карьера, референс-визиты.');
?>

	<main>
		<!-- banner-main -->
		<section class="banner-main">
				<div class="banner-main__wrapper">
					<div class="banner-main__content">
						<h1 class="banner-main__title">
							Политехническое Общество Выпускников
						</h1>
						<p class="banner-main__subtitle">
							МВТУ (МГТУ) им. Н.Э. Баумана
						</p>
					</div>
					<!-- /.banner-main__content -->
					<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/banner-main.jpg" alt="" class="banner-main__image desk-block" >
					<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/banner-main-mob.jpg" alt="" class="banner-main__image desk-none" >
					<p class="banner-main__text">
						Сохраняем связь выпускников способствуем успехам технических наук и промышленности.
					</p>
				</div>
				<!-- /.banner-main-wrapper -->
		</section>
		<!-- /.banner-main -->
		<!-- society -->
		<section class="society">
			<div class="container">
				<div class="society__wrapper">
					<div class="society__discription">
						<h2 class="society__title main-title">
							Об обществе выпускников
						</h2>
						<p class="society__text main-text">
							Мы помогаем выпускникам расти и строить карьеру. Даём площадку для общения и обмена опытом. Создаём Общество, частью которого&nbsp;престижно быть.
						</p>
					</div>
					<div class="society__slider swiper">
						<div class="swiper-wrapper">
							<div class="swiper-slide society__flex">
								<div class="society__item">
									<h3>
										Развитие
									</h3>
									<p>
										Каждый год выпускаем 5000 специалистов. <br> Выпускники повышают престиж&nbsp;Бауманки и вносят вклад в жизнь общества.
									</p>
									<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/society-icon-1.png" alt="" >
								</div>
								<div class="society__item">
									<h3>
										Поддержка
									</h3>
									<p>
										Мы готовы помочь советом, ресурсами и связями на всех этапах карьеры.
									</p>
									<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/society-icon-2.png" alt="" >
								</div>
								<div class="society__item">
									<h3>
										Цель
									</h3>
									<p>
										Объединяем усилия для решения амбициозных задач, повышение престижа профессии, развития родного Университета.
									</p>
									<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/society-icon-3.png" alt="" >
								</div>
							</div>
							<div class="swiper-slide society__flex">
								<div class="society__item">
									<h3>
										Обмен <br>опытом
									</h3>
									<p>
										Задавайте вопросы и делитесь своими успехами — знания и опыт бауманцев бесценны.
									</p>
									<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/society-icon-4.png" alt="" >
								</div>
								<div class="society__item">
									<h3>
										Научный <br>суверенитет
									</h3>
									<p>
										Поддерживаем отечественные технологии и инновации, укрепляя будущее России.
									</p>
									<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/society-icon-5.png" alt="" >
								</div>
								<div class="society__item">
									<h3>
										Активность
									</h3>
									<p>
										Создаём проекты, мероприятия и инициативы — вклад каждого важен
									</p>
									<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/society-icon-6.png" alt="" >
								</div>
							</div>
							
						</div>
						<div class="swiper-pagination"></div>
					</div>
				</div>
			</div>
			<!-- /.container -->
		</section>
		<!-- /.society -->
		<!-- opportunities -->
		<section class="opportunities">
			<div class="container">
				<div class="opportunities__wrapper">
					<div class="opportunities__missions">
						<h3>
							Миссия
						</h3>
						<p>
							Поддерживать связь выпускников МВТУ (МГТУ) им. Н.Э. Баумана, содействовать развитию технического образования, науки и промышленности Российской федерации.
						</p>
					</div>
					<div class="opportunities__target">
						<h3>
							Цели
						</h3>
						<h4>
							Повышение престижа
						</h4>
						<p>
							Повышать престиж инженерных специальностей.
						</p>
						<h4>
							Развитие науки и промышленности
						</h4>
						<p>
							Способствовать научно-техническому прогрессу, ориентированному на потребности российской промышленности.
						</p>
						<h4>
							Практическая подготовка кадров
						</h4>
						<p>
							Сокращать разрыв между учебными программами и требованиями современной индустрии к инженерам.
						</p>
					</div>
				</div>
			</div>
			<!-- /.container -->
		</section>
		<!-- /.opportunities -->
<?php
$_boardItems = [];
if (defined('IBLOCK_BOARD_ID') && IBLOCK_BOARD_ID > 0 && \Bitrix\Main\Loader::includeModule('iblock')) {
    $dbBoard = CIBlockElement::GetList(
        ['SORT' => 'ASC'],
        ['IBLOCK_ID' => IBLOCK_BOARD_ID, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 12],
        ['ID', 'NAME', 'PREVIEW_PICTURE', 'PREVIEW_TEXT']
    );

    while ($board = $dbBoard->GetNext()) {
        $_boardItems[] = [
            'name' => (string)$board['NAME'],
            'pos' => (string)$board['PREVIEW_TEXT'],
            'photo' => !empty($board['PREVIEW_PICTURE']) ? CFile::GetPath($board['PREVIEW_PICTURE']) : '',
        ];
    }
}
?>
<?php if (!empty($_boardItems)): ?>
		 <section class="boards">
			<div class="container">
				<div class="boards__wrapper">
					<h2 class="main-title">
						Члены Совета Политехнического общества
					</h2>
				<div class="boards__list">
<?php foreach ($_boardItems as $boardItem): ?>
						<div class="boards__item">
							<?php if (!empty($boardItem['photo'])): ?>
							<img src="<?= htmlspecialchars($boardItem['photo']) ?>" alt="<?= htmlspecialchars($boardItem['name']) ?>" class="boards__item-image">
							<?php endif; ?>
							<h3 class="boards__item-title">
								<?= htmlspecialchars($boardItem['name']) ?>
							</h3>
							<p class="boards__item-text">
								<?= htmlspecialchars($boardItem['pos']) ?>
							</p>
						</div>
<?php endforeach; ?>
				</div>
				</div>
			</div>
			<!-- /.container -->
		 </section>
<?php endif; ?>
		 <!-- /.boards -->
		<!-- initiative -->
		<section class="initiative">
			<div class="container">
				<div class="initiative__wrapper">
					<div class="initiative__info">
						<h2 class="main-title">
							Проекты сообщества
						</h2>
						<p class="main-text">
							Наши резиденты запустили важные проекты Политеха. Станьте частью братства и используйте все возможности сообщества.
						</p>
					</div>
<?php
// Аварийный fallback — только если инфоблок проектов пуст/недоступен.
$_fallbackProjects = [
    ['name' => 'Конференция PolytechExpo',        'url' => '/projects/politech-expo/', 'img' => 'initiative-img-1.png', 'mob' => 'initiative-img-mob-1.png'],
    ['name' => 'Конференция Встреча выпускников', 'url' => '/projects/conference/',    'img' => 'initiative-img-2.png', 'mob' => 'initiative-img-mob-2.png'],
    ['name' => 'Попечительский совет',            'url' => '/projects/trustees/',      'img' => 'initiative-img-3.png', 'mob' => 'initiative-img-mob-3.png'],
    ['name' => 'Реставрации Ротонды',             'url' => '/projects/restoration/',   'img' => 'initiative-img-4.png', 'mob' => 'initiative-img-mob-4.png'],
];

$_projects = [];
$_localDesktopFallback = ['initiative-img-1.png', 'initiative-img-2.png', 'initiative-img-3.png', 'initiative-img-4.png'];
$_localMobileFallback  = ['initiative-img-mob-1.png', 'initiative-img-mob-2.png', 'initiative-img-mob-3.png', 'initiative-img-mob-4.png'];

if (defined('IBLOCK_PROJECTS_ID') && IBLOCK_PROJECTS_ID > 0 && \Bitrix\Main\Loader::includeModule('iblock')) {
    $dbProjects = CIBlockElement::GetList(
        ['SORT' => 'ASC', 'ID' => 'ASC'],
        ['IBLOCK_ID' => IBLOCK_PROJECTS_ID, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PROPERTY_HOME_IMAGE', 'PROPERTY_HOME_IMAGE_MOB', 'PROPERTY_DETAIL_URL']
    );
    $projectIndex = 0;
    while ($proj = $dbProjects->GetNext()) {
        $homeImageId    = (int)($proj['PROPERTY_HOME_IMAGE_VALUE'] ?? 0);
        $homeImageMobId = (int)($proj['PROPERTY_HOME_IMAGE_MOB_VALUE'] ?? 0);
        $previewId      = (int)($proj['PREVIEW_PICTURE'] ?? 0);

        $desktopImage = '';
        foreach ([$homeImageId, $previewId] as $candidateId) {
            if ($candidateId <= 0) {
                continue;
            }
            $candidatePath = CFile::GetPath($candidateId);
            if ($candidatePath) {
                $desktopImage = $candidatePath;
                break;
            }
        }

        $mobileImage = '';
        foreach ([$homeImageMobId, $homeImageId, $previewId] as $candidateId) {
            if ($candidateId <= 0) {
                continue;
            }
            $candidatePath = CFile::GetPath($candidateId);
            if ($candidatePath) {
                $mobileImage = $candidatePath;
                break;
            }
        }

        if ($desktopImage === '') {
            $desktopImage = SITE_TEMPLATE_PATH . '/assets/img/' . $_localDesktopFallback[$projectIndex % count($_localDesktopFallback)];
        }
        if ($mobileImage === '') {
            $mobileImage = SITE_TEMPLATE_PATH . '/assets/img/' . $_localMobileFallback[$projectIndex % count($_localMobileFallback)];
        }

        $detailUrl = trim((string)($proj['PROPERTY_DETAIL_URL_VALUE'] ?? ''));
        if ($detailUrl === '') {
            $detailUrl = trim((string)($proj['DETAIL_PAGE_URL'] ?? ''));
        }
        if ($detailUrl === '') {
            $detailUrl = '/projects/detail/?id=' . (int)$proj['ID'];
        }

        $_projects[] = [
            'name' => (string)$proj['NAME'],
            'url'  => $detailUrl,
            'img'  => $desktopImage,
            'mob'  => $mobileImage,
        ];
        $projectIndex++;
    }
}

if (empty($_projects)) {
    foreach ($_fallbackProjects as $fallbackProject) {
        $_projects[] = [
            'name' => $fallbackProject['name'],
            'url'  => $fallbackProject['url'],
            'img'  => SITE_TEMPLATE_PATH . '/assets/img/' . $fallbackProject['img'],
            'mob'  => SITE_TEMPLATE_PATH . '/assets/img/' . $fallbackProject['mob'],
        ];
    }
}

// Рендерим карточки — структура точно как в верстке (h3 + img без обёрток)
foreach ($_projects as $project):
    $cardUrl = htmlspecialchars($project['url']);
?>
				<div class="initiative__card" style="cursor:pointer;position:relative;" onclick="window.location='<?= $cardUrl ?>'">
					<h3>
						<?= htmlspecialchars($project['name']) ?>
					</h3>
					<img src="<?= htmlspecialchars($project['img']) ?>" alt="<?= htmlspecialchars($project['name']) ?>" class="initiative__image desk-block" />
					<img src="<?= htmlspecialchars($project['mob']) ?>" alt="<?= htmlspecialchars($project['name']) ?>" class="initiative__image desk-none" />
				</div>
<?php endforeach; ?>
				</div>
			</div>
			<!-- /.container -->
		</section>
		<!-- /.initiative -->
		<!-- history -->
		<section class="history">
			<div class="container">
				<h2 class="main-title history__title">
					С 19 века создаем сеть поддержки и обеспечиваем стабильность и рост общества
				</h2>
				<div class="history__scroll">
					<div class="history__size">
						<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/line.png" alt="">
						<div class="history__list">
							<div class="history__item">
								<h3>1874</h3>
								<p>Ходатайство об учреждении при ИМТУ Общества учёных-техников</p>
							</div>
							<div class="history__item">
								<h3>1877</h3>
								<p>Первый устав общества утверждён императором Александром II</p>
							</div>
							<div class="history__item">
								<h3>1905</h3>
								<p>«Вестник Политехнического Общества»</p>
							</div>
							<div class="history__item">
								<h3>1907</h3>
								<p>Первое собрание членов в собственном доме</p>
							</div>
							<div class="history__item">
								<h3>1991</h3>
								<p>Зарегистрирована общественная организация <br>«МВТУ-Политех»</p>
							</div>
							<div class="history__item">
								<h3>2024</h3>
								<p>Обновление <br> общества</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /.container -->
		</section>
		<!-- /.history -->
		<!-- culture -->
		<section class="culture">
			<div class="container">
				<h2 class="main-title culture__title">
					Мы — Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана
				</h2>
				<p class="main-text culture__text">
					Резиденты сами формируют культуру мощного сообщества с широкими возможностями
				</p>
				<div class="culture__wrapper">
					<div class="culture__card culture__card--big">
						<div class="culture__card-overlay">
							<h3>
								Более 20 мероприятий ежегодно
							</h3>
							<ul class="culture__list">
								<li class="culture__item">
									Ежегодная встреча «Сила сообщества»
								</li>
								<li class="culture__item">
									Карта возможностей
								</li>
								<li class="culture__item">
									Конференции (встречи с экспертами, мастер-классы, тренинги) 
								</li>
								<li class="culture__item">
									Референс-визиты на предприятия
								</li>
								<li class="culture__item">
									Мастермайнды
								</li>
								<li class="culture__item">
									Клубы по интересам
								</li>
							</ul>
						</div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/culture-bg-card.png" alt="" class="culture__card-image desk-block">
					</div>
					<div class="culture__box">
						<div class="culture__card">
							<h3>
								Около 200 000 выпускников
							</h3>
							<p>
								За 185 лет существования университета
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Более 40 академиков
							</h3>
							<p>
								РАН и других академий
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Более 100 лауреатов
							</h3>
							<p>
								Ленинской, Государственной, других престижных премий
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Представители во всех областях
							</h3>
							<p>
								промышленности России
							</p>
						</div>
					</div>
				</div>
			</div>
			<!-- /.conttainer -->
		</section>
		<!-- /.culture -->
		<!-- new-project -->
		<section class="new-project">
			<div class="container">
				<h2 class="main-title new-project__title">
					Новые проекты и возможности
				</h2>
				<p class="main-text new-project__text">
					Выпускники играют ключевую роль в формировании репутации и будущего университета.
				</p>
				
				<div class="new-project__slider swiper">
					<div class="swiper-wrapper">
						<div class="swiper-slide new-project__item">
							<a href="/resume-form/" class="new-project__card" style="text-decoration:none;color:inherit;display:block;">
								<h3>
									Карьерная платформа
								</h3>
								<p>
									Встречи с представителями индустрии
								</p>
							</a>
							<a href="/competencies/" class="new-project__card" style="text-decoration:none;color:inherit;display:block;">
								<h3>
									Витрина компетенций
								</h3>
								<p>
									Практика на основе исследований
								</p>
							</a>
						<a href="/about/#history-section" class="new-project__card new-project__card--different" style="text-decoration:none;color:#fff;display:block;">
							<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/new-project-bg.png" alt="">
							<h3>
								История и крепкое сообщество
							</h3>
						</a>
						</div>
						
					</div>
					<div class="swiper-pagination"></div>
				</div>
			</div>
			<!-- /.container -->
		</section>
		<!-- /.new-project -->
		<!-- membership -->
		<section class="membership">
			<div class="container">
				<h2 class="main-title membership__title">
					Виды членства
				</h2>
				<p class="main-text membership__text">
					В зависимости от варианта участия резиденты получают определённые возможности и преференции, приведённые в таблице ниже.
				</p>
				<div class="membership-slider swiper">
					<div class="swiper-wrapper">
						<div class="swiper-slide membership-slider__card">
							<h3 class="membership-slider__title">
								Базовое
							</h3>
							<p class="membership-slider__name">1 000 Р</p>
							<p class="membership-slider__time">
								ежегодно
							</p>
							<ul class="membership-slider__list">
								<li class="membership-slider__item">
									Возможность размещения резюме на карьерной платформе Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Доступ в закрытый карьерный канал с вакансиями от профильных компаний;
								</li>
								<li class="membership-slider__item">
									Участие в активностях, выставках и мероприятиях Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Доступ в электронную библиотеку МВТУ (МГТУ) им. Н.Э. Баумана (в разработке);
								</li>
								<li class="membership-slider__item">
									Доступ к витрине компетенций партнёров Политехнического общества, кафедр, студенческих конструкторских бюро и научно-образовательных центров МВТУ (МГТУ) им. Н.Э. Баумана.
								</li>
							</ul>
						<a href="/join/" class="membership-slider__join btn btn-empty">Вступить</a>
					</div>
					<div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
							<h3 class="membership-slider__title">
								Профессиональное
							</h3>
							<p class="membership-slider__name">50 000 Р</p>
							<p class="membership-slider__time">
								ежегодно
							</p>
							<button class="membership-slider__advantages">+ Возможности Базового</button>
							<ul class="membership-slider__list">
								<li class="membership-slider__item">
									Участие в закрытом чате членов общества уровня «Бизнес»;
								</li>
								<li class="membership-slider__item">
									Размещение информации и новостей о компании на площадках Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Возможность предложить собственный проект для поиска спонсоров и поддержки Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Участие в бизнес-мероприятиях Политехнического общества в онлайн и очном форматах;
								</li>
								<li class="membership-slider__item">
									Доступ к базе резюме выпускников на карьерной платформе Политехнического общества.
								</li>
							</ul>
						<a href="/join/" class="membership-slider__join btn btn-empty">Вступить</a>
					</div>
					<div class="swiper-slide membership-slider__card membership-slider__card--honorary">
							<h3 class="membership-slider__title">
								Партнёрское
							</h3>
							<p class="membership-slider__name membership-slider__name--small">Персональные условия</p>
							<p class="membership-slider__time">
								обсуждается индивидуально
							</p>
							<button class="membership-slider__advantages">+ Возможности профессионального</button>
							<ul class="membership-slider__list">
								<li class="membership-slider__item">
									Участие в закрытых мероприятиях Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Право стать членом Совета Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана;
								</li>
								<li class="membership-slider__item">
									Участие в закрытом чате партнёров Политехнического общества.
								</li>
							</ul>
						<a href="/join/" class="membership-slider__join btn btn-empty">Вступить</a>
					</div>
					<div class="swiper-slide membership-slider__card membership-slider__card--gratuitous">
							<h3 class="membership-slider__title">
								Почётное
							</h3>
							<p class="membership-slider__name">Бесценно</p>
							<p class="membership-slider__time">
								по результатам заполненной анкеты
							</p>
							<button class="membership-slider__advantages">+ Возможности Базового</button>
							<ul class="membership-slider__list">
								<li class="membership-slider__item">
									Для тех, кто внёс значительный вклад в развитие технической науки, образования, технологий и деятельности Политехнического общества.
								</li>
							</ul>
						<a href="/join/" class="membership-slider__join btn btn-empty">Вступить</a>
					</div>
				</div>
				<div class="swiper-pagination"></div>
			</div>
		</div>
		<!-- /.container -->
	</section>
	<!-- /.membership -->

	<!-- partner -->
	<section class="partner">
		<div class="container">
			<div class="partner__wrapper">
				<div class="partner__info">
					<h2 class="main-title partner__title">Индустриальное партнерство</h2>
					<p class="main-text partner__text">Для юридических лиц</p>
					<button class="btn partner__btn desk-block" onclick="showPartnerFormMain()">Стать партнером</button>
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
				<button class="btn partner__btn desk-none" onclick="showPartnerFormMain()">Стать партнером</button>
			</div>
		</div>
	</section>
	<!-- /.partner -->

	<!-- partner form on main -->
	<section id="join-ur-block-main" style="display:none">
		<div class="container" style="padding-top:48px;padding-bottom:48px">
			<div class="join__wrapper">
				<h2 class="account__title main-title">Индустриальное партнёрство</h2>
				<p style="margin-bottom:24px;color:#666">Для компаний, НИИ и организаций. После отправки заявки мы свяжемся с вами в течение 5 рабочих дней.</p>
				<?php
				po_render_industrial_partnership_form([
					'prefix'      => 'd7',
					'action'      => '/join/#join-ur-block',
					'hidden_name' => 'd7_action',
					'post'        => [],
				]);
				?>
			</div>
		</div>
	</section>
	<!-- /partner form on main -->
		<!-- news -->
		<section class="news">
			<div class="container">
				<h2 class="main-title news__title">
					Новости и события
				</h2>
				<div class="news__wrapper">
<?php
if (\Bitrix\Main\Loader::includeModule('iblock')):
    $newsIblockIds = [];
    if (defined('IBLOCK_NEWS_ID')   && IBLOCK_NEWS_ID   > 0) $newsIblockIds[] = IBLOCK_NEWS_ID;
    if (defined('IBLOCK_EVENTS_ID') && IBLOCK_EVENTS_ID > 0) $newsIblockIds[] = IBLOCK_EVENTS_ID;
    if (!empty($newsIblockIds)):
        $dbNews = CIBlockElement::GetList(
            ['DATE_ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'],
            ['IBLOCK_ID' => $newsIblockIds, 'ACTIVE' => 'Y'],
            false,
            ['nTopCount' => 6],
            ['ID', 'NAME', 'DATE_ACTIVE_FROM', 'PREVIEW_PICTURE', 'IBLOCK_ID']
        );
        while ($newsItem = $dbNews->GetNext()):
            $newsImg = $newsItem['PREVIEW_PICTURE']
                ? CFile::GetPath($newsItem['PREVIEW_PICTURE'])
                : SITE_TEMPLATE_PATH . '/assets/img/news-img.png';
            $newsDate = $newsItem['DATE_ACTIVE_FROM']
                ? date('d.m.Y', strtotime($newsItem['DATE_ACTIVE_FROM']))
                : '';
?>
					<a href="/news/detail/?id=<?= (int)$newsItem['ID'] ?>" class="news__card">
						<img src="<?= htmlspecialchars($newsImg) ?>" alt="<?= htmlspecialchars($newsItem['NAME']) ?>">
						<div class="news__content">
							<h3 class="news__card-title">
								<?= htmlspecialchars($newsItem['NAME']) ?>
							</h3>
							<div class="news__row">
								<p class="news__date">
									<?= $newsDate ?>
								</p>
							</div>
						</div>
					</a>
<?php
        endwhile;
    endif;
endif;
?>
				</div>
				<a href="/news/" class="btn news__btn btn-transparent">Все новости</a>
			</div>
			<!-- /.container -->
		</section>
		<!-- /.news -->
	</main>

<script>
function showPartnerFormMain() {
	var block = document.getElementById('join-ur-block-main');
	if (block) {
		block.style.display = '';
		block.scrollIntoView({ behavior: 'smooth', block: 'start' });
	}
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана",
  "url": "https://bauman-polytech.ru",
  "logo": "https://bauman-polytech.ru<?= SITE_TEMPLATE_PATH ?>/assets/img/logo.svg",
  "description": "Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана — объединение выпускников, партнёров и друзей университета.",
  "sameAs": [],
  "contactPoint": {
    "@type": "ContactPoint",
    "contactType": "customer support",
    "email": "info@bauman-polytech.ru"
  },
  "address": {
    "@type": "PostalAddress",
    "addressCountry": "RU",
    "addressLocality": "Москва",
    "streetAddress": "ул. 2-я Бауманская, 5"
  },
  "foundingDate": "2020",
  "parentOrganization": {
    "@type": "EducationalOrganization",
    "name": "МВТУ (МГТУ) им. Н.Э. Баумана",
    "url": "https://bmstu.ru"
  }
}
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
