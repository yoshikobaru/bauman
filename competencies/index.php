<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Витрина компетенций");
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
                            <a href="#" class="banner-other__btn btn" data-fancybox data-src="#form-reference-visits">Стать партнёром</a>
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
<div class="form-competencies" id="form-competencies" style="display:none;max-width:1400px;">
		<div class="join__wrapper">
            <h2 class="account__title main-title">Запрос в Витрине компетенций</h2>
            <div class="account__personal">
                <div class="account__chapter">
                    <h3 class="account__subtitle">
                        Данные представителя
                    </h3>
                </div>
                <div class="account__personal-list account__grid account__personal-list--short">
                    <input type="text" placeholder="Фамилия">
                    <input type="text" placeholder="Имя">
                    <input type="text" placeholder="Отчество">
                    <input type="email" placeholder="Електропочта">
                    <input type="text" placeholder="Телефон">
                    <input type="text" placeholder="Сайт">
                </div>
            </div>
            <div class="account__personal">
                <div class="account__chapter">
                    <h3 class="account__subtitle">
                        Данные о компании
                    </h3>
                </div>
                <div class="account__personal-list account__grid--range">
                    <input type="text" placeholder=" Компания">
                    <input type="text" placeholder="Род деятельности">
                    <textarea name="" placeholder="Описание потребности"></textarea>
                    
                </div>
            </div>
            <div class="account__file">
                <div class="account__file-info">
                    <div class="account__file-content account__photo-content">
                        <label class="account__photo-upload">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none">
                                <path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="black"/>
                            </svg>
                            Перетащите или <span>загрузите файл</span> PDF
                            <input type="file" class="account__photo-input" accept="image/png, image/jpeg">
                        </label>
                    </div>
                </div>                            
            </div>
            <button class="btn">Отправить</button>
        </div>
	</div>
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