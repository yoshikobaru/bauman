<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Вступить в общество (юр. лицо)");
?>

<main>
		<section class="join">
            <div class="container">
                <div class="join__wrapper">
                    <h2 class="account__title main-title">Вступить в общество (юр. лиц)</h2>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Данные представителя
                            </h3>
                        </div>
                        <div class="account__personal-list account__grid--tripl">
                            <input type="text" placeholder="Фамилия">
                            <input type="text" placeholder="Имя">
                            <input type="text" placeholder="Отчество">
                            <input type="email" placeholder="Електропочта">
                            <input type="password" placeholder="Пароль">
                            
                        </div>
                    </div>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">
                                Сведения о компании
                            </h3>
                        </div>
                        <div class="account__personal-list account__grid--range">
                            <input type="text" placeholder="Компания">
                            <input type="text" placeholder="Сайт">
                            <input type="text" placeholder="Планируемое количество представителей на платформе">
                            
                        </div>
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