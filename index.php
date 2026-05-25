<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Политехническое Общество Выпускников");
$APPLICATION->SetPageProperty('description', 'Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана — объединение выпускников, партнёров и друзей университета. Проекты, события, карьера, референс-визиты.');
?><main>
<!-- banner-main --> <section class="banner-main">
<div class="banner-main__wrapper">
	<div class="banner-main__content">
		<h1 class="banner-main__title">
		Политехническое Общество Выпускников </h1>
		<p class="banner-main__subtitle">
			 МВТУ (МГТУ) им. Н.Э. Баумана
		</p>
	</div>
	 <!-- /.banner-main__content --> <img src="/local/templates/my_template/assets/img/banner-main.jpg" alt="" class="banner-main__image desk-block"> <img src="/local/templates/my_template/assets/img/banner-main-mob.jpg" alt="" class="banner-main__image desk-none">
	<p class="banner-main__text">
		 Сохраняем связь выпускников способствуем успехам технических наук и промышленности.
	</p>
</div>
 <!-- /.banner-main-wrapper --> </section>
<!-- /.banner-main --> <!-- society --> <section class="society">
<div class="container">
	<div class="society__wrapper">
		<div class="society__discription">
			<h2 class="society__title main-title">
			Об обществе выпускников </h2>
			<p class="society__text main-text">
				 Мы помогаем выпускникам расти и строить карьеру. Даём площадку для общения и обмена опытом. Создаём Общество, частью которого&nbsp;престижно быть.
			</p>
		</div>
		<div class="society__slider swiper">
			<div class="swiper-wrapper">
				<div class="swiper-slide society__flex">
					<div class="society__item">
						<h3>
						Развитие </h3>
						<p>
							 Каждый год выпускаем 5000 специалистов. <br>
							 Выпускники повышают престиж&nbsp;Бауманки и вносят вклад в жизнь общества.
						</p>
 <img src="/local/templates/my_template/assets/img/society-icon-1.png" alt="">
					</div>
					<div class="society__item">
						<h3>
						Поддержка </h3>
						<p>
							 Мы готовы помочь советом, ресурсами и связями на всех этапах карьеры.
						</p>
 <img src="/local/templates/my_template/assets/img/society-icon-2.png" alt="">
					</div>
					<div class="society__item">
						<h3>
						Цель </h3>
						<p>
							 Объединяем усилия для решения амбициозных задач, повышение престижа профессии, развития родного Университета.
						</p>
 <img src="/local/templates/my_template/assets/img/society-icon-3.png" alt="">
					</div>
				</div>
				<div class="swiper-slide society__flex">
					<div class="society__item">
						<h3>
						Обмен <br>
						опытом </h3>
						<p>
							 Задавайте вопросы и делитесь своими успехами — знания и опыт бауманцев бесценны.
						</p>
 <img src="/local/templates/my_template/assets/img/society-icon-4.png" alt="">
					</div>
					<div class="society__item">
						<h3>
						Научный <br>
						суверенитет </h3>
						<p>
							 Поддерживаем отечественные технологии и инновации, укрепляя будущее России.
						</p>
 <img src="/local/templates/my_template/assets/img/society-icon-5.png" alt="">
					</div>
					<div class="society__item">
						<h3>
						Активность </h3>
						<p>
							 Создаём проекты, мероприятия и инициативы — вклад каждого важен
						</p>
 <img src="/local/templates/my_template/assets/img/society-icon-6.png" alt="">
					</div>
				</div>
			</div>
			<div class="swiper-pagination">
			</div>
		</div>
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.society --> <!-- opportunities --> <section class="opportunities">
<div class="container">
	<div class="opportunities__wrapper">
		<div class="opportunities__missions">
			<h3>
			Миссия </h3>
			<p>
				 Поддерживать связь выпускников МГТУ им. Н.Э. Баумана, содействовать развитию технического образования, науки и промышленности Российской федерации.
			</p>
		</div>
		<div class="opportunities__target">
			<h3>
			Цели </h3>
			<h4>
			Повышение престижа </h4>
			<p>
				 Повышать престиж инженерных специальностей.
			</p>
			<h4>
			Развитие науки и промышленности </h4>
			<p>
				 Способствовать научно-техническому прогрессу, ориентированному на потребности российской промышленности.
			</p>
			<h4>
			Практическая подготовка кадров </h4>
			<p>
				 Сокращать разрыв между учебными программами и требованиями современной индустрии к инженерам.
			</p>
		</div>
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.opportunities -->

<!-- Рендерер: Члены Совета (с fancybox для модальных окон) -->
<?php po_render_board_section('Члены Совета Политехнического общества', 12, true); ?>

<!-- Рендерер: Модальные окна членов Совета (для fancybox) -->
<?php po_render_board_modals(); ?>

<!-- /.boards -->

<!-- initiative -->

<!-- Рендерер: Проекты общества -->
<?php po_render_projects_section(); ?>

<!-- /.initiative -->

<!-- history -->
<div class="container">
	<h2 class="main-title history__title">
	С 19 века создаем сеть поддержки и обеспечиваем стабильность и рост общества </h2>
	<div class="history__scroll">
		<div class="history__size">
 <img src="/local/templates/my_template/assets/img/line.png" alt="">
			<div class="history__list">
				<div class="history__item">
					<h3>1874</h3>
					<p>
						Ходатайство об учреждении при ИМТУ Общества учёных-техников
					</p>
				</div>
				<div class="history__item">
					<h3>1877</h3>
					<p>
						Первый устав общества утверждён императором Александром II
					</p>
				</div>
				<div class="history__item">
					<h3>1905</h3>
					<p>
						«Вестник Политехнического Общества»
					</p>
				</div>
				<div class="history__item">
					<h3>1907</h3>
					<p>
						Первое собрание членов в собственном доме
					</p>
				</div>
				<div class="history__item">
					<h3>1991</h3>
					<p>
						Зарегистрирована общественная организация <br>
						«МВТУ-Политех»
					</p>
				</div>
				<div class="history__item">
					<h3>2024</h3>
					<p>
						Обновление <br>
						 общества
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.history --> <!-- culture --> <section class="culture">
<div class="container">
	<h2 class="main-title culture__title">
	Мы — Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана </h2>
	<p class="main-text culture__text">
		 Члены общества сами формируют культуру мощного объединения с широкими возможностями
	</p>
	<div class="culture__wrapper">
		<div class="culture__card culture__card--big">
			<div class="culture__card-overlay">
				<h3>
				Более 20 мероприятий ежегодно </h3>
				<ul class="culture__list">
					<li class="culture__item">
					Ежегодная встреча «Сила сообщества» </li>
					<li class="culture__item">
					Карта возможностей </li>
					<li class="culture__item">
					Конференции (встречи с экспертами, мастер-классы, тренинги) </li>
					<li class="culture__item">
					Референс-визиты на предприятия </li>
					<li class="culture__item">
					Мастермайнды </li>
					<li class="culture__item">
					Клубы по интересам </li>
				</ul>
			</div>
 <img src="/local/templates/my_template/assets/img/culture-bg-card.png" alt="" class="culture__card-image desk-block">
		</div>
		<div class="culture__box">
			<div class="culture__card">
				<h3>
				Около 200 000 выпускников </h3>
				<p>
					 За 185 лет существования университета
				</p>
			</div>
			<div class="culture__card">
				<h3>
				Более 40 академиков </h3>
				<p>
					 РАН и других академий
				</p>
			</div>
			<div class="culture__card">
				<h3>
				Более 100 лауреатов </h3>
				<p>
					 Ленинской, Государственной, других престижных премий
				</p>
			</div>
			<div class="culture__card">
				<h3>
				Представители во всех областях </h3>
				<p>
					 промышленности России
				</p>
			</div>
		</div>
	</div>
</div>
 <!-- /.conttainer --> </section>
<!-- /.culture --> <!-- new-project --> <section class="new-project">
<div class="container">
	<h2 class="main-title new-project__title">
	Новые проекты и возможности </h2>
	<p class="main-text new-project__text">
		 Выпускники играют ключевую роль в формировании репутации и будущего университета.
	</p>
	<div class="new-project__slider swiper">
		<div class="swiper-wrapper">
			<div class="swiper-slide new-project__item">
 <a href="/resume-form/" class="new-project__card" style="text-decoration:none;color:inherit;display:block;">
				<h3>
				Карьерная платформа </h3>
				<p>
					 Встречи с представителями индустрии
				</p>
 </a> <a href="/competencies/" class="new-project__card" style="text-decoration:none;color:inherit;display:block;">
				<h3>
				Витрина компетенций </h3>
				<p>
					 Практика на основе исследований
				</p>
 </a> <a href="/about/#history" class="new-project__card new-project__card--different" style="text-decoration:none;color:#fff;display:block;"> <img src="/local/templates/my_template/assets/img/new-project-bg.png" alt="">
				<h3>
				История и крепкое сообщество </h3>
 </a>
			</div>
		</div>
		<div class="swiper-pagination">
		</div>
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.new-project --> <!-- membership --> <section class="membership">
<div class="container">
	<h2 class="main-title membership__title">
	Виды членства </h2>
	<p class="main-text membership__text">
		 В зависимости от варианта участия члены общества получают определённые возможности и преференции, приведённые в таблице ниже.
	</p>
	<div class="membership-slider swiper">
		<div class="swiper-wrapper">
			<div class="swiper-slide membership-slider__card">
				<h3 class="membership-slider__title">
				Базовое </h3>
				 <!-- <p class="membership-slider__name">1 000 Р</p>
							<p class="membership-slider__time">
								ежегодно
							</p> -->
				<ul class="membership-slider__list">
					<li class="membership-slider__item">Участие в активностях, выставках и мероприятиях Политехнического общества;</li>
					<li class="membership-slider__item">Доступ в закрытый карьерный канал с вакансиями от профильных компаний;</li>
					<li class="membership-slider__item">Возможность получить пластиковый пропуск члена Политехнического общества для посещения МГТУ им. Н.Э. Баумана;</li>
					<li class="membership-slider__item">Доступ в электронную библиотеку МГТУ им Н.Э. Баумана.</li>
				</ul>
				 <!-- <a href="/join/" class="membership-slider__join btn btn-empty">Вступить</a> --> <button class="membership-slider__join btn btn-empty" onclick="window.location='/registration/'">Вступить</button>
			</div>
			<div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
				<h3 class="membership-slider__title">
				Профессиональное </h3>
				 <!-- <p class="membership-slider__name">50 000 Р</p>
							<p class="membership-slider__time">
								ежегодно
							</p> --> <button class="membership-slider__advantages">+ Возможности Базового</button>
				<ul class="membership-slider__list">
					<li class="membership-slider__item">Участие в бизнес-мероприятиях Политехнического общества в онлайн и очном форматах;</li>
					<li class="membership-slider__item">Возможность предложить собственный проект для поиска спонсоров и поддержки Политехнического общества;</li>
					<li class="membership-slider__item">Возможность участвовать в референс-визитах, организуемых Политехническим обществом;</li>
					<li class="membership-slider__item">Доступ к базе резюме выпускников на карьерной платформе Политехнического общества;</li>
					<li class="membership-slider__item">Участие в закрытом чате членов общества уровня «Бизнес».</li>
				</ul>
				 <!-- <a href="/join/" class="membership-slider__join btn btn-empty">Вступить</a> --> <button class="membership-slider__join btn btn-empty" onclick="window.location='/registration/'">Вступить</button>
			</div>
			<div class="swiper-slide membership-slider__card membership-slider__card--honorary">
				<h3 class="membership-slider__title">
				Партнёрское </h3>
				<p class="membership-slider__name membership-slider__name--small">
					Персональные условия
				</p>
				 <!-- <p class="membership-slider__time">
								обсуждается индивидуально
							</p> --> <button class="membership-slider__advantages">+ Возможности профессионального</button>
				<ul class="membership-slider__list">
					<li class="membership-slider__item">
					Участие в закрытых мероприятиях Политехнического общества; </li>
					<li class="membership-slider__item">
					Право стать членом Совета Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана; </li>
					<li class="membership-slider__item">
					Участие в закрытом чате партнёров Политехнического общества. </li>
				</ul>
				 <!-- <a href="/join/" class="membership-slider__join btn btn-empty">Вступить</a> --> <button class="membership-slider__join btn btn-empty" onclick="window.location='/registration/'">Вступить</button>
			</div>
			<div class="swiper-slide membership-slider__card membership-slider__card--gratuitous">
				<h3 class="membership-slider__title">
				Почётное </h3>
				 <!-- <p class="membership-slider__name">Бесценно</p>
							<p class="membership-slider__time">
								по результатам заполненной анкеты
							</p> --> <button class="membership-slider__advantages">+ Возможности Базового</button>
				<ul class="membership-slider__list">
					<li class="membership-slider__item">
					Для тех, кто внёс значительный вклад в развитие технической науки, образования, технологий и деятельности Политехнического общества. </li>
				</ul>
				 <!-- <a href="/join/" class="membership-slider__join btn btn-empty">Вступить</a> --> <button class="membership-slider__join btn btn-empty" onclick="window.location='/registration/'">Вступить</button>
			</div>
		</div>
		<div class="swiper-pagination">
		</div>
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.membership --> <!-- partner --> <section class="partner">
<div class="container">
	<div class="partner__wrapper">
		<div class="partner__info">
			<h2 class="main-title partner__title">Индустриальное партнерство</h2>
			<p class="main-text partner__text">
				Для юридических лиц
			</p>
 <button class="btn partner__btn desk-block" data-fancybox data-src="#join-ur-block-main">Стать партнером</button>
		</div>
		<div class="partner__discription">
			<ul class="partner__list">
				<li class="partner__item">Все преимущества базового и бизнес членства;</li>
				<li class="partner__item">Возможность разместить свою компанию на витрине компетенций Политехнического общества (в разработке);</li>
				<li class="partner__item">Рекламные возможности площадок и мероприятий Политехнического общества.</li>
			</ul>
			<p class="partner__discription-text">
				Стоимость обсуждается индивидуально.
			</p>
		</div>
 <button class="btn partner__btn desk-none" data-fancybox data-src="#join-ur-block-main">Стать партнером</button>
	</div>
</div>
 </section>
<!-- /.partner -->

<!-- partner form popup on main -->
<div id="join-ur-block-main" class="form-partnership-popup" style="display:none;">
		<h2 class="account__title main-title">Индустриальное партнёрство</h2>
		<p class="form-partnership-popup__subtitle">
			Для компаний, НИИ и организаций. После отправки заявки мы свяжемся с вами в течение 5 рабочих дней.
		</p>
		<?php
			po_render_industrial_partnership_form([
				'prefix'      => 'd7',
				'action'      => '/join/#join-ur-block',
				'hidden_name' => 'd7_action',
				'post'        => [],
			]);
		?>
	</div>
<!-- /partner form popup on main -->

<!-- Рендерер: Новости и события -->
<?php po_render_news_section(); ?>

<!-- /.news -->

</main>
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
</script><?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>