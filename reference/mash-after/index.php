<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Референс-визит в МАШ ЮНИТ — итоги");
?>

<main>
        <!-- banner-other -->
		<section class="banner-other">
            <div class="container">
                <div class="banner-other__wrapper">
                    <div class="banner-other__content">
                        <div class="banner-other__info">
                            <div class="banner-other__date banner-other__date--column">
                                <p class="banner-other__status">Активный</p>
                                <p class="banner-other__time">27 февраля, 10:00</p>
                                <p class="banner-other__time">Технопарк Отрадное</p>                                
                                <p class="banner-other__time">2–2,5 часа инженерного реализма</p>
                            </div>
                            <h1 class="banner-other__title main-title">
                               Референс-визит в МАШ ЮНИТ
                            </h1>
                            <p class="banner-other__text main-text">
                                Едем к разработчикам отечественной электроники МАШ ЮНИТ — резиденту Сколково, который собирает ИТ-оборудование от платы до промышленного компьютера.
                            </p>
                            <p class="banner-other__text main-text">
                                Участвуют <strong>выпускники Бауманки.</strong> Дальше такие визиты будут доступны только членам Политехнического общества выпускников.
                            </p>
                            <p class="banner-other__text main-text">
                               Регистрация до 22 февраля. Мест немного — формат камерный.
                            </p>
                            <a href="#" class="banner-other__btn btn" data-fancybox data-src="#form-cemat">Зарегистрироваться</a>
                        </div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/reference-cemat-mash.png" alt="" class="banner-other__image">
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.banner-other -->
        
        

        <section class="project-gallery">
            <div class="container">
                <div class="project-gallery__arrows">
                    <h2 class="main-title project-gallery__title">
                        Фото с мероприятия
                    </h2>
                    <div class="project-gallery__arrows-row">
                        <div class="gallery-button-prev">
                            <svg width="12" height="22" viewBox="0 0 12 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11 1L1 11L11 21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="gallery-button-next">
                            <svg width="12" height="22" viewBox="0 0 12 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 21L11 11L1 1" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper project-gallery__swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/reference-gallery-img-1.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/reference-gallery-img-2.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/reference-gallery-img-3.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/reference-gallery-img-4.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/reference-gallery-img-1.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/reference-gallery-img-2.png" alt=""></div>
                </div>
            </div>
        </section>

        
	</main>
<div class="form-cemat" id="form-cemat" style="display:none;">
		<div class="join__wrapper">
			<h2 class="account__title main-title">Заявка на участие в референс-визите</h2>
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">
						Данные об участнике
					</h3>
				</div>
				<div class="account__personal-list join__grid join__grid--cemat">
					<input type="text" placeholder=" Имя">
					<input type="text" placeholder="Фамилия">
					<input type="email" placeholder="e-mail">
					<input type="tel" placeholder="Номер телефона">
					<input type="text" placeholder="Telegram">
					
				</div>
			</div>
			
			<button class="btn authorization__btn">Отправить</button>
		</div>
	</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>