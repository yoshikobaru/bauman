<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("PolytechExpo");
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
                                <p class="banner-other__time"><span>Дата:</span> Осень 2026 года</p>
                            </div>
                            <h1 class="banner-other__title main-title">
                                Конференция PolytechExpo
                            </h1>
                            <p class="banner-other__text main-text">
                                Эффективный инструмент для коммуникации бизнеса, промышленности и передового технического вуза. Ежегодная научно-промышленная выставка
                            </p>
                            <p class="banner-other__text main-text">
                               Мы ищем контакты для сотрудничества в рамках проведения мероприятия — добавить через форму обращения на почту info@bauman-polyteh.ru.
                            </p>
                            <a href="support.html" class="btn">Поддержать</a>
                        </div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-img.png" alt="" class="banner-other__image banner-other__image--current">
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

        <section class="project-target">
            <div class="container">
                <h2 class="main-title project-target__title">
                    Цели
                </h2>
                <div class="project-target__list">
                    <div class="project-target__item">
                        <h3>
                            Создать среду для обмена информацией о потребностях бизнеса, государства и университета в сфере технологического предпринимательства
                        </h3>
                    </div>
                    <div class="project-target__item">
                        <h3>
                            Представить Бауманку в качестве базы для инновационных разработок
                        </h3>
                    </div>
                    <div class="project-target__item">
                        <h3>
                            Развитие общества молодых учёных политехнического общества
                        </h3>
                    </div>
                </div>
            </div>
        </section>

        <section class="classter">
            <div class="container">
                <h2 class="main-title classter__title">
                    Кластеры выставки
                </h2>
                <div class="classter__list">
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-1.png" alt="">
                        <p class="main-text">
                            Машино-строительные технологии <br>
                            и перспективные материалы
                        </p>
                    </div>
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-2.png" alt="">
                        <p class="main-text">
                            Наземные транспортно-технологические системы
                        </p>
                    </div>
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-3.png" alt="">
                        <p class="main-text">
                            Оборонные технологии
                        </p>
                    </div>
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-4.png" alt="">
                        <p class="main-text">
                            Робототехника
                        </p>
                    </div>
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-5.png" alt="">
                        <p class="main-text">
                            Технологии защиты природы
                        </p>
                    </div>
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-6.png" alt="">
                        <p class="main-text">
                            Технологии энерго-машиностроения
                        </p>
                    </div>
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-7.png" alt="">
                        <p class="main-text">
                            Фотонные, квантовые <br>
                            и флюидные технологии
                        </p>
                    </div>
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-8.png" alt="">
                        <p class="main-text">
                            Инженерия в науках о жизни
                        </p>
                    </div>
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-9.png" alt="">
                        <p class="main-text">
                            Цифровая трансформация<br>
                            и ИИ
                        </p>
                    </div>
                    <div class="classter__item">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/project_PolytechExpo/classter-icon-10.png" alt="">
                        <p class="main-text">
                            Космическая техника
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="exhibition-program">
            <div class="container">
                <h2 class="main-title exhibition-program__title">
                    Программа выставки
                </h2>
                <div class="exhibition-program__list">
                    <div class="exhibition-program__item">
                        <h3>
                            Деловая программа 
                        </h3>
                        <p class="main-text">
                            Самые актуальные вопросы для глобальной промышленности в дискуссиях <br>представителей органов власти и бизнеса
                        </p>
                    </div>
                    <div class="exhibition-program__item">
                        <h3>
                            Премия молодых учёных
                        </h3>
                        <p class="main-text">
                            Критерии отбора: технологическая новизна, доказанный экономический эффект, межотраслевое применение, высокий экспортный потенциал
                        </p>
                    </div>
                    <div class="exhibition-program__item">
                        <h3>
                            Форум
                        </h3>
                        <p class="main-text">
                            Технологического предпринимательства для молодых учёных и инженеров
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="project-polytech">
            <div class="container">
                <div class="project-polytech__wrapper">
                    <div class="project-polytech__preview">
                        <h2 class="main-title project-polytech__title">
                            BAUMAN <br >POLYTECH <br class="desk-block">EXPO
                        </h2>
                        <p class="main-text project-polytech__text">
                            Форум Технологического предпринимательство для молодых учёных и инженеров
                        </p>
                        <p class="project-polytech__subtext">
                            Политех как акселератор взаимодействия предпринимателей, государства, молодых инженеров и учёных
                        </p>
                    </div>
                    <div class="project-polytech__view">                       
                        <div class="project-polytech__view-item">
                            <h3 class="project-polytech__view-title">
                                КОНФЕРЕНЦИЯ
                            </h3>
                            <div class="project-polytech__view-row">
                                <div>
                                    <p>
                                        Лучшие спикеры из мира технологического предпринимательства расскажут о успешных кейсах
                                    </p>
                                </div>
                                <div>
                                    <p>
                                        Молодые учёные и инженеры поделятся своим опытом технологического предпринимательства
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="project-polytech__view-item">
                            <h3 class="project-polytech__view-title">
                                БИРЖА КОНТАКТОВ
                            </h3>
                            <div class="project-polytech__view-row">
                                <div>
                                    <p>
                                        Возможность предварительно зарегистрироваться и назначить встречу с заинтересовавшим контактом
                                    </p>
                                </div>
                                <div>
                                    <p>
                                        Анонс онлайн платформы для Биржи контактов политехнического общества
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="project-polytech__view-item">
                            <h3 class="project-polytech__view-title">
                                Разбор кейсов
                            </h3>
                            <div class="project-polytech__view-row">
                                <div>
                                    <p>
                                        Приглашённые гости в неформальной обстановке расскажут о своих неудачах
                                    </p>
                                </div>
                                <div>
                                    <p>
                                        Открытый микрофон — любой участник может подать заявку на выступление и прислать свою историю
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="button-help">
            <a href="support.html" class="btn project-polytech__btn">Поддержать</a>
        </div>
                
	</main>
<div class="form-finance-help" id="form-finance-help" style="display:none;max-width:1440px;">
        <h2 class="main-title">Связаться с организаторами</h2>
		<input type="email" placeholder="e-mail" required>
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