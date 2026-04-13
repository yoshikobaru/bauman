<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Витрина компетенций");
$APPLICATION->SetPageProperty('description', 'Витрина компетенций МВТУ (МГТУ) им. Н.Э. Баумана: НОЦ, студенческие КБ и компетенции партнёров Политехнического общества выпускников.');

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');
Loader::includeModule('iblock');

$_userGroups = $USER->IsAuthorized() ? $USER->GetUserGroupArray() : [];
$_isMember   = defined('PO_MEMBER_BASIC_ID') && (
    in_array(PO_MEMBER_BASIC_ID,   $_userGroups) ||
    in_array(PO_MEMBER_PREMIUM_ID, $_userGroups) ||
    in_array(PO_PARTNER_ID,        $_userGroups)
);

$d6Done  = false;
$d6Error = '';

// D6: Запрос в витрине компетенций (только члены)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d6_action'])) {
    if (!$_isMember) {
        $d6Error = 'Доступно только для членов общества.';
    } else {
        $company = trim($_POST['company']     ?? '');
        $fn      = trim($_POST['first_name']  ?? '');
        $em      = trim($_POST['email']       ?? '');
        if (!$fn || !$em) {
            $d6Error = 'Заполните обязательные поля: Имя, Email.';
        } else {
            if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
                $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
                if ($hlEntity) {
                    $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                    $res = $hlClass::add([
                        'UF_USER_ID'     => (int)$USER->GetID(),
                        'UF_TYPE'        => 'competency_request',
                        'UF_STATUS'      => 'new',
                        'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                        'UF_DATA'        => json_encode([
                            'company'    => $company,
                            'first_name' => $fn,
                            'last_name'  => trim($_POST['last_name'] ?? ''),
                            'email'      => $em,
                            'phone'      => trim($_POST['phone']  ?? ''),
                            'request'    => trim($_POST['request'] ?? ''),
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
                    if ($res->isSuccess()) {
                        $d6Done = true;
                        po_logAction('form_submit', 'application', 0, 'D6 запрос в витрине компетенций');
                        $d6Data = [
                            'first_name' => $fn,     'last_name' => trim($_POST['last_name'] ?? ''),
                            'email'      => $em,     'phone'     => trim($_POST['phone'] ?? ''),
                            'company'    => $company,'request'   => trim($_POST['request'] ?? ''),
                        ];
                        po_sendAdminEmail('competency_request', $d6Data);
                        po_createCrmLead('competency_request', $d6Data);
                    } else {
                        $d6Error = 'Ошибка сохранения. Попробуйте позже.';
                    }
                }
            } else {
                $d6Done = true; // HL не настроен — просто принимаем
            }
        }
    }
}
?>

<main>
        <!-- banner-other -->
		<section class="banner-other">
            <div class="container">
                <div class="banner-other__wrapper">
                    <div class="banner-other__content">
                        <div class="banner-other__info">
                            <h1 class="banner-other__title main-title">
                               Витрина компетенций МВТУ (МГТУ) им. Н.Э. Баумана
                            </h1>
                            <a href="#" class="banner-other__btn btn" data-fancybox data-src="#form-finance-help">Стать партнёром</a>
                        </div>
                        <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
                    </div>
                    <img src="<?=SITE_TEMPLATE_PATH?>/assets/img/competencies-img.png" alt="" class="banner-other__image">
                </div>
            </div>
            <!-- /.container -->
        </section>
        <!-- /.banner-other -->
        
        <section class="competencies">
            <div class="container">
                <?php
                $catTabs = [
                    'university' => ['title' => 'Компетенции университета',          'tab' => 'competencies__univer'],
                    'skb'        => ['title' => 'Студенческие конструкторские бюро', 'tab' => 'competencies__student'],
                    'partner'    => ['title' => 'Компетенции партнёров',             'tab' => 'competencies__partner'],
                ];
                $competenciesData = ['university' => [], 'skb' => [], 'partner' => []];
                $useIblock = defined('IBLOCK_COMPETENCIES_ID') && IBLOCK_COMPETENCIES_ID > 0;
                if ($useIblock) {
                    $dbEl = CIBlockElement::GetList(
                        ['SORT' => 'ASC'],
                        ['IBLOCK_ID' => IBLOCK_COMPETENCIES_ID, 'ACTIVE' => 'Y'],
                        false, false,
                        ['ID','NAME','PREVIEW_PICTURE','PREVIEW_TEXT','DETAIL_TEXT','PROPERTY_TAGS','PROPERTY_CATEGORY','PROPERTY_PDF_LINK']
                    );
                    while ($arEl = $dbEl->GetNext()) {
                        $cat = $arEl['PROPERTY_CATEGORY_VALUE'] ?? 'university';
                        if (!isset($competenciesData[$cat])) $cat = 'university';
                        $competenciesData[$cat][] = $arEl;
                    }
                }
                $activeTab = 'university';
                ?>
                <div class="competencies__tabs">
                    <ul class="competencies__navs">
                        <?php foreach ($catTabs as $catKey => $catInfo): ?>
                        <li class="main-tabs-click <?= $catKey === $activeTab ? 'main-tabs-click--active' : '' ?>"
                            data-tab="<?= $catInfo['tab'] ?>">
                            <?= htmlspecialchars($catInfo['title']) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="competencies__content">
                    <?php foreach ($catTabs as $catKey => $catInfo):
                        $items = $competenciesData[$catKey];
                    ?>
                    <div class="competencies__item main-tabs-pane <?= $catKey === $activeTab ? 'main-tabs-pane--active' : '' ?>"
                         data-tab="<?= $catInfo['tab'] ?>">
                        <div class="competencies__list">
                            <?php if (!$useIblock): ?>
                            <p style="padding:20px;color:#888">
                                Инфоблок компетенций не настроен.
                                <?php if ($USER->IsAdmin()): ?><a href="/setup_competencies.php">Запустить настройку</a><?php endif; ?>
                            </p>
                            <?php elseif (empty($items)): ?>
                            <p style="padding:20px;color:#888">Витрина компетенций в разработке. Следите за новостями.</p>
                            <?php else: ?>
                            <?php foreach ($items as $comp):
                                $imgSrc = '';
                                if (!empty($comp['PREVIEW_PICTURE'])) {
                                    $imgSrc = CFile::GetPath($comp['PREVIEW_PICTURE']);
                                }
                                $imgSrc = $imgSrc ?: (SITE_TEMPLATE_PATH . '/assets/img/competencies-img-1.png');
                                $tagsRaw = $comp['PROPERTY_TAGS_VALUE'] ?? '';
                                $tagList = array_filter(array_map('trim', preg_split('/[\s,]+/', $tagsRaw)));
                                $pdfLink = $comp['PROPERTY_PDF_LINK_VALUE'] ?? '';
                            ?>
                            <div class="competencies__card">
                                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($comp['NAME']) ?>" class="competencies__card-image">
                                <?php if (!empty($tagList)): ?>
                                <div class="competencies__card-tags">
                                    <?php foreach ($tagList as $tag): ?><div><?= htmlspecialchars($tag) ?></div><?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <p class="competencies__card-subtext main-text"><?= htmlspecialchars($comp['PREVIEW_TEXT'] ?? '') ?></p>
                                <h2 class="competencies__card-title"><?= htmlspecialchars($comp['NAME']) ?></h2>
                                <?php if (!empty($comp['DETAIL_TEXT'])): ?>
                                <p class="main-text competencies__card-text"><?= strip_tags($comp['DETAIL_TEXT']) ?></p>
                                <?php endif; ?>
                                <?php if ($pdfLink): ?>
                                <a href="<?= htmlspecialchars($pdfLink) ?>" class="competencies__card-link" target="_blank" rel="noopener">Скачать подробное описание в PDF</a>
                                <?php endif; ?>
                                <button class="btn" data-fancybox data-src="#form-competencies">Отправить запрос</button>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        
	</main>
<div class="form-reference-visits" id="form-competencies" style="display:none;">
	<div class="join__wrapper">
		<?php if ($d6Done): ?>
			<h2 class="account__title main-title">Заявка принята!</h2>
			<p style="margin-top:16px">Мы свяжемся с вами в ближайшее время.</p>
		<?php elseif (!$USER->IsAuthorized()): ?>
			<h2 class="account__title main-title">Запрос в витрину компетенций</h2>
			<p style="margin-top:16px">Для отправки запроса необходимо <a href="/authorization/">войти</a> или <a href="/join/">вступить в общество</a>.</p>
		<?php elseif (!$_isMember): ?>
			<h2 class="account__title main-title">Запрос в витрину компетенций</h2>
			<p style="margin-top:16px">Запрос доступен только для членов Политехнического общества. <a href="/join/">Вступить</a></p>
		<?php else: ?>
		<h2 class="account__title main-title">Запрос в витрину компетенций</h2>
		<?php if ($d6Error): ?>
		<div class="authorization__alert authorization__alert--error" style="margin:12px 0">
			<p><?= htmlspecialchars($d6Error) ?></p>
		</div>
		<?php endif; ?>
		<form method="POST" action="/competencies/">
			<input type="hidden" name="d6_action" value="1">
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">Ваши данные</h3>
				</div>
				<div class="account__personal-list account__grid">
					<input type="text"  name="last_name"  placeholder="Фамилия"
					       value="<?= htmlspecialchars($USER->GetParam('LAST_NAME')) ?>">
					<input type="text"  name="first_name" placeholder="Имя *" required
					       value="<?= htmlspecialchars($USER->GetParam('NAME')) ?>">
					<input type="email" name="email"      placeholder="e-mail *" required
					       value="<?= htmlspecialchars($USER->GetParam('EMAIL')) ?>">
					<input type="tel"   name="phone"      placeholder="Телефон">
					<input type="text"  name="company"    placeholder="Компания">
				</div>
			</div>
			<div class="account__personal">
				<div class="account__chapter">
					<h3 class="account__subtitle">Запрос</h3>
				</div>
				<div style="margin-top:8px">
					<textarea name="request" placeholder="Опишите ваш запрос к витрине компетенций"
					          style="width:100%;min-height:100px;padding:12px;border:1px solid #ccc"></textarea>
				</div>
			</div>
			<p class="form-required-note">* Обязательные поля</p>
			<button type="submit" class="btn authorization__btn">Отправить</button>
		</form>
		<?php endif; ?>
		</div>
	</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
