<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проекты");
$APPLICATION->SetPageProperty('description', 'Проекты Политехнического общества выпускников МГТУ: инициативы по развитию науки, технологий и связей с индустрией.');
?>
<main>
    <!-- banner-other -->
    <section class="banner-other banner-other-project">
        <div class="container">
            <div class="banner-other__wrapper">
                <div class="banner-other__content">
                    <div class="banner-other__info">
                        <h1 class="banner-other__title main-title">
                            Инициативные члены общества запустили знаковые проекты Политеха
                        </h1>
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                </div>
                <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/projects-page/project_page_hero.jpg" alt="" class="banner-other__image">
            </div>
        </div>
    </section>

    <?php po_render_projects_listing('Проекты общества'); ?>
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>