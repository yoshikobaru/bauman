<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Вступить в общество");
?>

<main>
		<section class="join">
            <div class="container">
                <div class="join__wrapper">
                    <h2 class="account__title main-title">Вступить в общество</h2>
                    <div class="account__photo">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/avatar.png" alt="" class="account__photo-image">
                        <div class="account__photo-content">
                            <label class="account__photo-upload">
                                Загрузить аватар
                                <input type="file" class="account__photo-input" accept="image/png, image/jpeg">
                            </label>
                            <p>
                                Изображение размером 300x300, формат jpg, png
                            </p>
                        </div>
                    </div>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Личные данные
                            </h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="email" placeholder="Електропочта">
                            <input type="password" placeholder="Пароль">
                            <input type="text" placeholder="Фамилия">
                            <input type="text" placeholder="Имя">
                            <input type="text" placeholder="Отчество">
                            <input type="text" placeholder="Дата Рождения">
                            
                        </div>
                    </div>
                    <div class="account__graduate">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Выпускник МГТУ? 
                            </h3>
                        </div>
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
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Данные выпускника
                            </h3>
                            <button class="account__chapter-edit">Редактировать</button>
                        </div>
                        <div class="account__personal-list account__personal-list--short account__grid">
                            <select name="Department" id="">
                                <option value="">Год окончания</option>
                                <option value="Кафедра">Кафедра</option>
                                <option value="Кафедра">Кафедра</option>
                                <option value="Кафедра">Кафедра</option>
                            </select>
                            <select name="Department" id="">
                                <option value="">Выпускающая кафедра</option>
                                <option value="Кафедра">Кафедра</option>
                                <option value="Кафедра">Кафедра</option>
                                <option value="Кафедра">Кафедра</option>
                            </select>
                            <input type="text" placeholder="Telegram" required>
                        </div>
                    </div>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                            Сведения о дипломе
                            </h3>
                            <button class="account__chapter-edit">Редактировать</button>
                        </div>
                        <div class="account__personal-list account__personal-list--short account__grid">
                            <input type="text" placeholder="Серия бланка">
                            <input type="text" placeholder="Номер бланка">
                            <input type="text" placeholder="Дата выдачи">
                            <textarea name="" id="" placeholder="Достижения"></textarea>
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
                                <button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership">Выбрать</button>
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
                                <button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership">Выбрать</button>
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
                                <button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership">Выбрать</button>
                            </div>
                            <div class="swiper-slide membership-slider__card membership-slider__card--gratuitous">
                               <h3 class="membership-slider__title">
                                    Почётное
                                </h3>
                                <p class="membership-slider__time">
                                    по результатам заполненной анкеты
                                </p>
                                <button class="membership-slider__advantages">+ Возможности Базового</button>
                                <ul class="membership-slider__list">
                                    <li class="membership-slider__item">
                                        Для тех, кто внёс значительный вклад в развитие технической науки, образования, технологий и деятельности Политехнического общества.
                                    </li>
                                </ul>
                                <button class="membership-slider__join btn btn-empty" data-fancybox data-src="#form-membership">Выбрать</button>
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <div class="join__politic">
                        <div class="join__politic-question">
                            <p class="join__politic-link">
                                Ознакомлен(а) и согласен(а) с <a href="#">Уставом</a> и <a href="#">Положением о членских взносах</a>
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
                    <button class="btn authorization__btn">Вступить</button>
                </div>
                <div class="join__wrapper">
                    <h2 class="account__title main-title">Вы уже член общества</h2>
                    <div class="account__chapter">
                        <h3 class="account__subtitle">
                           Ваш тариф
                        </h3>
                    </div>
                     <div class="account__rate account__rate--proff">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/rate-conus.png" alt="" class="account__rate-conus">
                        <h4 class="account__rate-plan">
                            Профессиональное
                        </h4>
                        <p class="account__rate-price">
                            50 000 Р
                        </p>
                        <p class="account__rate-when">
                            ежегодно
                        </p>
                        <p class="account__rate-advantages">
                            + Возможности Базового
                        </p>
                        <ul class="account__rate-list">
                            <li class="account__rate-item">
                                Участие в закрытом чате членов общества уровня «Бизнес»;
                            </li>
                            <li class="account__rate-item">
                                Размещение информации и новостей о компании на площадках Политехнического общества;
                            </li>
                            <li class="account__rate-item">
                                Возможность предложить собственный проект для поиска спонсоров и поддержки Политехнического общества;
                            </li>
                            <li class="account__rate-item">
                                Участие в бизнес-мероприятиях Политехнического общества в онлайн и очном форматах;
                            </li>
                            <li class="account__rate-item">
                                Доступ к базе резюме выпускников на карьерной платформе Политехнического общества.
                            </li>
                        </ul>
                        <div class="account__rate-buttons">
                            <button class="account__rate-btn account__rate-btn--changes btn">Изменить тариф</button>
                        </div>
                    </div>
                </div>
                <div class="join__wrapper">
                    <h2 class="account__title main-title">Вступить в общество</h2>
                    <div class="account__chapter">
                        <h3 class="account__subtitle">
                           Личные данные
                        </h3>
                    </div>
                     <div class="join__grid">
                        <input type="text" placeholder="Фамилия">
                        <input type="text" placeholder="Имя">
                        <input type="text" placeholder="Отчество">
                        <input type="text" placeholder="Компания">
                        <input type="text" placeholder="Тема">
                        <textarea name="" id="" placeholder="Комментарий"></textarea>
                        <input type="email" placeholder="Електропочта">
                        <input type="password" placeholder="Пароль">
                    </div>
                    <div class="account__graduate">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Выпускник МГТУ? 
                            </h3>
                        </div>
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
                    <div class="join__dont">
                        <p>
                            Если вы не являетесь членом «Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана» мы также будем рады обсудить возможные форматы сотрудничества.
                        </p>
                        <a href="mailto:info@bauman-polytech.ru" class="footer-mail">
                            info@bauman-polytech.ru
                        </a>
                    </div>
                    <div class="join__politic">
                        <div class="join__politic-question">
                            <p class="join__politic-link">
                                Ознакомлен(а) и согласен(а) с <a href="#">Уставом</a> и <a href="#">Положением о членских взносах</a>
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
                    <button class="btn authorization__btn">Вступить</button>

                </div>
                
            </div>
             
        </section>
	</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>