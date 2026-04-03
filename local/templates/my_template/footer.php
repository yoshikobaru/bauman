<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
	<!-- footer -->
	<footer class="footer">
		<div class="container">
			<a href="mailto:info@bauman-polytech.ru" class="footer-mail">
				info@bauman-polytech.ru
			</a>
			<a href="#" class="footer-address">
				Москва, Бригадирский переулок, 13, 4 этаж, каб. 407
			</a>
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
		<div class="footer-list">
			<a href="#" target="_blank">
				Политехническое Общество Выпускников МВТУ (МГТУ) им. Н.Э. Баумана
			</a>
			<a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">
				Политика обработки персональных данных
			</a>
		</div>
		</div>
	</footer>

	<!-- Форма входа -->
	<div class="form-login" id="form-login" style="display:none;max-width:500px;">
		<p id="form-login-error" style="display:none;color:#c0392b;margin-bottom:12px"></p>
		<input type="email"    id="modal-email"    placeholder="Электропочта" required>
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
			<a href="/join/" class="btn form-login__btn form-login__btn--register btn-empty">Зарегистрироваться</a>
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
		<div class="form-membership__left">
			<img src="<?=SITE_TEMPLATE_PATH?>/assets/img/form-membership-basic.png" alt="">
			<p class="form-membership__text desk-block">
				Нажимая кнопку «Оплатить членский взнос» вы даете свое согласие на <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">обработку персональных данных</a>, а также с тем, что вам исполнилось 18 лет, вы студент или выпускник МГТУ им. Н.Э. Баумана, вы разделяете цели Политехнического общества, признаёте его <a href="<?= defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#' ?>" target="_blank">Устав</a> и подтверждаете свое добровольное согласие на вступление в «МГТУ-Политех»
			</p> 
		</div>
		<form action="" class="form-membership__form">
			<input type="text" placeholder="Фамилия" required>
			<input type="text" placeholder="Имя" required>
			<input type="text" placeholder="Отчество" required>
			<input type="number" placeholder="Номер телефона" required>
			<input type="email" placeholder="Електропочта" required>
			<select name="Department" id="">
				<option value="">Кафедра</option>
				<option value="Кафедра">Кафедра</option>
				<option value="Кафедра">Кафедра</option>
				<option value="Кафедра">Кафедра</option>
			</select>
			<input class="form-membership__year" type="text" placeholder="Год окончания МГТУ им. Баумана" required>
			<button class="btn form-membership__send">Оплатить членский взнос</button>
		</form>
		<p class="form-membership__text desk-none">
			Нажимая кнопку «Оплатить членский взнос» вы даете свое согласие на <a href="<?= defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#' ?>" target="_blank">обработку персональных данных</a>, а также с тем, что вам исполнилось 18 лет, вы студент или выпускник МГТУ им. Н.Э. Баумана, вы разделяете цели Политехнического общества, признаёте его <a href="<?= defined('DOC_USTAV_URL') ? DOC_USTAV_URL : '#' ?>" target="_blank">Устав</a> и подтверждаете свое добровольное согласие на вступление в «МГТУ-Политех»
		</p> 
	</div>

	<script src="<?=SITE_TEMPLATE_PATH?>/assets/js/swiper-bundle.min.js"></script>
	<script src="<?=SITE_TEMPLATE_PATH?>/assets/js/fancybox.umd.js"></script>
	<script src="<?=SITE_TEMPLATE_PATH?>/assets/js/script.js"></script>
	<script>
	// Принудительное скачивание .docx и .pdf документов
	document.addEventListener('DOMContentLoaded', function() {
		document.querySelectorAll('a[href*=".docx"], a[href*=".pdf"]').forEach(function(a) {
			a.setAttribute('download', '');
		});
	});
	</script>
</body>
</html>
