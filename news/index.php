<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новости и события");
$APPLICATION->SetPageProperty('description', 'Новости и события Политехнического общества выпускников МГТУ им. Н.Э. Баумана: конференции, встречи, лекции и другие мероприятия.');
?>
<main>
    <section class="breadcrumbs">
        <div class="container">
            <ul>
                <li><a href="/">Главная</a></li>
                <li><a href="/news/">Новости и события</a></li>
            </ul>
        </div>
    </section>

    <?php po_render_news_page('Новости и события'); ?>
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
