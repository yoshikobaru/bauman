<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?=$APPLICATION->GetTitle()?></title>
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/assets/css/swiper-bundle.min.css">
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/assets/css/fancybox.css">
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/assets/css/main.css">
</head>
<body>
<?php $APPLICATION->ShowPanel();?>
	<header class="header">
		<div class="container">
			<div class="header-wrapper">
				<a href="/" class="header-wrapper__logo desk-none">
					<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/logo-header.png" alt="Политехническое Общество Выпускников">
				</a>
				<button class="header-wrapper__burger" aria-label="Открыть меню">
					<span></span>
					<span></span>
					<span></span>
				</button>
				<div class="header-wrapper__menu-mobile">
					<a href="/" class="header-wrapper__logo desk-block">
						<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/logo-header.png" alt="Политехническое Общество Выпускников">
					</a>
					<nav>
						<ul class="header-wrapper__list">
							<li class="header-wrapper__item">
								<a href="/">
									Главная
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/support/">
									Поддержать
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/projects/">
									Проекты
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/news/">
									События
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/about/">
									О нас
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/contacts/">
									Контакты
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/reference/">
									Референс-визиты
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/resume-form/">
									Карьерная платформа
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/competencies/">
									Витрина компетенций
								</a>
							</li>
						</ul>
					</nav>
					<div class="header-wrapper__buttons">
						<a href="/subscriptions/" class="btn header-wrapper__btn header-wrapper--join">Вступить</a>
						<button class="btn header-wrapper__btn header-wrapper__btn--sign btn-empty" data-fancybox data-src="#form-login">Войти</button>
					</div>
				</div>				
			</div>
		</div>
	</header>
