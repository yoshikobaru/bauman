<?php
/**
 * One-time migration: load СЕМАТ and МАШ ЮНИТ reference visits into IBLOCK_REFERENCE_ID.
 * Run AFTER setup_reference.php and after updating init.php with the correct IBLOCK_REFERENCE_ID.
 * Then DELETE this file.
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('iblock');

if (!defined('IBLOCK_REFERENCE_ID') || IBLOCK_REFERENCE_ID <= 0) {
    die("Ошибка: IBLOCK_REFERENCE_ID не задан в init.php. Сначала запустите setup_reference.php и пропишите ID.\n");
}

$refId = IBLOCK_REFERENCE_ID;
$tplPath = '/local/templates/my_template';

// Получаем ID значений списка REF_STATUS
$dbProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $refId, 'CODE' => 'REF_STATUS']);
$prop = $dbProp->Fetch();
$activeEnumId = null;
$completedEnumId = null;
if ($prop) {
    $dbVals = CIBlockPropertyEnum::GetList(['SORT' => 'ASC'], ['PROPERTY_ID' => $prop['ID']]);
    while ($val = $dbVals->Fetch()) {
        if ($val['XML_ID'] === 'active')    $activeEnumId    = $val['ID'];
        if ($val['XML_ID'] === 'completed') $completedEnumId = $val['ID'];
    }
}

if (!$activeEnumId) {
    die("Ошибка: не найдено значение 'active' для свойства REF_STATUS. Проверьте setup_reference.php.\n");
}

// ===== ВИЗИТЫ ДЛЯ ЗАГРУЗКИ =====
$visits = [

    // ---- СЕМАТ ----
    [
        'name'         => 'Референс-визит в СЕМАТ',
        'code'         => 'semat',
        'sort'         => 100,
        'preview_text' => 'Авиастроение, машиностроение, медицина, приборка, робототехника, электроника — если вы внутри этих процессов, вам точно будет о чём поговорить.',
        'status_id'    => $completedEnumId ?: $activeEnumId,
        'ref_date'     => '5 февраля 2026, 10:30',
        'ref_location' => 'Инновационный центр Сколково',
        'ref_duration' => '2,5–3 часа',
        'detail_text'  => <<<HTML
<section class="project-help">
    <div class="container">
        <h2 class="main-title project-help__title">Инженерия — она здесь. Настоящая. Бауманская.</h2>
        <p class="project-help__text main-text">
            Приглашаем вас, выпускники Бауманки, на референс-визит в СЕМАТ — инженерную компанию, где проектируют и собирают высокотехнологичное оборудование для обработки металлов.
        </p>
    </div>
</section>

<section class="reference-activities">
    <div class="container">
        <p class="main-text reference-activities__text">
            В основе деятельности компании — четыре краеугольных камня:
        </p>
        <div class="reference-activities__list">
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">Передовые решения для оптимизации</h3>
                <p class="reference-activities__list-text main-text">производственных процессов;</p>
            </div>
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">Гарантия качества (сертификация ISO 9001)</h3>
                <p class="reference-activities__list-text main-text">и полное техническое сопровождение проектов;</p>
            </div>
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">Собственные запатентованные технологии (ЭХО, ЭЭО, УЗУ)</h3>
                <p class="reference-activities__list-text main-text">снижающие себестоимость продукции;</p>
            </div>
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">Клиентоориентированный подход:</h3>
                <p class="reference-activities__list-text main-text">предпроектные тесты, НИОКР и услуги металлообработки.</p>
            </div>
        </div>
    </div>
</section>

<section class="participants">
    <div class="container">
        <div class="participants__wrapper">
            <div class="participants__left">
                <h2 class="main-title participants__title">Это встреча для:</h2>
                <img src="{TPL}/assets/img/reference-page/participants-img-2.png" alt="">
            </div>
            <div class="participants__right">
                <div class="participants__card">
                    <p class="participants__number">01</p>
                    <p class="participants__position">Топ менеджмент</p>
                    <ul class="participants__list">
                        <li class="participants__item">Владельцы бизнеса</li>
                        <li class="participants__item">Генеральные и коммерческие директора</li>
                        <li class="participants__item">Руководители профильных отделов/направлений</li>
                    </ul>
                </div>
                <div class="participants__card">
                    <p class="participants__number">02</p>
                    <p class="participants__position">Отраслевые эксперты</p>
                    <ul class="participants__list">
                        <li class="participants__item">Эксперты от МВТУ (МГТУ) им. Н.Э. Баумана</li>
                        <li class="participants__item">Эксперты от Политехнического общества выпускников МВТУ (МГТУ) им. Н.Э. Баумана</li>
                    </ul>
                </div>
                <div class="participants__card">
                    <p class="participants__number">03</p>
                    <p class="participants__position">Технические специалисты</p>
                    <ul class="participants__list">
                        <li class="participants__item">Технические директора</li>
                        <li class="participants__item">Представители отдела развития</li>
                        <li class="participants__item">Представители отдела закупок</li>
                        <li class="participants__item">Главные инженеры</li>
                        <li class="participants__item">Специалисты по внедрению</li>
                        <li class="participants__item">Сервисные инженеры</li>
                        <li class="participants__item">И другие</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="exhibition-program">
    <div class="container">
        <h2 class="main-title exhibition-program__title">
            Здесь не расскажут, как должно быть. <br> Здесь покажут, как это уже работает.
        </h2>
        <div class="exhibition-program__list">
            <div class="exhibition-program__item exhibition-program__item--reference">
                <h3>В лаборатории вы увидите</h3>
                <p class="main-text">
                    сборку электрохимического станка и прототип российского электроэрозионного станка, а также проведёте тестовую обработку детали электродом.
                </p>
            </div>
            <div class="exhibition-program__item exhibition-program__item--reference">
                <h3>На производственной площадке</h3>
                <p class="main-text">
                    помимо фрезеровки и токарной обработки можно будет увидеть процесс электроэрозионной обработки — вырезку детали латунной проволокой.
                </p>
            </div>
            <div class="exhibition-program__item exhibition-program__item--reference">
                <h3>Кульминация визита</h3>
                <p class="main-text">
                    живая сессия с Павлом Беликовым, выпускником Бауманки и генеральным директором СЕМАТ. Разговор не про «успешный успех», а про производство как точку роста.
                </p>
            </div>
        </div>
    </div>
</section>
HTML,
    ],

    // ---- МАШ ЮНИТ ----
    [
        'name'         => 'Референс-визит в МАШ ЮНИТ',
        'code'         => 'mash-unit',
        'sort'         => 200,
        'preview_text' => 'Едем к разработчикам отечественной электроники МАШ ЮНИТ — резиденту Сколково, который собирает ИТ-оборудование от платы до промышленного компьютера.',
        'status_id'    => $completedEnumId ?: $activeEnumId,
        'ref_date'     => '27 февраля 2026, 10:00',
        'ref_location' => 'Технопарк Отрадное',
        'ref_duration' => '2–2,5 часа',
        'detail_text'  => <<<HTML
<section class="reference-activities">
    <div class="container">
        <h2 class="main-title reference-activities__title">Их сила в:</h2>
        <div class="reference-activities__list reference-activities__list--mash">
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">Системе качества уровня ISO</h3>
            </div>
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">ОС в реестре Минцифры</h3>
            </div>
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">ПАК в реестре Минпрома</h3>
            </div>
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">Лицензированном ПО ФСТЭК с модулем криптошифрования</h3>
            </div>
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">20+ патентов и экранах 8K до 80 дюймов</h3>
            </div>
            <div class="reference-activities__item">
                <h3 class="reference-activities__list-title">Российской компонентной базе</h3>
            </div>
        </div>
    </div>
</section>

<section class="project-row project-row--mash">
    <div class="container">
        <div class="project-row__wrapper">
            <div class="project-row__content">
                <h2 class="main-title">Это встреча для тех, кто ищет</h2>
                <ul>
                    <li>Отечественные технологические решения</li>
                    <li>Партнёров по разработке отделов/направлений</li>
                    <li>Контрактный монтаж</li>
                </ul>
                <p>Участвуют выпускники Бауманки. Дальше такие визиты будут доступны только членам Политехнического общества выпускников.</p>
            </div>
            <div class="project-row__image">
                <img src="{TPL}/assets/img/reference-page/reference-meet-img.png" alt="">
            </div>
        </div>
    </div>
</section>

<section class="exhibition-program exhibition-program--cemat-after">
    <div class="container">
        <div class="exhibition-program__wrapper--cemat-after">
            <h2 class="main-title exhibition-program__title">За 2 часа покажут:</h2>
            <div class="exhibition-program__list exhibition-program__list--cemat-after">
                <div class="exhibition-program__item exhibition-program__item--cemat-after">
                    <h3>Линию монтажа плат</h3>
                    <p class="main-text">на производстве 1000 м²</p>
                </div>
                <div class="exhibition-program__item exhibition-program__item--cemat-after">
                    <h3>R&amp;D-лабораторию</h3>
                    <p class="main-text">В действии</p>
                </div>
                <div class="exhibition-program__item exhibition-program__item--cemat-after">
                    <h3>Одноплатные компьютеры</h3>
                    <p class="main-text">и медиасистемы</p>
                </div>
                <div class="exhibition-program__item exhibition-program__item--cemat-after">
                    <h3>Как становятся</h3>
                    <p class="main-text">поставщиком заводов уровня Метеор Лифт, ЩЛЗ, КМЗ</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="reference-sesion">
    <div class="container">
        <div class="reference-sesion__wrapper reference-sesion__wrapper--mash">
            <div class="reference-sesion__image">
                <img src="{TPL}/assets/img/reference-page/reference-sesion-img-mash.png" alt="" class="desk-block">
                <img src="{TPL}/assets/img/reference-page/reference-sesion-img-mash-mob.png" alt="" class="desk-none">
            </div>
            <div class="reference-sesion__content">
                <div>
                    <h2 class="main-title reference-sesion__title">Отдельный бонус</h2>
                    <p class="reference-sesion__text main-text">Живая кейс-сессия с гендиректором Андреем Лариным.</p>
                    <p class="reference-sesion__content-subtext"><span>Тема сессии</span></p>
                    <h3 class="reference-sesion__content-subtitle">
                        «Отечественные технологии в электронике: как локализация разработок способствует технологическому суверенитету».
                    </h3>
                    <p class="reference-sesion__content-subtext"><span>Формат</span> Живой, открытый</p>
                    <p class="reference-sesion__text main-text">Можно будет спрашивать, спорить, уточнять</p>
                </div>
            </div>
        </div>
    </div>
</section>
HTML,
    ],
];

// ===== ЗАПИСЬ В ИНФОБЛОК =====
$el = new CIBlockElement();

foreach ($visits as $v) {
    // Заменить {TPL} на реальный путь к шаблону
    $detailHtml = str_replace('{TPL}', $tplPath, $v['detail_text']);

    // Проверить, не создан ли уже элемент с таким кодом
    $dbCheck = CIBlockElement::GetList(
        [], ['IBLOCK_ID' => $refId, 'CODE' => $v['code']], false, false, ['ID']
    );
    if ($existed = $dbCheck->Fetch()) {
        echo "• Визит '{$v['name']}' (CODE={$v['code']}) уже существует (ID={$existed['ID']}), пропуск.\n";
        continue;
    }

    $res = $el->Add([
        'IBLOCK_ID'    => $refId,
        'NAME'         => $v['name'],
        'CODE'         => $v['code'],
        'SORT'         => $v['sort'],
        'ACTIVE'       => 'Y',
        'PREVIEW_TEXT' => $v['preview_text'],
        'DETAIL_TEXT'  => $detailHtml,
        'DETAIL_TEXT_TYPE' => 'html',
        'PROPERTY_VALUES' => [
            'REF_STATUS'   => $v['status_id'],
            'REF_DATE'     => $v['ref_date'],
            'REF_LOCATION' => $v['ref_location'],
            'REF_DURATION' => $v['ref_duration'],
        ],
    ]);

    if ($res) {
        echo "✓ Визит '{$v['name']}' создан (ID=$res). URL: /reference/{$v['code']}/\n";
    } else {
        echo "✗ Ошибка создания '{$v['name']}': " . $el->LAST_ERROR . "\n";
    }
}

echo "\nГотово. Удалите этот файл после выполнения.\n";
