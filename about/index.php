<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("О нас");
$APPLICATION->SetPageProperty('description', 'О Политехническом обществе выпускников МГТУ им. Н.Э. Баумана: миссия, история, члены правления и цели организации.');
?><main>
<!-- banner-other --> <section class="banner-other banner-other-project">
<div class="container">
	<div class="banner-other__wrapper">
		<div class="banner-other__content">
			<div class="banner-other__info">
				<h1 class="banner-other__title main-title">
				О нас </h1>
			</div>
 <img src="/local/templates/my_template/assets/img/reference-page/banner-other-pattern.png" alt="" class="banner-other__pattern">
		</div>
 <img src="/local/templates/my_template/assets/img/about/about.jpg" alt="" class="banner-other__image">
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.banner-other --> <section class="director">
<div class="container">
	<div class="director__wrapper">
 <img src="/local/templates/my_template/assets/img/about/director-img.png" alt="" class="director__image">
		<div class="director__content">
			<p>
				 Дорогие друзья, приветствуем вас от имени Московского политехнического общества!
			</p>
			<p>
				 Ещё в 1874 году выпускники Императорского Московского технического училища договорились об одной простой вещи: бауманцы не теряют друг друга после выпуска. Так появилось Московское политехническое общество — как форма связи, поддержки и профессионального разговора между поколениями.
			</p>
			<p>
				 С тех пор менялись эпохи, названия и контексты, но суть оставалась прежней: связь выпускников, передача опыта, поддержка инженерной мысли и людей дела. Мы, новое поколение Бауманцев, загорелись идеей продолжать это величие! Это не просто проект, это миссия, способная изменить будущее. Мы предлагаем вам взглянуть на эту возможность как на шанс стать частью чего‑то большого и значимого.
			</p>
			<p>
				 Меня зовут Анна. Я — выпускница МТ-4 МВТУ (МГТУ) им. Н.Э. Баумана (2006 год) и директор Центра выпускников. Бауманка — часть моей жизни и профессионального пути, среда, которую я хорошо знаю и искренне уважаю.
			</p>
			<p>
				 Сегодня Политехническое общество МВТУ (МГТУ) Политех — это живая связь поколений. Пространство, где университет продолжается за пределами аудиторий: в диалоге, партнёрствах, совместных проектах, в умении быть полезными друг другу и университету. Здесь опыт не пылится, а работает — на развитие науки, инженерии и промышленности нашей страны.
			</p>
			<p>
				 Рада видеть вас на страницах нашего сайта и приглашаю стать частью МВТУ (МГТУ) Политех: делиться опытом, идеями, временем и энергией. Вместе мы продолжаем бауманскую историю — спокойно, по‑делу и с уверенностью в будущем.
			</p>
			<p>
				 С уважением, Анна Ганжа, Директор Центра выпускников МВТУ (МГТУ) им. Н.Э. Баумана Политехническое общество МВТУ (МГТУ) Политех
			</p>
		</div>
	</div>
</div>
 </section> <section class="boards boards--about">
<div class="container">
	<div class="boards__wrapper">
		<h2 class="main-title">
		Члены Совета Политехнического общества </h2>
		<div class="boards__list">
			 <?php
$boardMembers = [];
if (defined('IBLOCK_BOARD_ID') && IBLOCK_BOARD_ID > 0 && \Bitrix\Main\Loader::includeModule('iblock')):
    $dbBoardAbout = CIBlockElement::GetList(
        ['SORT' => 'ASC'],
        ['IBLOCK_ID' => IBLOCK_BOARD_ID, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID', 'NAME', 'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'DETAIL_TEXT']
    );
    while ($bm = $dbBoardAbout->GetNext()):
        $boardMembers[] = $bm;
        $photoSrc = $bm['PREVIEW_PICTURE']
            ? CFile::GetPath($bm['PREVIEW_PICTURE'])
            : SITE_TEMPLATE_PATH . '/assets/img/board-placeholder.png';
?>
			<div class="boards__item" data-fancybox="" data-src="#board-modal-&lt;span id=" bxid186643096"="" title="Код PHP: &lt;?= (int)$bm['ID'] ?&gt;">
				 <?= (int)$bm['ID'] ?><span class="bxhtmled-surrogate-inner"><span class="bxhtmled-right-side-item-icon"></span><span class="bxhtmled-comp-lable" unselectable="on" spellcheck="false">Код PHP</span></span>" style="cursor:pointer;"&gt; <img alt="<?= htmlspecialchars($bm['NAME']) ?>" src="<?= htmlspecialchars($photoSrc) ?>
				 " class="boards__item-image">
				<h3 class="boards__item-title">
				<?= htmlspecialchars($bm['NAME']) ?> </h3>
				<p class="boards__item-text">
					 <?= htmlspecialchars($bm['PREVIEW_TEXT']) ?>
				</p>
			</div>
			 <?php
    endwhile;
endif;
?>
		</div>
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.boards --> <!-- history --> <section class="history bg-white" id="history-section">
<div class="container">
	<h2 class="main-title history__title">
	С 19 века создаем сеть поддержки и обеспечиваем стабильность и рост общества </h2>
	<div class="history__scroll">
		<div class="history__size">
 <img src="/local/templates/my_template/assets/img/line.png" alt="">
			<div class="history__list">
				<div class="history__item">
					<h3>1874</h3>
					<p>
						 Ходатайство об учреждении при ИМТУ Общества учёных-техников
					</p>
				</div>
				<div class="history__item">
					<h3>1877</h3>
					<p>
						 Первый устав общества утверждён императором Александром II
					</p>
				</div>
				<div class="history__item">
					<h3>1905</h3>
					<p>
						 «Вестник Политехнического Общества»
					</p>
				</div>
				<div class="history__item">
					<h3>1907</h3>
					<p>
						 Первое собрание членов в собственном доме
					</p>
				</div>
				<div class="history__item">
					<h3>1991</h3>
					<p>
						 Зарегистрирована общественная организация <br>
						 «МВТУ-Политех»
					</p>
				</div>
				<div class="history__item">
					<h3>2024</h3>
					<p>
						 Обновление <br>
						 общества
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.history --> <section class="charter">
<div class="container">
	<div class="charter__wrapper">
 <img src="/local/templates/my_template/assets/img/about/charter-img-1.png" alt="" class="charter__image-main">
		<div class="charter__content">
			<div>
				<h2 class="charter__title">
				От мысли к делу </h2>
				<p>
					 В начале семидесятых годов XIX века в среде выпускников ИМТУ зародилась мысль об организации собственного общественного органа, идея была поддержана директором В.К. Делла-Восом.
				</p>
				<p>
					 Первоначальной целью объединения была взаимопомощь выпускников в различных областях их деятельности.
				</p>
				<p>
					 13 ноября 1874 года было представлено ходатайство об учреждении при ИМТУ Общества учёных техников.
				</p>
			</div>
 <img src="/local/templates/my_template/assets/img/about/charter-img-1.png" alt="" class="mob">
			<div class="charter__first">
 <img src="/local/templates/my_template/assets/img/about/charter-img-2.png" alt="">
				<p>
					 Первый устав общества был утверждён императором Александром II.
				</p>
			</div>
		</div>
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.charter --> <section class="house">
<div class="container">
	<h2 class="main-title">
	Собственный дом Общества </h2>
	<p class="main-text house-text">
		 Был построен на членские взносы
	</p>
	<div class="house__wrapper">
		<div class="house__info">
 <img src="/local/templates/my_template/assets/img/about/house-img.png" alt="">
			<p class="house__info-subscribe main-text">
				 г. Москва, Малый Харитоньевский переулок, дом 4
			</p>
			<p class="house__info-text main-text">
				 С 1936 года в этом здании располагается Институт машиноведения имени А. А. Благонравова РАН.
			</p>
		</div>
		<div class="house__history">
			<div class="house__scroll">
 <img src="/local/templates/my_template/assets/img/about/house-line.png" alt="">
				<div class="house__list">
					<div class="house__item">
						 1
						<p class="main-text">
							 В апреле 1902 года инженер-механик К.В. Абакумов предложил Обществу идею постройки собственного дома.
						</p>
					</div>
					<div class="house__item">
						 2
						<p class="main-text">
							 Окончательный проект был поручен инженеру А.В. Кузнецову.
						</p>
					</div>
					<div class="house__item">
						 3
						<p class="main-text">
							 Перед годичным собранием 1905 года состоялась закладка дома.
						</p>
					</div>
					<div class="house__item">
						 4
						<p class="main-text">
							 21 января 1907 года – первое собрание членов Политехнического Общества в собственном доме
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
 <!-- /.container --> </section>
<!-- /.house --> <section class="first-people">
<div class="container">
	<h2 class="main-title first-people__title">Первые люди и публикации Общества</h2>
	<div class="first-people__wrapper">
		<div class="first-people__card">
 <img src="/local/templates/my_template/assets/img/about/first-people-img-1.png" alt="" class="first-people__image">
			<h3 class="first-people__subtitle">
			Орлов Фёдор Евплович </h3>
			<p class="main-text">
				 ответственный за первый Устав Общества
			</p>
		</div>
		<div class="first-people__card">
 <img src="/local/templates/my_template/assets/img/about/first-people-img-2.png" alt="" class="first-people__image">
			<h3 class="first-people__subtitle">
			Делла-Вос Виктор Карлович </h3>
			<p class="main-text">
				 директор ИМТУ
			</p>
		</div>
		<div class="first-people__card">
 <img src="/local/templates/my_template/assets/img/about/first-people-img-3.png" alt="" class="first-people__image">
			<h3 class="first-people__subtitle">
			Худяков Петр Кондратьевич </h3>
			<p class="main-text">
				 профессор ИМТУ председатель Общества
			</p>
		</div>
		<div class="first-people__card">
			<p class="main-text">
				 Весной 1905 года начинает выходить «Вестник Политехнического Общества», журнал, в кото­ром, кроме научных трудов, публиковалась хроника Училища и сведения о жизни и деятельности инженеров‑выпускников ИМТУ. <br>
 <br>
				 Общество издаёт книги, в том числе посвящённые памяти своих выдающихся деятелей.
			</p>
		</div>
		<div class="first-people__card">
 <img src="/local/templates/my_template/assets/img/about/first-people-img-4.png" alt="" class="first-people__image">
		</div>
		<div class="first-people__card">
 <img src="/local/templates/my_template/assets/img/about/first-people-img-5.png" alt="" class="first-people__image">
		</div>
	</div>
</div>
 </section> <section class="revival">
<div class="container">
	<div class="revival__wrapper">
		<div class="revival__preview">
			<h2 class="main-title revival__title">
			Возрождение Политехнического Общества </h2>
			<p class="main-text">
				 17 декабря 1991 г. зарегистрирована общественная организация «МВТУ-Политех»
			</p>
		</div>
		<div class="revival__content">
			<p class="main-text">
				 4 сентября 1996 г. принят Устав Региональной общественной организации «Политехническое общество выпускников МВТУ (МГТУ) имени. Н.Э. Баумана» сокращенно «МВТУ-Политех»
			</p>
			<p class="main-text">
 <strong>Общее собрание не реже 1 раза в год</strong> — избирает Совет и президента;<br>
 <br>
 <strong>Совет Общества — 12 членов,</strong> Президент и Контрольно-ревизионная комиссия избирается на Общем собрании сроком на три года;<br>
 <br>
				 Совет общества избирает директора <strong>на 3 года;</strong><br>
 <br>
				 В дирекцию входят <strong>Учёный секретарь и бухгалтер,</strong> которые назначаются Советом по представлению директора.
			</p>
		</div>
	</div>
</div>
 </section> <section class="documents">
<div class="container">
	<h2 class="main-title documents__title">Уставные документы</h2>
	<div class="documents__table">
		<div class="documents__head">
			<div class="documents__number">
				<p>
					 Номер
				</p>
			</div>
			<div class="documents__name">
				<p>
					 Наименование документа
				</p>
			</div>
			<div class="documents__date">
				<p>
					 Дата публикации
				</p>
			</div>
			<div class="documents__icon">
			</div>
		</div>
		<div class="documents__body">
			<div class="documents__number">
				<p class="main-text">
					 №1
				</p>
			</div>
			<div class="documents__name">
				<p>
					 Устав Регионального общественного объединения «Политехническое общество выпускников МВТУ (МГТУ) им. Н.Э. Баумана»
				</p>
			</div>
			<div class="documents__date">
				<p class="main-text">
					 04.09.1996
				</p>
			</div>
			<div class="documents__icon">
 <a href="<?= defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#' ?>" target="_blank"> </a>
			</div>
		</div>
		<div class="documents__body">
			<div class="documents__number">
				<p class="main-text">
					 №2
				</p>
			</div>
			<div class="documents__name">
				<p>
					 Политика в отношении обработки персональных данных
				</p>
			</div>
			<div class="documents__date">
				<p class="main-text">
					 2025
				</p>
			</div>
			<div class="documents__icon">
 <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank"> </a>
			</div>
		</div>
	</div>
 <button class="documents__btn btn">Все документы</button>
</div>
 </section> </main>
<?php foreach ($boardMembers as $bm):
    $mPhotoSrc = $bm['PREVIEW_PICTURE']
        ? CFile::GetPath($bm['PREVIEW_PICTURE'])
        : SITE_TEMPLATE_PATH . '/assets/img/board-placeholder.png';
?>
<div class="form-boards" id="board-modal-&lt;span id=" bxid393565794"="" title="Код PHP: &lt;?= (int)$bm['ID'] ?&gt;">
	 <?= (int)$bm['ID'] ?><span class="bxhtmled-surrogate-inner"><span class="bxhtmled-right-side-item-icon"></span><span class="bxhtmled-comp-lable" unselectable="on" spellcheck="false">Код PHP</span></span>" style="display:none;max-width:1100px;"&gt;
	<div class="form-boards__wrapper">
 <img alt="<?= htmlspecialchars($bm['NAME']) ?>" src="<?= htmlspecialchars($mPhotoSrc) ?>
		 " class="form-boards__image">
		<div class="form-boards__content">
			<h2><?= htmlspecialchars($bm['NAME']) ?></h2>
			 <?php if (!empty($bm['DETAIL_TEXT'])): ?>
			<div>
				 <?= $bm['DETAIL_TEXT'] ?>
			</div>
			 <?php else: ?>
			<p>
				 <?= htmlspecialchars($bm['PREVIEW_TEXT']) ?>
			</p>
			 <?php endif; ?>
		</div>
	</div>
</div>
<?php endforeach; ?><?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>