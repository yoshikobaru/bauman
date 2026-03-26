<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мой профиль");
?>

<main>
		<section class="account">
            <div class="container">
                <div class="account__wrapper">
                    <div class="account__sidebar">
                        <div class="account__menu">
                            <a href="#" class="account__menu-item account__menu-item--active">Мой профиль</a>
                            <a href="#" class="account__menu-item">Безопасность</a>
                            <a href="#" class="account__menu-item">Мои активности</a>
                            <a href="#" class="account__menu-item">Мои заявки</a>
                        </div>
                    </div>

                    <div class="account__main">
                        <div class="account__block">
                            <h2 class="account__title account__title">Мой профиль</h2>
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
                                    <button class="account__chapter-edit">Редактировать</button>
                                </div>
                                <div class="account__personal-list account__grid">
                                    <input type="text" placeholder="Фамилия">
                                    <input type="text" placeholder="Имя">
                                    <input type="text" placeholder="Отчество">
                                    <input type="text" placeholder="Дата Рождения">
                                    <input type="email" placeholder="Електропочта">
                                    <input type="password" placeholder="Пароль">
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
                                </div>
                            </div>
                            <div class="account__file">
                                <div class="account__file-complete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none">
                                        <path d="M11.2871 0.5L15.5 4.88281V19.5H0.5V0.5H11.2871Z" stroke="black"/>
                                    </svg>
                                    <p>document-23553-plan.pdf</p>
                                </div>
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
                            
                            <div class="account__chapter">
                                <h3 class="account__subtitle">
                                    Ваш тариф
                                </h3>
                            </div>
                            <div class="account__rate account__rate--proff">
                                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/rate-conus.png" alt="" class="account__rate-conus">
                                <div class="account__rate-info">
                                    <span class="account__rate-status">Активный</span>
                                    <div class="account__rate-date">
                                        <span>Срок действия</span>  до 11 Апреля 2027
                                    </div>
                                </div>
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
                                <div class="account__rate-buttons account__grid">
                                    <button class="account__rate-btn btn">Продлить</button>
                                    <button class="account__rate-btn account__rate-btn--changes btn">Изменить тариф</button>
                                </div>
                            </div>
                            <div class="account__ready">
                                <div class="account__ready-info">
                                    <p class="account__ready-text">
                                        8(800)888-00-98
                                    </p>
                                    <p class="account__ready-status">
                                        Подключено
                                    </p>
                                </div>
                                <div class="account__ready-info">
                                    <p class="account__ready-text">
                                        example@mail.ru
                                    </p>
                                    <p class="account__ready-status">
                                        Подключено
                                    </p>
                                </div>
                            </div>
                            <div class="account__log">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">
                                        Привязка аккаунта для быстрого входа
                                    </h3>
                                </div>
                                <div class="account__log-wrapper">
                                    <button class="account__log-btn">
                                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/yandex-icon.png" alt="">
                                        Войти через Яндекс
                                    </button>
                                    <button class="account__log-btn">
                                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/gos-icon.png" alt="">
                                        Войти через Госуслуги
                                    </button>
                                    <button class="account__log-btn">
                                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/my_profile/vk-icon.png" alt="">
                                        Войти через Вконтакте
                                    </button>
                                </div>
                            </div>
                            <div class="account__activity">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">
                                        Активные сессии
                                    </h3>
                                </div>
                                <div class="account__activity-wrapper account__grid">
                                    <div class="account__activity-card">
                                        <div class="account__activity-type">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="20" viewBox="0 0 22 20" fill="none">
                                                <path d="M7 19H15M11 15V19M3 1H19C20.1046 1 21 1.89543 21 3V13C21 14.1046 20.1046 15 19 15H3C1.89543 15 1 14.1046 1 13V3C1 1.89543 1.89543 1 3 1Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Chrome
                                        </div>
                                        <p class="account__activity-text">
                                            Москва
                                        </p>
                                        <p class="account__activity-text">
                                            Это устройство
                                        </p>
                                    </div>
                                    <div class="account__activity-card">
                                        <div class="account__activity-type">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 18H12.01M7 2H17C18.1046 2 19 2.89543 19 4V20C19 21.1046 18.1046 22 17 22H7C5.89543 22 5 21.1046 5 20V4C5 2.89543 5.89543 2 7 2Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            iPhone 17
                                        </div>
                                        <p class="account__activity-text">
                                            Москва
                                        </p>
                                        <p class="account__activity-text">
                                            Сегодня в 17:00
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                       <div class="account__block">
                            <h2 class="account__title account__title">Мои активности</h2>
                            <div class="account__bots">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">
                                        Доступные Telegram-чаты
                                    </h3>
                                </div>
                                <a href="#" class="account__bots-link">Название чата в тг канале, наименование кликабельно</a>
                                <a href="#" class="account__bots-link">Название чата в тг канале, наименование полностью кликабельно</a>
                                <a href="#" class="account__bots-link">Наименование чата в тг, название кликабельно</a>
                                <a href="#" class="account__bots-link">Название чата в тг канале</a>
                                <a href="#" class="account__bots-link">Наименование полностью кликабельно</a>
                            </div>
                            <div class="account__events">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">
                                        Мероприятия
                                    </h3>
                                    <p class="account__show-more">
                                        Показать все
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="7" viewBox="0 0 13 7" fill="none">
                                            <path d="M12.5 0.5L6.5 6.5L0.5 0.5" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </p>
                                </div>
                                <div class="account__events-list">
                                    <div class="account__events-card">
                                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/news-img.png" alt="">
                                        <div>
                                            <span class="account__events-status">Активно</span>
                                            <h4 class="account__events-name">
                                                Бауманский университет пилотирует создание Фонда целевого капитала.
                                            </h4>
                                            <a href="#" class="account__events-btn">Вступить в чат</a>
                                        </div>
                                    </div>
                                    <div class="account__events-card account__events-card--disactive">
                                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/news-img.png" alt="">
                                        <div>
                                            <span class="account__events-status">Завершено</span>
                                            <h4 class="account__events-name">
                                                Бауманский университет пилотирует создание Фонда целевого капитала.
                                            </h4>
                                            <a href="#" class="account__events-btn">Вступить в чат</a>
                                        </div>
                                    </div>
                                    <div class="account__events-card account__events-card--disactive">
                                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/news-img.png" alt="">
                                        <div>
                                            <span class="account__events-status">Завершено</span>
                                            <h4 class="account__events-name">
                                                Бауманский университет пилотирует создание Фонда целевого капитала.
                                            </h4>
                                            <a href="#" class="account__events-btn">Вступить в чат</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="account__donate">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">
                                        История пожертвований
                                    </h3>
                                     <p class="account__show-more">
                                        Показать все
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="7" viewBox="0 0 13 7" fill="none">
                                            <path d="M12.5 0.5L6.5 6.5L0.5 0.5" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </p>
                                </div>
                                <div class="account__donate-list account__grid">
                                    <div class="account__donate-card">
                                        <div class="account__donate-info">
                                            <span class="account__donate-status">Активный</span>
                                            <div class="account__donate-time">
                                                <span>Запущен</span>
                                                <p>
                                                    11 Апреля 2025
                                                </p>
                                            </div>
                                        </div>
                                         <h4 class="account__donate-title">
                                                Бауманский университет пилотирует создание Фонда целевого капитала
                                            </h4>
                                            <p class="account__donate-text">
                                                Сумма
                                            </p>
                                            <p class="account__donate-price">
                                                300 Р
                                            </p>
                                    </div>
                                    <div class="account__donate-card account__donate-card--disactive">
                                        <div class="account__donate-info">
                                            <span class="account__donate-status">Активный</span>
                                            <div class="account__donate-time">
                                                <span>Завершено</span>
                                                <p>
                                                    11 Апреля 2025
                                                </p>
                                            </div>
                                        </div>
                                         <h4 class="account__donate-title">
                                            Бауманский университет пилотирует создание Фонда целевого капитала
                                        </h4>
                                        <p class="account__donate-text">
                                            Сумма
                                        </p>
                                        <p class="account__donate-price">
                                            300 Р
                                        </p>
                                    </div>
                                    <div class="account__donate-card account__donate-card--disactive">
                                        <div class="account__donate-info">
                                            <span class="account__donate-status">Активный</span>
                                            <div class="account__donate-time">
                                                <span>Завершено</span>
                                                <p>
                                                    11 Апреля 2025
                                                </p>
                                            </div>
                                        </div>
                                         <h4 class="account__donate-title">
                                            Бауманский университет пилотирует создание Фонда целевого капитала
                                        </h4>
                                        <p class="account__donate-text">
                                            Сумма
                                        </p>
                                        <p class="account__donate-price">
                                            300 Р
                                        </p>
                                    </div>
                                    <div class="account__donate-card account__donate-card--disactive">
                                        <div class="account__donate-info">
                                            <span class="account__donate-status">Активный</span>
                                            <div class="account__donate-time">
                                                <span>Завершено</span>
                                                <p>
                                                    11 Апреля 2025
                                                </p>
                                            </div>
                                        </div>
                                         <h4 class="account__donate-title">
                                            Бауманский университет пилотирует создание Фонда целевого капитала
                                        </h4>
                                        <p class="account__donate-text">
                                            Сумма
                                        </p>
                                        <p class="account__donate-price">
                                            300 Р
                                        </p>
                                    </div>
                                </div>
                            </div>
                       </div>
                       <div class="account__block">
                            <h2 class="account__title account__title">Мои заявки</h2>
                            <div class="account__applications">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">
                                        Статусы заявок
                                    </h3>
                                     <p class="account__show-more">
                                        Показать все
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="7" viewBox="0 0 13 7" fill="none">
                                            <path d="M12.5 0.5L6.5 6.5L0.5 0.5" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </p>
                                </div>
                                <div class="account__applications-card">
                                    <div class="account__applications-info">
                                        <p class="account__applications-name">
                                            Вступление
                                        </p>
                                        <span class="account__applications-status">
                                            На рассмотрении
                                        </span>
                                    </div>
                                    <h4 class="account__applications-title">
                                        Бауманский университет пилотирует создание Фонда целевого капитала
                                    </h4>
                                    <div class="account__donate-time">
                                        <span>Подано</span>
                                        <p>
                                            11 Апреля 2025
                                        </p>
                                    </div>
                                </div>
                                <div class="account__applications-card account__applications-card--disactive">
                                    <div class="account__applications-info">
                                        <p class="account__applications-name">
                                            Вступление
                                        </p>
                                        <span class="account__applications-status">
                                            Завершено
                                        </span>
                                    </div>
                                    <h4 class="account__applications-title">
                                        Бауманский университет пилотирует создание Фонда целевого капитала
                                    </h4>
                                    <div class="account__donate-time">
                                        <span>Подано</span>
                                        <p>
                                            11 Апреля 2025
                                        </p>
                                    </div>
                                </div>
                                <div class="account__applications-card account__applications-card--disactive">
                                    <div class="account__applications-info">
                                        <p class="account__applications-name">
                                            Вступление
                                        </p>
                                        <span class="account__applications-status">
                                            Завершено
                                        </span>
                                    </div>
                                    <h4 class="account__applications-title">
                                        Бауманский университет пилотирует создание Фонда целевого капитала
                                    </h4>
                                    <div class="account__donate-time">
                                        <span>Подано</span>
                                        <p>
                                            11 Апреля 2025
                                        </p>
                                    </div>
                                </div>
                            </div>
                       </div>
                    </div>

                </div>
            </div>
        </section>
	</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>