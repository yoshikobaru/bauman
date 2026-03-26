<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Инициативы");
?>

<main>
		<!-- banner-other -->
		<section class="banner-other banner-other--initiatives">
            <div class="container">
                <div class="banner-other__wrapper">
                    <div class="banner-other__content">
                        <div class="banner-other__info">
                            <h1 class="banner-other__title main-title">
                                Реализация проектов становится возможной благодаря
                            </h1>
                            <ul class="banner-other__list-initiatives">
                                <li>
                                    Добровольным пожертвованиям выпускников
                                </li>
                                <li>
                                    Членским взносам участников сообщества выпускников
                                </li>
                                <li>
                                    Оплате участия в мероприятиях партнёрами
                                </li>
                                <li>
                                    Спонсорской помощи крупного бизнеса  и госкорпораций
                                </li>
                                <li>
                                    Прибыли от реализации стартапов (crowdfunding)
                                </li>
                                <li>
                                    Грантовой поддержке
                                </li>
                            </ul>
                        </div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/initiatives-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/initiatives-page/banner-other-img.png" alt="" class="banner-other__image">
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.banner-other -->
        <!-- initiatives -->
        <section class="initiatives">
            <div class="container">
                <h2 class="main-title initiatives__title">
                    Информационная кампания по формированию имиджа инженерных специальностей 
                </h2>
                 <div class="new-project__slider swiper">
					<div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="new-project__card">
                                    <h3>
                                        Встречи с представителями индустрии
                                    </h3>
                                    <p>
                                        Обсуждение требований бизнеса к выпускникам, обмен ожиданиями и тенденциями.
                                    </p>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="new-project__card">
                                    <h3>
                                        Встречи с представителями индустрии
                                    </h3>
                                    <p>
                                        Обсуждение требований бизнеса к выпускникам, обмен ожиданиями и тенденциями.
                                    </p>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="new-project__card">
                                    <h3>
                                        Встречи с представителями индустрии
                                    </h3>
                                    <p>
                                        Обсуждение требований бизнеса к выпускникам, обмен ожиданиями и тенденциями.
                                    </p>
                                </div>
                            </div>
					    </div>
					<div class="swiper-pagination"></div>
				</div>
            </div>
        </section>
        
	</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>