<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
	<!-- footer -->
	<footer class="footer">
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
			<div class="footer-list">
				<a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">
					Политика обработки персональных данных
				</a>
				<a href="#" target="_blank">
					Юридическая информация и реквизиты
				</a>
				<a href="#" target="_blank">
					Публичная оферта
				</a>
			</div>
			<div class="footer-bottom">
				<ul class="footer-social">
					<li class="footer-social__item">					
						<a href="#" target="_blank" aria-label="ВКонтакте">
							<svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect width="52" height="52" fill="white"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M0 10C0.422184 29.988 10.5558 42 28.3222 42H29.3293V30.5645C35.8577 31.2052 40.7943 35.9139 42.7755 42H52C49.4666 32.9029 42.8079 27.8738 38.6505 25.9519C42.8079 23.5816 48.6543 17.8158 50.0509 10H41.6709C39.8521 16.3424 34.461 22.1081 29.3293 22.6526V10H20.9492V32.1661C15.7525 30.8849 9.19171 24.6707 8.89939 10H0Z" fill="black"/>
							</svg>
						</a>
					</li>
					<li class="footer-social__item">					
						<a href="#" target="_blank" aria-label="Telegram">
							<svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
								<g clip-path="url(#clip0_545_277)">
								<rect width="52" height="52" fill="white"/>
								<path d="M41.0367 15.4964L16.0377 29.2649C15.7715 29.4115 15.4679 29.4759 15.1651 29.4502L4.5008 28.5433C2.8828 28.4057 2.55392 26.1804 4.06293 25.5807L45.7164 9.02524C46.8393 8.57892 48.0148 9.542 47.7981 10.7308L41.9617 42.7517C41.7668 43.8211 40.5544 44.3566 39.6326 43.7803L24.0656 34.0479C23.2092 33.5126 23.0922 32.3114 23.8292 31.6208L41.0367 15.4964Z" fill="black"/>
								</g>
								<defs>
								<clipPath id="clip0_545_277">
								<rect width="52" height="52" fill="white"/>
								</clipPath>
								</defs>
							</svg>
						</a>
					</li>
				</ul>
				<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/mir.png" alt="МИР" class="footer-mir">
			</div>
		</div>
	</footer>

	<!-- Форма входа -->
	<div class="form-login" id="form-login" style="display:none;max-width:500px;">
		<p id="form-login-error" style="display:none;color:#c0392b;margin-bottom:12px"></p>
		<input type="email"    id="modal-email"    placeholder="e-mail" required>
		<input type="password" id="modal-password" placeholder="Пароль" required>
		<div class="form-login__row">
			<label class="checkbox-container">
				<input type="checkbox" id="modal-remember">
				<span class="checkmark"></span>
				Запомнить меня
			</label>
			<a href="/authorization/" class="form-login__lost">Я забыл пароль</a>
		</div>
		<div class="form-login__buttons">
			<button class="btn form-login__btn form-login__btn--sign" id="modal-login-btn">Войти</button>
			<a href="/registration/" class="btn form-login__btn form-login__btn--register btn-empty">Зарегистрироваться</a>
		</div>
	</div>
	<script>
	(function(){
		var btn = document.getElementById('modal-login-btn');
		if (!btn) return;
		btn.addEventListener('click', function() {
			var email    = document.getElementById('modal-email').value.trim();
			var password = document.getElementById('modal-password').value;
			var remember = document.getElementById('modal-remember').checked ? '1' : '0';
			var errEl    = document.getElementById('form-login-error');
			errEl.style.display = 'none';
			if (!email || !password) { errEl.textContent = 'Заполните email и пароль'; errEl.style.display = ''; return; }
			btn.disabled = true;
			var fd = new FormData();
			fd.append('email', email);
			fd.append('password', password);
			fd.append('remember', remember);
			fetch('/authorization/ajax.php', { method: 'POST', body: fd })
				.then(function(r){ return r.json(); })
				.then(function(data){
					if (data.success) { window.location.href = data.redirect || '/profile/'; }
					else { errEl.textContent = data.message || 'Ошибка входа'; errEl.style.display = ''; btn.disabled = false; }
				})
				.catch(function(){ errEl.textContent = 'Ошибка соединения'; errEl.style.display = ''; btn.disabled = false; });
		});
	})();
	</script>
	<!-- Форма вступления -->
	<div class="form-membership" id="form-membership" style="display:none;max-width: 90%;">
		<div class="form-membership__left" id="modal-membership-left">
			<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/form-membership-basic.png" alt="">
			<p class="form-membership__text desk-block">
				Нажимая кнопку «Оплатить членский взнос» вы даете свое согласие на <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">обработку персональных данных</a>, а также с тем, что вам исполнилось 18 лет, вы студент или выпускник МВТУ (МГТУ) им. Н.Э. Баумана, вы разделяете цели Политехнического общества, признаёте его <a href="<?= defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#' ?>" target="_blank">Устав</a> и подтверждаете свое добровольное согласие на вступление в «МГТУ-Политех»
			</p>
		</div>
		<div class="form-membership__form" id="modal-membership-form">
			<input type="hidden" id="modal-membership-type" value="basic">
			<p id="modal-membership-error" style="display:none;color:#c0392b;margin-bottom:10px;font-size:13px;width:100%"></p>
			<input type="text"   id="modal-membership-lname" placeholder="Фамилия" required>
			<input type="text"   id="modal-membership-fname" placeholder="Имя" required>
			<input type="text"   id="modal-membership-sname" placeholder="Отчество" required>
			<input type="number" id="modal-membership-phone" placeholder="Номер телефона" required>
			<input type="email"  id="modal-membership-email" placeholder="e-mail" required>
			<select id="modal-membership-dept">
				<option value="">Кафедра</option>
			</select>
			<input class="form-membership__year" type="text" id="modal-membership-year" placeholder="Год окончания МВТУ (МГТУ) им. Н.Э. Баумана" required>
			<button class="btn form-membership__send" id="modal-membership-submit">Оплатить членский взнос</button>
		</div>
		<p class="form-membership__text desk-none">
			Нажимая кнопку «Оплатить членский взнос» вы даете свое согласие на <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">обработку персональных данных</a>, а также с тем, что вам исполнилось 18 лет, вы студент или выпускник МВТУ (МГТУ) им. Н.Э. Баумана, вы разделяете цели Политехнического общества, признаёте его <a href="<?= defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#' ?>" target="_blank">Устав</a> и подтверждаете свое добровольное согласие на вступление в «МГТУ-Политех»
		</p>
	</div>

	<!-- Форма: Связаться с организаторами / Стать партнёром -->
	<div class="form-finance-help" id="form-finance-help" style="display:none;">
		<h2 class="form-finance-help__title" id="ffh-title">Связаться с организаторами</h2>
		<div id="ffh-form-block">
			<p id="ffh-error" class="form-finance-help__error"></p>
			<div class="form-finance-help__fields">
				<input type="text"  id="ffh-name"  placeholder="Имя *" class="form-finance-help__input">
				<input type="email" id="ffh-email" placeholder="E-mail *" class="form-finance-help__input">
				<input type="tel"   id="ffh-phone" placeholder="Номер телефона" class="form-finance-help__input">
			</div>
			<button class="btn form-finance-help__btn" id="ffh-submit">Отправить</button>
			<p class="form-finance-help__consent">
				Нажимая кнопку «Отправить» вы даете свое согласие на <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">обработку персональных данных</a>
			</p>
		</div>
		<div id="ffh-ok" class="form-finance-help__success" style="display:none;">
			<div class="form-finance-help__success-icon">✓</div>
			<h3 class="form-finance-help__success-title">Заявка отправлена!</h3>
			<p class="form-finance-help__success-text">Мы свяжемся с вами в ближайшее время.</p>
		</div>
	</div>
	<script>
	(function(){
		var btn = document.getElementById('ffh-submit');
		if (!btn) return;
		btn.addEventListener('click', function(){
			var name  = (document.getElementById('ffh-name')?.value  || '').trim();
			var email = (document.getElementById('ffh-email')?.value || '').trim();
			var phone = (document.getElementById('ffh-phone')?.value || '').trim();
			var errEl = document.getElementById('ffh-error');
			errEl.style.display = 'none';
			if (!name)  { errEl.textContent = 'Введите имя';   errEl.style.display = ''; return; }
			if (!email) { errEl.textContent = 'Введите e-mail'; errEl.style.display = ''; return; }
			btn.disabled = true;
			var fd = new FormData();
			fd.append('name', name); fd.append('email', email);
			fd.append('phone', phone); fd.append('message', '');
			fetch('/local/ajax/contact.php', { method: 'POST', body: fd })
				.then(function(r){ return r.json(); })
				.then(function(data){
					if (data.success) {
						document.getElementById('ffh-form-block').style.display = 'none';
						document.getElementById('ffh-ok').style.display = '';
					} else {
						errEl.textContent = data.message || 'Ошибка. Попробуйте снова.';
						errEl.style.display = '';
						btn.disabled = false;
					}
				})
				.catch(function(){ errEl.textContent = 'Ошибка соединения'; errEl.style.display = ''; btn.disabled = false; });
		});
	})();
	</script>

	<script src="<?=SITE_TEMPLATE_PATH?>/assets/js/swiper-bundle.min.js"></script>
	<script src="<?=SITE_TEMPLATE_PATH?>/assets/js/fancybox.umd.js"></script>
	<script src="<?=SITE_TEMPLATE_PATH?>/assets/js/script.js"></script>
</body>
</html>
