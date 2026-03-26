<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Реставрация Ротонды");
?>

<main>
        <!-- banner-other -->
		<section class="banner-other banner-project-current">
            <div class="container">
                <div class="banner-other__wrapper banner-other__wrapper--current">
                    <div class="banner-other__content">
                        <div class="banner-other__info banner-other__info--current">
                            <div class="banner-other__date banner-other__date--column">
                                <p class="banner-other__status">Активный</p>
                            </div>
                            <h1 class="banner-other__title main-title">
                                Реставрации Ротонды:
                            </h1>
                            <p class="banner-other__text main-text">
                               Реставрация родной для всех бауманцев Ротонды с Пеликаном. <br>
                                Мы ищем контакты для сотрудничества в рамках проведения мероприятия — добавить через форму обращения на почту info@bauman-polyteh.ru.
                            </p>
                            <a href="support.html" class="btn">Поддержать</a>
                        </div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-img-4.png" alt="" class="banner-other__image banner-other__image--current">
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.banner-other -->
        <section class="project-help">
            <div class="container">
                <h2 class="main-title project-help__title">Проекту необходима финансовая поддержка</h2>
                <p class="project-help__text main-text">
                    Мы рады любой помощи вне зависимости от её размера. Для компаний желающими стать спонсорами данного мероприятия - готовы направить спонсорский пакет.
                </p>
                <button class="btn project-help__btn" data-fancybox data-src="#form-finance-help">Связаться с организаторами</button>
            </div>
        </section>

        <section class="project-row">
            <div class="container">
                <div class="project-row__wrapper">
                    <div class="project-row__image">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-row-img.png" alt="">
                    </div>
                    <div class="project-row__content">
                        <p>
                            Ротонда — это памятник, расположенный на территории МГТУ им. Н. Э. Баумана. Она была возведена к 175-летию университета в качестве подарка от выпускников. В её центре стоит скульптура пеликана, изготовленная академиком А. Н. Бургановым.
                        </p>
                        <p>
                            На текущий момент ротонда нуждается в реставрации. Политехническое общество ищет выпускников, готовых помочь как и финансами, так и знаниями, необходимыми для составления плана реставрационных работ, сметы и поиска подходящих исполнителей.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="project-gallery">
            <div class="container">
                <div class="project-gallery__arrows">
                    <h2 class="main-title project-gallery__title">
                        Галерея
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
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-gallery-img-1.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-gallery-img-2.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-gallery-img-3.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-gallery-img-4.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-gallery-img-1.png" alt=""></div>
                    <div class="swiper-slide"><img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-gallery-img-2.png" alt=""></div>
                </div>
            </div>
        </section>

        <div class="button-help">
            <a href="support.html" class="btn project-polytech__btn">Поддержать</a>
        </div>
                
	</main>
<div class="form-finance-help" id="form-finance-help" style="display:none;max-width:1440px;">
        <h2 class="main-title">Связаться с организаторами</h2>
		<input type="email" placeholder="Електропочта" required>
		<input type="password" placeholder="Пароль" required>
        <textarea name="" id="" placeholder="Письмо"></textarea>
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
        <button class="btn form-finance-help__btn">Отправить</button>
	</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>