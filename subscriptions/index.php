<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Вступить");
?>

<main>
		<!-- culture -->
		<section class="culture culture--subtitles">
			<div class="container">
				<h2 class="main-title culture__title">
					Новые резиденты принимают эстафету лидерства и продолжают традиции успеха  
				</h2>
				<div class="culture__wrapper">
					<div class="culture__box">
						<div class="culture__card">
							<h3>
								Крупные предприятия и организации
							</h3>
							<p>
								Внесшие значительный вклад в развитие Общества.
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Люди, оказавшие важные услуги
							</h3>
							<p>
								Развитию технического образования в России.
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Учёные, прославившиеся трудами
							</h3>
							<p>
								В технической литературе.
							</p>
						</div>
						<div class="culture__card">
							<h3>
								Активные участники добровольческих проектов
							</h3>
							<p>
								Общества социальной направленности
							</p>
						</div>
					</div>
                    <div class="culture__card culture__card--big culture__card--man">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/subscriptions-page/culture-bg-card.png" alt="" class="culture__card-image">
                        <div class="culture__card-overlay">
                            <h3>
                                Выпускники МГТУ (МВТУ) 
                            </h3>
                            <p>
                                и его филиалов
                            </p>
                        </div>						
					</div>
				</div>
			</div>
			<!-- /.container -->
		</section>
		<!-- /.culture -->
        <!-- membership -->
		<section class="membership">
			<div class="container">
				<h2 class="main-title membership__title">
					Новые резиденты укрепляют связи внутри сообщества и способствуют его развитию
				</h2>
				<div class="membership-slider swiper">
					<div class="membership-slider swiper">
					<div class="swiper-wrapper">
						<div class="swiper-slide membership-slider__card">
							<h3 class="membership-slider__title">
								Базовое
							</h3>
							<p class="membership-slider__name">
								5 000 Р
							</p>
							<p class="membership-slider__time">
								ежегодно
							</p>
							<ul class="membership-slider__list">
								<li class="membership-slider__item">
									Возможность размещения резюме на карьерной платформе Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Доступ в закрытый карьерный канал с вакансиями от профильных компаний;
								</li>
								<li class="membership-slider__item">
									Участие в активностях, выставках и мероприятиях Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Доступ в электронную библиотеку МГТУ (в разработке);
								</li>
								<li class="membership-slider__item">
									Доступ к витрине компетенций партнёров Политехнического общества, кафедр, студенческих конструкторских бюро и научно-образовательных центров МГТУ.
								</li>
							</ul>
							<button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership">Вступить</button>
						</div>
						<div class="swiper-slide membership-slider__card membership-slider__card--proffesional">
							<h3 class="membership-slider__title">
								Профессиональное
							</h3>
							<p class="membership-slider__name">
								50 000 Р
							</p>
							<p class="membership-slider__time">
								ежегодно
							</p>
							<button class="membership-slider__advantages">+ Возможности Базового</button>
							<ul class="membership-slider__list">
								<li class="membership-slider__item">
									Участие в закрытом чате членов общества уровня «Бизнес»;
								</li>
								<li class="membership-slider__item">
									Размещение информации и новостей о компании на площадках Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Возможность предложить собственный проект для поиска спонсоров и поддержки Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Участие в бизнес-мероприятиях Политехнического общества в онлайн и очном форматах;
								</li>
								<li class="membership-slider__item">
									Доступ к базе резюме выпускников на карьерной платформе Политехнического общества.
								</li>
							</ul>
							<button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership">Вступить</button>
						</div>
						<div class="swiper-slide membership-slider__card membership-slider__card--honorary">
							<h3 class="membership-slider__title">
								Партнёрское
							</h3>
							<p class="membership-slider__name membership-slider__name--small">
								Индивидуальные условия
							</p>
							<p class="membership-slider__time">
								обсуждается индивидуально
							</p>
							<button class="membership-slider__advantages">+ Возможности профессионального</button>
							<ul class="membership-slider__list">
								<li class="membership-slider__item">
									Участие в закрытых мероприятиях Политехнического общества;
								</li>
								<li class="membership-slider__item">
									Право стать членом правления Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана;
								</li>
								<li class="membership-slider__item">
									Участие в закрытом чате почётных членов Политехнического общества.
								</li>
							</ul>
							<button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership">Вступить</button>
						</div>
						<div class="swiper-slide membership-slider__card membership-slider__card--gratuitous">
							<h3 class="membership-slider__title">
								Почётное
							</h3>
							<p class="membership-slider__name">
								Бесценно
							</p>
							<p class="membership-slider__time">
								по результатам заполненной анкеты
							</p>
							<button class="membership-slider__advantages">+ Возможности Базового</button>
							<ul class="membership-slider__list">
								<li class="membership-slider__item">
									Для тех, кто внёс значительный вклад в развитие технической науки, образования, технологий и деятельности Политехнического общества.
								</li>
							</ul>
							<button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership">Вступить</button>
						</div>
					</div>
					<div class="swiper-pagination"></div>
				</div>
			</div>
			<!-- /.container -->
		</section>
		<!-- /.membership -->

        <section class="partner">
            <div class="container">
                <div class="partner__wrapper">
                    <div class="partner__info">
                        <h2 class="main-title partner__title">
                            Индустриальное партнерство
                        </h2>
                        <p class="main-text partner__text">
                            Для юридических лиц
                        </p>
                        <button class="btn partner__btn desk-block">Стать партнером</button>
                    </div>
                    <div class="partner__discription">
                        <ul class="partner__list">
                            <li class="partner__item">
                                Все преимущества базового и бизнес членства
                            </li>
                            <li class="partner__item">
                                Возможность состоять в индустриальном клубе Политехнического общества
                            </li>
                            <li class="partner__item">
                                Доступ к витрине компетенций, возможность разместить заказ/взять задачу
                            </li>
                            <li class="partner__item">
                                Рекламные возможности площадок и мероприятий Политехнического общества
                            </li>
                        </ul>
                        <p class="partner__discription-text">
                            Стоимость обсуждается индивидуально. 
                        </p>
                    </div>
                    <button class="btn partner__btn desk-none">Стать партнером</button>
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.partner -->
	</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>