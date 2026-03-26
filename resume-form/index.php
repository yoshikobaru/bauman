<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Карьерная платформа");
?>

<main>

        <section class="resume-select">
            <div class="container">
                <div class="resume-select__wrapper">
                    <div class="resume-select__card">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-1.png" alt="" class="resume-select__image desk-block">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-mob-1.png" alt="" class="resume-select__image desk-none">
                        <div>
                            <h2 class="main-title">Вакансия от компании</h2>
                            <button class="btn resume-select__btn">Разместить вакансию</button>
                        </div>
                    </div>
                    <div class="resume-select__card">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-2.png" alt="" class="resume-select__image desk-block">
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/resume-select-img-mob-2.png" alt="" class="resume-select__image desk-none">
                        <div>
                            <h2 class="main-title">Резюме выпускника</h2>
                            <button class="btn resume-select__btn resume-select__btn--blue">Разместить моё резюме</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
		<section class="join">
            <div class="container">
                <div class="join__wrapper">
                    <div class="join-have-acc">
                        <h3>
                            Есть аккаунт? 
                        </h3>
                        <p>
                            Войти или Зарегистрироваться, чтобы заполнить форму быстрее
                        </p>
                        <div class="join-have-acc__buttons">
                            <button class="btn join-have-acc__btn">Зарегистрироваться</button>
                            <button class="btn join-have-acc__btn join-have-acc__btn-sign">Войти</button>
                        </div>
                    </div>
                    <h2 class="account__title main-title">Резюме выпускника</h2>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Личные данные
                            </h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="text" placeholder="Фамилия">
                            <input type="text" placeholder="Имя">
                            <input type="text" placeholder="Отчество">
                            <input type="text" placeholder="Дата Рождения">
                            
                        </div>
                    </div>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Образование
                            </h3>
                        </div>
                        <div class="account__personal-list account__personal-list account__grid">
                            <input type="text" placeholder="Выпускающая кафедра">
                            <select name="Department" id="">
                                <option value="">Год выпуска</option>
                                <option value="Год выпуска">Год выпуска</option>
                                <option value="Год выпуска">Год выпуска</option>
                                <option value="Год выпуска">Год выпуска</option>
                            </select>
                        </div>
                    </div>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                               Данные об опыте работы
                            </h3>
                        </div>
                        <div class="account__personal-list account__grid--range">
                            <input type="text" placeholder="Сфера деятельности">
                            <input type="text" placeholder="Стаж">
                            <input type="text" placeholder="Желаемая должность">
                            
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
                    <button class="btn authorization__btn">Разместить моё резюме</button>
                </div>
                
            </div>
             
        </section>
		
        
        <section class="join">
            <div class="container">
                <div class="join__wrapper">
                    <div class="join-have-acc">
                        <h3>
                            Есть аккаунт? 
                        </h3>
                        <p>
                            Войти или Зарегистрироваться, чтобы заполнить форму быстрее
                        </p>
                        <div class="join-have-acc__buttons">
                            <button class="btn join-have-acc__btn">Зарегистрироваться</button>
                            <button class="btn join-have-acc__btn join-have-acc__btn-sign">Войти</button>
                        </div>
                    </div>
                    <h2 class="account__title main-title">Вакансия от компании</h2>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Данные о компании
                            </h3>
                        </div>
                        <div class="account__personal-list account__grid">
                            <input type="text" placeholder="Компания">
                            <input type="text" placeholder="Сайт">
                            
                        </div>
                    </div>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Данные о вакансии
                            </h3>
                        </div>
                        <input type="text" placeholder="Название должности">
                    </div>
                    <div class="account__personal" style="margin-top: 24px;">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Контакты для отклика
                            </h3>
                        </div>
                        <div class="account__personal-list account__grid--tripl">
                            <input type="text" placeholder="Фамилия">
                            <input type="text" placeholder="Имя">
                            <input type="text" placeholder="Отчество">
                            <input type="tel" placeholder="Номер телефона">
                            <input type="email" placeholder="Електропочта">
                            
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
                    <button class="btn authorization__btn">Разместить вакансию</button>
                </div>
                
            </div>
             
        </section>
	</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>