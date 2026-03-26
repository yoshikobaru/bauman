<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Войти");
?>

<main>
		<section class="authorization">
            <div class="container">
                <div class="authorization__wrapper">
                    <h2 class="authorization-title main-title">
                        Войти
                    </h2>
                    <div class="authorization__row">
                        <input type="email" placeholder="Електропочта">
                        <input type="password" placeholder="Пароль">
                    </div>
                    <p class="authorization__fogot">
                        Не помню пароль, <a href="#" class="authorization__link authorization__link--fogot">восстановить</a> 
                    </p>
                    <button class="btn authorization__btn">Войти</button>
                </div>
                
                <div class="authorization__wrapper">
                    <h2 class="authorization-title main-title">
                        Восстановление пароля
                    </h2>
                    <div class="authorization__tabs">
                        <div class="authorization__tabs-nav">
                            <p class="authorization__tabs-click authorization__tabs-click--active main-tabs-click main-tabs-click--active" data-tab="phone">
                                Телефон
                            </p>
                            <p class="authorization__tabs-click main-tabs-click" data-tab="email">
                                Почта
                            </p>
                        </div>
                        <div class="authorization__tabs-content">
                            <div class="authorization__tabs-pane authorization__tabs-pane--active main-tabs-pane main-tabs-pane--active" data-tab="phone">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">
                                        Мобильный телефон
                                    </h3>
                                </div>
                                <div class="authorization__row">
                                    <input type="text" placeholder="Телефон">
                                    <input type="text" placeholder="Код">
                                </div>
                            </div>

                            <div class="authorization__tabs-pane main-tabs-pane" data-tab="email">
                                <div class="account__chapter">
                                    <h3 class="account__subtitle">
                                        Почта
                                    </h3>
                                </div>
                                <div class="authorization__row">
                                    <input type="text" placeholder="Електропочта">
                                    <input type="text" placeholder="Код">
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="authorization__link">Я не помню номер или не мею к нему доступа</a>
                    <button class="btn authorization__btn">Получить код</button>
                </div>

                <div class="authorization__wrapper">
                    <h2 class="authorization-title main-title">
                        Восстановление пароля
                    </h2>
                    <div class="account__chapter">
                        <h3 class="account__subtitle">
                            Почта
                        </h3>
                    </div>
                    <div class="authorization__row">
                        <input type="password" placeholder="Придумайте пароль">
                        <input type="password" placeholder="Повторите пароль">
                    </div>
                    <button class="btn authorization__btn">Сохранить</button>
                </div>
                <div class="authorization__wrapper">
                    <h2 class="authorization-title main-title">
                        Восстановление пароля
                    </h2>
                    <div class="account__chapter">
                        <h3 class="account__subtitle">
                            Почта
                        </h3>
                    </div>
                    <div class="authorization__row authorization__row--create">
                        <input type="text" placeholder="Имя">
                        <input type="text" placeholder="Фамилия">
                        <input type="text" placeholder="Отчество">
                        <input type="email" placeholder="Доступная почта">
                        <input type="number" placeholder="Доступный номер телефона">
                    </div>
                    <button class="btn authorization__btn">Отправить</button>
                </div>
               
            </div>
        </section>
	</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>