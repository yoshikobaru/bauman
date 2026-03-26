<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поддержать");
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
                               Бауманский университет пилотирует создание Фонда целевого капитала
                            </h1>
							<div class="banner-other__detail">
								<div>
									<p class="details-project__about-discription">
										Команда
									</p>
									<div class="details-project__about-team">
										<div class="details-project__about-who">
											<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/foto-team-1.png" alt="" class="details-project__about-foto">
											<div class="details-project__about-person">
												<p>
													Дима Архипов
												</p>
												<p>
													Менеджер проекта
												</p>
											</div>
										</div>
										<div class="details-project__about-who">
											<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/foto-team-2.png" alt="" class="details-project__about-foto">
											<div class="details-project__about-person">
												<p>
													Алена Артемьева
												</p>
												<p>
													Разработчик
												</p>
											</div>
										</div>
										<div class="details-project__about-who">
											<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/foto-team-3.png" alt="" class="details-project__about-foto">
											<div class="details-project__about-person">
												<p>
													Олег Швец
												</p>
												<p>
													Вдохновитель
												</p>
											</div>
										</div>
									</div>
								</div>
								<div>
									<p class="details-project__about-discription">
										Документация
									</p>
									<div class="details-project__about-document">
										<a href="#" class="details-project__about-download">
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none">
												<path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="white"/>
											</svg>
											document-23553-plan.pdf
										</a>
										<a href="#" class="details-project__about-download">
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none">
												<path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="white"/>
											</svg>
											document-23553-plan.pdf
										</a>
										<a href="#" class="details-project__about-download">
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none">
												<path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="white"/>
											</svg>
											document-23553-plan.pdf
										</a>
									</div>                        
									<p class="details-project__about-discription">
										Ссылки
									</p>
									<div class="details-project__about-links">
										<a href="#" target="_blank">Группа Вконтакте</a>
										<a href="#" target="_blank">Канал с новостями в Телеграм</a>
									</div>
								</div>
								
								
							</div>
                            <!-- <a href="#" class="btn">Поддержать</a> -->
                        </div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-img.png" alt="" class="banner-other__image banner-other__image--current">
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.banner-other -->
        
		 

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
						<div class="project-programm__tabs">
							<ul class="project-programm__navs">
								<li class="main-tabs-click main-tabs-click--active" data-tab="summ">
									Сумма
								</li>
								<li class="main-tabs-click" data-tab="programm">
									Программы
								</li>
								<li class="main-tabs-click" data-tab="data">
									Данные 
								</li>
								<li class="main-tabs-click" data-tab="pay">
									Оплата
								</li>
							</ul>
						</div>
						<div class="project-programm__content">
							<div class="project-programm__item main-tabs-pane main-tabs-pane--active" data-tab="summ">
								<div class="project-programm__item-selector">
									<div class="active">Ежемесячное</div>
									<div>Разовое</div>
								</div>
								<div class="project-programm__item-price">
									<div class="active">300 Р</div>
									<div>500 Р</div>
									<div>1000 Р</div>
									<div>3000 Р</div>
									<div>5000 Р</div>
									<div>10 000 Р</div>
									<div>30 000 Р</div>
									<div>Другая сумма</div>
								</div>
								<div class="project-programm__buttons">
									<button class="btn project-programm__btn">Продолжить</button>
								</div>
							</div>
							<div class="project-programm__item main-tabs-pane" data-tab="programm">
								<div class="project-programm__all">
									<select name="Department" id="">
										<option value="">Список проектов</option>
										<option value="Список проектов">Список проектов</option>
										<option value="Список проектов">Список проектов</option>
										<option value="Список проектов">Список проектов</option>
									</select>
								</div>
								<div class="project-programm__buttons">
									<button class="btn project-programm__btn project-programm__btn--back">Назад</button>
									<button class="btn project-programm__btn">Продолжить</button>
								</div>
							</div>
							<div class="project-programm__item main-tabs-pane" data-tab="data">
								<div class="project-programm__item-selector">
									<div class="main-tabs-click-project main-tabs-click-project--active" data-tab="fiz">Физ. лицо</div>
									<div class="main-tabs-click-project" data-tab="your">Юр. лицо</div>
								</div>
								<div class="main-tabs-pane-project main-tabs-pane-project--active" data-tab="fiz">
									<div class="account__personal">
										<div class="account__chapter">
											<h3 class="account__subtitle">
												Личные данные
											</h3>
										</div>
										<div class="account__personal-list account__grid--tripl">
											<input type="text" placeholder="Фамилия">
											<input type="text" placeholder="Имя">
											<input type="text" placeholder="Дата Рождения">
											<input type="email" placeholder="Електропочта">
											<input type="tel" placeholder="Номер телефона">
										</div>
									</div>
									<div class="join__politic">
										<div class="join__politic-question">
											<p class="join__politic-link">
												Ознакомлен с <a href="#">Уставом</a> и <a href="#">Офертой</a>
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
								</div>
								<div class="main-tabs-pane-project" data-tab="your">
									<div class="account__personal">
										<div class="account__chapter">
											<h3 class="account__subtitle">
												Личные данные
											</h3>
										</div>
										<div class="account__personal-list account__personal-list--project">
											<input type="text" placeholder="Фамилия">
											<input type="text" placeholder="Имя">
											<input type="text" placeholder="отчество">
											<input type="email" placeholder="Електропочта">
											<input type="tel" placeholder="Номер телефона">
											<input type="text" placeholder="Компания">
											<input type="text" placeholder="Сайт">
											<input type="text" placeholder="Сумма">
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
								</div>
								
								<div class="project-programm__buttons">
									<button class="btn project-programm__btn project-programm__btn--back">Назад</button>
									<button class="btn project-programm__btn">Продолжить</button>
								</div>
							</div>
							<div class="project-programm__item main-tabs-pane" data-tab="pay">

							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

        
	</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>