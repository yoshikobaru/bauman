<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?=$APPLICATION->GetTitle()?></title>
	<?php
	$_metaDesc = $APPLICATION->GetProperty('description');
	if ($_metaDesc):
	?><meta name="description" content="<?= htmlspecialchars($_metaDesc) ?>"><?php endif; ?>
	<?php if (function_exists('po_render_home_lcp_preload')) { po_render_home_lcp_preload(); } ?>
	<?$APPLICATION->ShowHead();?>
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
								<a href="/reference/">
									Референс-визиты
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/competencies/">
									Витрина компетенций
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/resume-form/">
									Карьерная платформа
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/about/">
									О нас
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/news/">
									События
								</a>
							</li>
							<li class="header-wrapper__item">
								<a href="/contacts/">
									Контакты
								</a>
							</li>
						</ul>
					</nav>
					<div class="header-wrapper__buttons">
						<?php
						$_userGroups = $USER->IsAuthorized() ? $USER->GetUserGroupArray() : [];
						$_isMember   = defined('PO_MEMBER_BASIC_ID') && (
							in_array(PO_MEMBER_BASIC_ID,   $_userGroups) ||
							in_array(PO_MEMBER_PREMIUM_ID, $_userGroups) ||
							in_array(PO_PARTNER_ID,        $_userGroups)
						);
						// Цветная галочка уровня членства
						$_memberBadge = '';
						if ($USER->IsAuthorized() && defined('PO_MEMBER_BASIC_ID')) {
							if (in_array(PO_PARTNER_ID, $_userGroups)) {
								$_memberBadge = '<span title="Партнёрское членство" style="display:inline-block;width:14px;height:14px;border-radius:50%;background:#2980b9;line-height:14px;text-align:center;font-size:10px;color:#fff;margin-left:5px;vertical-align:middle">✓</span>';
							} elseif (in_array(PO_MEMBER_PREMIUM_ID, $_userGroups)) {
								$_memberBadge = '<span title="Профессиональное членство" style="display:inline-block;width:14px;height:14px;border-radius:50%;background:#f0a500;line-height:14px;text-align:center;font-size:10px;color:#fff;margin-left:5px;vertical-align:middle">✓</span>';
							} elseif (in_array(PO_MEMBER_BASIC_ID, $_userGroups)) {
								$_memberBadge = '<span title="Базовое членство" style="display:inline-block;width:14px;height:14px;border-radius:50%;background:#7f8c8d;line-height:14px;text-align:center;font-size:10px;color:#fff;margin-left:5px;vertical-align:middle">✓</span>';
							}
						}
						if (!$USER->IsAuthorized()):
						?>
							<a href="/join/" class="btn header-wrapper__btn header-wrapper--join">Вступить</a>
							<button class="btn header-wrapper__btn header-wrapper__btn--sign btn-empty" data-fancybox data-src="#form-login">Войти</button>
						<?php else:
							$_displayName = $_isMember
								? htmlspecialchars(trim($USER->GetParam('NAME') . ' ' . $USER->GetParam('LAST_NAME')) ?: $USER->GetParam('EMAIL'))
								: htmlspecialchars($USER->GetParam('EMAIL'));
							$_logoutUrl = '/?logout=yes&sessid=' . bitrix_sessid() . '&backurl=%2F';
						?>
							<?php if (!$_isMember): ?>
							<a href="/join/" class="btn header-wrapper__btn header-wrapper--join">Вступить</a>
							<?php endif; ?>
							<div class="header-user-dropdown" style="position:relative;display:inline-block">
								<button class="btn header-wrapper__btn header-wrapper__btn--sign btn-empty header-user-btn" type="button" aria-expanded="false">
									<?= $_displayName ?><?= $_memberBadge ?>
									<svg style="margin-left:6px;vertical-align:middle" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
									</svg>
								</button>
								<div class="header-user-menu" style="display:none;position:absolute;right:0;top:calc(100% + 8px);min-width:200px;background:#fff;border:1px solid #e0e0e0;border-radius:4px;box-shadow:0 4px 16px rgba(0,0,0,.12);z-index:1000;padding:8px 0">
									<a href="/profile/" style="display:block;padding:10px 16px;color:#333;text-decoration:none;font-size:14px" class="header-user-menu__item">Настройки профиля</a>
									<a href="/profile/security/" style="display:block;padding:10px 16px;color:#333;text-decoration:none;font-size:14px" class="header-user-menu__item">Безопасность</a>
									<a href="/profile/?tab=activities" style="display:block;padding:10px 16px;color:#333;text-decoration:none;font-size:14px" class="header-user-menu__item">Мои активности</a>
									<a href="/profile/?tab=applications" style="display:block;padding:10px 16px;color:#333;text-decoration:none;font-size:14px" class="header-user-menu__item">Мои заявки</a>
									<?php
									$_isModerator = $USER->IsAdmin() || (defined('PO_MODERATOR_ID') && in_array(PO_MODERATOR_ID, $_userGroups));
									if ($_isModerator):
									?>
									<hr style="margin:4px 0;border:none;border-top:1px solid #f0f0f0">
									<a href="/local/admin/po_moderation.php" style="display:block;padding:10px 16px;color:#e74c3c;text-decoration:none;font-size:14px;font-weight:600" class="header-user-menu__item">⚙ Панель модерации</a>
									<?php endif; ?>
									<hr style="margin:4px 0;border:none;border-top:1px solid #f0f0f0">
									<a href="<?= $_logoutUrl ?>" style="display:block;padding:10px 16px;color:#c0392b;text-decoration:none;font-size:14px" class="header-user-menu__item">Выйти</a>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<script>
					(function() {
						var btn  = document.querySelector('.header-user-btn');
						var menu = document.querySelector('.header-user-menu');
						if (!btn || !menu) return;
						btn.addEventListener('click', function(e) {
							e.stopPropagation();
							var open = menu.style.display !== 'none';
							menu.style.display = open ? 'none' : 'block';
							btn.setAttribute('aria-expanded', String(!open));
						});
						document.addEventListener('click', function() {
							menu.style.display = 'none';
							btn.setAttribute('aria-expanded', 'false');
						});
						menu.addEventListener('click', function(e) { e.stopPropagation(); });
					})();
					</script>
				</div>				
			</div>
		</div>
	</header>
