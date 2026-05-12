<?php

if (!function_exists('po_render_industrial_partnership_form')) {
    /**
     * Общая разметка формы «Индустриальное партнёрство» (D7 на /join/ или UR на /registration/).
     *
     * @param array{
     *   prefix: 'd7'|'ur',
     *   action: string,
     *   hidden_name: string,
     *   hidden_value?: string,
     *   post?: array,
     *   defaults?: array{email?: string},
     *   email_placeholder?: string,
     *   consent_label_html?: string|null,
     *   extra_after_consent?: string,
     *   company_subtitle_extra?: string
     * } $cfg
     */
    function po_render_industrial_partnership_form(array $cfg): void
    {
        $prefix = $cfg['prefix'];
        if ($prefix !== 'd7' && $prefix !== 'ur') {
            return;
        }

        $p            = $prefix . '_';
        $action       = htmlspecialchars($cfg['action'], ENT_QUOTES, 'UTF-8');
        $hiddenName   = htmlspecialchars($cfg['hidden_name'], ENT_QUOTES, 'UTF-8');
        $hiddenValue  = htmlspecialchars($cfg['hidden_value'] ?? '1', ENT_QUOTES, 'UTF-8');
        $post         = $cfg['post'] ?? [];
        $defaults     = $cfg['defaults'] ?? [];
        $politika     = defined('DOC_POLITIKA_URL') ? DOC_POLITIKA_URL : '#';
        $politikaEsc  = htmlspecialchars($politika, ENT_QUOTES, 'UTF-8');

        $emailPh = $cfg['email_placeholder']
            ?? ($prefix === 'ur' ? 'e-mail *' : 'Email *');

        $fv = static function (array $post, string $key, string $default = ''): string {
            $v = $post[$key] ?? $default;

            return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        };

        $companyKey = $p . 'company';
        $siteKey    = $p . 'site';
        $contactKey = $p . 'contact';
        $emailKey   = $p . 'email';
        $phoneKey   = $p . 'phone';
        $agreeKey   = $p . 'agree_pd';

        $emailDefault = (string)($defaults['email'] ?? '');
        if (array_key_exists($emailKey, $post)) {
            $emailVal = $fv($post, $emailKey);
        } else {
            $emailVal = htmlspecialchars($emailDefault, ENT_QUOTES, 'UTF-8');
        }

        $consentLabel = $cfg['consent_label_html'] ?? null;
        if ($consentLabel === null) {
            if ($prefix === 'ur') {
                $consentLabel = 'Ознакомлен с <a href="' . $politikaEsc
                    . '" target="_blank">политикой обработки ПДн</a> *';
            } else {
                $consentLabel = 'Согласен с <a href="' . $politikaEsc
                    . '" target="_blank">политикой обработки ПДн *</a>';
            }
        }

        $agreeChecked = !empty($post[$agreeKey]) ? ' checked' : '';
        $extraAfter   = $cfg['extra_after_consent'] ?? '';
        $companyExtra = $cfg['company_subtitle_extra'] ?? '';

        $phoneTitle   = 'Допустимы только цифры, пробел, символы + и −';
        $phonePattern = '[0-9 \+\-]*';
        ?>
<form method="POST" action="<?= $action ?>">
                    <input type="hidden" name="<?= $hiddenName ?>" value="<?= $hiddenValue ?>">
                    <div class="account__personal">
                        <div class="account__chapter"><h3 class="account__subtitle">Данные компании<?= $companyExtra ?></h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="<?= htmlspecialchars($companyKey, ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="Название компании *" required
                                   value="<?= $fv($post, $companyKey) ?>">
                            <input type="text" name="<?= htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="Сайт компании"
                                   value="<?= $fv($post, $siteKey) ?>">
                        </div>
                    </div>
                    <div class="account__personal" style="margin-top:24px">
                        <div class="account__chapter"><h3 class="account__subtitle">Контакты представителя</h3></div>
                        <div class="account__personal-list account__grid">
                            <input type="text" name="<?= htmlspecialchars($contactKey, ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="ФИО представителя *" required
                                   value="<?= $fv($post, $contactKey) ?>">
                            <input type="email" name="<?= htmlspecialchars($emailKey, ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="<?= htmlspecialchars($emailPh, ENT_QUOTES, 'UTF-8') ?>" required
                                   value="<?= $emailVal ?>">
                            <input type="tel" name="<?= htmlspecialchars($phoneKey, ENT_QUOTES, 'UTF-8') ?>"
                                   class="js-partnership-phone"
                                   placeholder="Телефон *" required
                                   pattern="<?= htmlspecialchars($phonePattern, ENT_QUOTES, 'UTF-8') ?>"
                                   title="<?= htmlspecialchars($phoneTitle, ENT_QUOTES, 'UTF-8') ?>"
                                   inputmode="tel"
                                   autocomplete="tel"
                                   maxlength="25"
                                   value="<?= $fv($post, $phoneKey) ?>">
                        </div>
                    </div>
                    <div class="join__politic" style="margin-top:24px">
                        <div class="join__politic-question">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                                <input type="checkbox" name="<?= htmlspecialchars($agreeKey, ENT_QUOTES, 'UTF-8') ?>"
                                       id="<?= htmlspecialchars($agreeKey, ENT_QUOTES, 'UTF-8') ?>" required
                                       style="width:18px;height:18px;flex-shrink:0"<?= $agreeChecked ?>>
                                <span class="join__politic-link"><?= $consentLabel ?></span>
                            </label>
                            <?= $extraAfter ?>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn" style="margin-top:24px">Отправить заявку на партнёрство</button>
                </form>
<script>
(function () {
    function bindPartnershipPhone(inp) {
        if (!inp || inp.dataset.poPhoneMaskBound) return;
        inp.dataset.poPhoneMaskBound = '1';
        var maxLen = 25;
        inp.addEventListener('input', function () {
            var v = inp.value.replace(/[^\d+\-\s]/g, '');
            if (v.length > maxLen) v = v.slice(0, maxLen);
            inp.value = v;
        });
    }
    document.querySelectorAll('input.js-partnership-phone').forEach(bindPartnershipPhone);
})();
</script>
        <?php
    }
}
