<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Попечительский совет");
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
                                Попечительский совет 
                            </h1>
                            <p class="banner-other__text main-text">
                                Совет, состоящий из представителей кафедры или факультета, экспертов-выпускников и представителей промышленности, должен стать началом системной трансформации, которая повысит качество подготовки специалистов и укрепит связь МГТУ с индустрией. <br>
                                Мы ищем контакты для сотрудничества в рамках проведения мероприятия — добавить через форму обращения на почту info@bauman-polyteh.ru.
                            </p>
                            <a href="support.html" class="btn">Поддержать</a>
                        </div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-img-3.png" alt="" class="banner-other__image banner-other__image--current">
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

        <section class="exhibition-program exhibition-program--call">
            <div class="container">
                <h2 class="main-title exhibition-program__title exhibition-program__title--call">
                    Вызовы современного инженерного образования
                </h2>
                <p class="main-text exhibition-program__text">
                    Система требует трансформации для соответствия реальным потребностям индустрии и повышения качества подготовки специалистов.
                </p>
                <div class="exhibition-program__list exhibition-program__list--call">
                    <div class="exhibition-program__item exhibition-program__item--call">
                        <h3>
                        Разрыв с индустрией
                        </h3>
                        <p class="main-text">
                        Программы отстают от актуальных требований рынка труда
                        </p>
                    </div>
                    <div class="exhibition-program__item exhibition-program__item--call">
                        <h3>
                            Материально-техническая база
                        </h3>
                        <p class="main-text">
                        Необходимо постоянное обновление лабораторий и оборудования
                        </p>
                    </div>
                    <div class="exhibition-program__item exhibition-program__item--call">
                        <h3>
                            Профориентация студентов
                        </h3>
                        <p class="main-text">
                            Ранняя связь с практикой повышает осознанность выбора карьеры
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="project-option">
            <div class="container">
                <div class="project-option__wrapper">
                    <h2 class="project-option__title main-title">
                        Вариант механизма работы и финансирования
                    </h2>
                    <p class="project-option__text main-text">
                        Прозрачная, эффективная модель, встроенная в существующие структуры и фонды.
                    </p>
                    <div class="project-option__scroll">
                        <div class="project-option__overflow">
                            <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project-option-line.png" alt="" class="project-option__image">
                            <div class="project-option__list">
                                <div>
                                    <h3>
                                        Факультет или кафедра
                                    </h3>
                                    <p class="main-text">
                                        Предлагает идеи и инициативы
                                    </p>
                                </div>
                                <div>
                                    <h3>
                                        Попечительский совет
                                    </h3>
                                    <p class="main-text">
                                        Экспертный фильтр и одобрение проектов
                                    </p>
                                </div>
                                <div>
                                    <h3>
                                        Фонд Политехнического общества
                                    </h3>
                                    <p class="main-text">
                                        Финансирование утверждённых инициатив
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="project-option__attention">
                        <p>
                            Совет является частью структуры Политехнического общества выпускников МГТУ, что обеспечивает институциональную стабильность и доступ к ресурсам.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="exhibition-program exhibition-program--call">
            <div class="container">
                <h2 class="main-title exhibition-program__title exhibition-program__title--call">
                   Источники финансирования фонда
                </h2>
                <div class="exhibition-program__list exhibition-program__list--fond">
                    <div class="exhibition-program__item exhibition-program__item--fond">
                        <h3>
                            Коммерческие заказы
                        </h3>
                        <p class="main-text">
                            Уникальная роль выпускников кафедры: глубокая экспертиза в направлении закона о единстве измерений и многочисленные подзаконные акты и технормативы. Умение не только пользоваться и создавать методы и средства измерения, но и обеспечивать установленные в НПА действия по их утверждению, аттестации, регистрации, экспертизе.
                        </p>
                    </div>
                    <div class="exhibition-program__item exhibition-program__item--fond">
                        <h3>
                            Заработок, включая лоббистский ресурс
                        </h3>
                        <p class="main-text">
                            Привлечение средств через доходы от проектов и использование лоббистских возможностей для развития кафедры.
                        </p>
                    </div>
                    <div class="exhibition-program__item exhibition-program__item--fond">
                        <h3>
                            Привлечение внебюджетных средств
                        </h3>
                        <p class="main-text">
                            Прозрачная система привлечения и реализации внебюджетных средств для поддержки инициатив
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="project-faculties">
            <div class="container">
                <h2 class="project-faculties__title main-title">
                    Кафедры и факультеты имеющие попечительские советы
                </h2>
                <h3 class="project-faculties__subtitle">
                    Машиностроительные технологии
                </h3>
                <p class="project-faculties__subtext">
                    Кафедры
                </p>
                <ul class="project-faculties__list">
                    <li class="project-faculties__item">
                        МТ4 Метрология и взаимозаменяемость
                    </li>
                </ul>
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