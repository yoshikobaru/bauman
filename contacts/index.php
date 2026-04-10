<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Контакты");
?>

<main>
        <section class="contacts-top">
            <div class="container">
                <a href="mailto:info@bauman-polytech.ru" class="footer-mail">
                    info@bauman-polytech.ru
                </a>
                <p class="footer-address">
                    Москва, Бригадирский переулок, 13, 4 этаж, каб. 407
                </p>
                <p class="footer-name">
                    Политехническое Общество Выпускников МВТУ (МГТУ) им. Н.Э. Баумана
                </p>
                <ul class="footer-social">
                    <li class="footer-social__item">
                        <a href="#" target="_blank">
                            <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="52" height="52" fill="white"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M0 10C0.422184 29.988 10.5558 42 28.3222 42H29.3293V30.5645C35.8577 31.2052 40.7943 35.9139 42.7755 42H52C49.4666 32.9029 42.8079 27.8738 38.6505 25.9519C42.8079 23.5816 48.6543 17.8158 50.0509 10H41.6709C39.8521 16.3424 34.461 22.1081 29.3293 22.6526V10H20.9492V32.1661C15.7525 30.8849 9.19171 24.6707 8.89939 10H0Z" fill="black"/>
                            </svg>
                        </a>
                    </li>
                    <li class="footer-social__item">
                        <a href="#" target="_blank">
                            <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_545_277_contacts)">
                                    <rect width="52" height="52" fill="white"/>
                                    <path d="M41.0367 15.4964L16.0377 29.2649C15.7715 29.4115 15.4679 29.4759 15.1651 29.4502L4.5008 28.5433C2.8828 28.4057 2.55392 26.1804 4.06293 25.5807L45.7164 9.02524C46.8393 8.57892 48.0148 9.542 47.7981 10.7308L41.9617 42.7517C41.7668 43.8211 40.5544 44.3566 39.6326 43.7803L24.0656 34.0479C23.2092 33.5126 23.0922 32.3114 23.8292 31.6208L41.0367 15.4964Z" fill="black"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_545_277_contacts">
                                        <rect width="52" height="52" fill="white"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
        </section>

        <section class="map">
            <div style="position:relative;overflow:hidden;"><a href="https://yandex.ru/maps/213/moscow/?utm_medium=mapframe&utm_source=maps" style="color:#eee;font-size:12px;position:absolute;top:0px;">Москва</a><a href="https://yandex.ru/maps/213/moscow/house/brigadirskiy_pereulok_13/Z04YcA9gTkIPQFtvfXt3d3liYg==/inside/?ll=37.680778%2C55.766517&tab=inside&utm_medium=mapframe&utm_source=maps&z=16" style="color:#eee;font-size:12px;position:absolute;top:14px;">Организации внутри: Москва, Бригадирский переулок, 13 — Яндекс Карты</a><iframe src="https://yandex.ru/map-widget/v1/?ll=37.680778%2C55.766517&mode=whatshere&tab=inside&whatshere%5Bpoint%5D=37.680778%2C55.766516&whatshere%5Bzoom%5D=17&z=16" width="100%" height="600" frameborder="1" allowfullscreen="true" style="position:relative;"></iframe></div>
        </section>

        
	</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>