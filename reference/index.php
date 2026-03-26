<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Референс-визиты");
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
        <!-- visits -->
        <section class="visits">
            <div class="container">
                <h2 class="main-title visits__title">
                    Визиты
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
                            <a href="details-project.html" target="_blank" class="btn visits__btn btn-transparent">Подробнее</a>
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
                            <a href="details-project.html" target="_blank" class="btn visits__btn btn-transparent">Подробнее</a>
                        </div>
                    </div>
                </div>
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
			<h2 class="account__title main-title">Заявка на организацию референс-визита</h2>
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">
						Данные о компании
					</h3>
				</div>
				<div class="account__personal-list account__personal-list--form">
					<input type="text" placeholder=" Компания">
					<input type="text" placeholder="Чем занимается компания?">
					<input type="text" placeholder="Что хотите показать?">
					<input type="text" placeholder="Для какой аудитории?">
					
				</div>
			</div>
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">
						Образование
					</h3>
				</div>
				<div class="account__personal-list account__personal-list account__grid">
					<input type="text" placeholder="Фамилия">
					<input type="text" placeholder="Имя">
					<input type="text" placeholder="Отчество">
					<input type="tel" placeholder="Номер телефона">
					<input type="email" placeholder="Електропочта">
					<input type="text" placeholder="Сайт">
				</div>
			</div>
			
			<div class="join__politic">
				<div class="join__politic-question">
					<p class="join__politic-link">
						Согласен с <a href="#">политикой обработки ПДн</a>
					</p>
					<div class="account__graduate-choice">
						<label class="account__graduate-item">
							<input type="radio" name="subscribe" value="yes" class="account__graduate-input">
							<span class="account__graduate-box"></span>
							Да
						</label>

						<label class="account__graduate-item">
							<input type="radio" name="subscribe" value="no" class="account__graduate-input">
							<span class="account__graduate-box"></span>
							Нет
						</label>
					</div>
				</div>                        
			</div>
			<button class="btn authorization__btn">Отправить</button>
		</div>
	</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>