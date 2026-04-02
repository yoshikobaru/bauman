<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Вступить в общество (юр. лицо)");

use Bitrix\Main\Loader;
$hlOk = Loader::includeModule('highloadblock');

$d7Done  = false;
$d7Error = '';
$d7Errors = [];

// D7: Индустриальное партнёрство
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['d7_action'])) {
    $fn      = trim($_POST['first_name']    ?? '');
    $ln      = trim($_POST['last_name']     ?? '');
    $sn      = trim($_POST['second_name']   ?? '');
    $email   = trim($_POST['email']         ?? '');
    $company = trim($_POST['company']       ?? '');
    $site    = trim($_POST['site']          ?? '');
    $count   = trim($_POST['rep_count']     ?? '');
    $charter = ($_POST['agree_charter']     ?? '') === 'yes';
    $pdAgree = ($_POST['agree_pd']          ?? '') === 'yes';

    if (!$fn || !$ln)      $d7Errors[] = 'Укажите ФИО представителя';
    if (!$email)           $d7Errors[] = 'Укажите электропочту';
    if (!$company)         $d7Errors[] = 'Укажите название компании';
    if (!$charter || !$pdAgree) $d7Errors[] = 'Необходимо согласие с Уставом и политикой ПДн';

    if (empty($d7Errors)) {
        $saved = false;
        if ($hlOk && defined('HL_APPLICATIONS_ID') && HL_APPLICATIONS_ID > 0) {
            $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::getById(HL_APPLICATIONS_ID)->fetch();
            if ($hlEntity) {
                $hlClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlEntity)->getDataClass();
                $res = $hlClass::add([
                    'UF_USER_ID'     => $USER->IsAuthorized() ? (int)$USER->GetID() : 0,
                    'UF_TYPE'        => 'partnership',
                    'UF_STATUS'      => 'new',
                    'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                    'UF_DATA'        => json_encode([
                        'last_name'   => $ln,  'first_name'  => $fn,
                        'second_name' => $sn,  'email'       => $email,
                        'company'     => $company, 'site'    => $site,
                        'rep_count'   => $count,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
                if ($res->isSuccess()) $saved = true;
                else $d7Error = 'Ошибка сохранения. Попробуйте позже.';
            }
        } else {
            $saved = true; // HL не настроен — принимаем без записи
        }
        if ($saved) {
            $d7Done = true;
            $d7Data = [
                'first_name'  => $fn,     'last_name'  => $ln,
                'second_name' => $sn,     'email'      => $email,
                'company'     => $company,'site'       => $site,
                'rep_count'   => $count,
            ];
            po_sendAdminEmail('partnership', $d7Data);
            po_createCrmLead('partnership', $d7Data);
        }
    } else {
        $d7Error = implode('; ', $d7Errors);
    }
}
?>

<main>
    <section class="join">
        <div class="container">
            <?php if ($d7Done): ?>
            <div class="join__wrapper">
                <h2 class="account__title main-title">Заявка принята!</h2>
                <p style="margin-top:16px;color:#666">
                    Ваша заявка на индустриальное партнёрство принята. Мы свяжемся с вами в ближайшее время.
                </p>
                <a href="/" class="btn" style="margin-top:24px">На главную</a>
            </div>
            <?php else: ?>
            <div class="join__wrapper">
                <h2 class="account__title main-title">Вступить в общество (юр. лицо)</h2>
                <?php if ($d7Error): ?>
                <div class="authorization__alert authorization__alert--error" style="margin-bottom:16px">
                    <p><?= htmlspecialchars($d7Error) ?></p>
                </div>
                <?php endif; ?>
                <form method="POST" action="/join-ur/">
                    <input type="hidden" name="d7_action" value="1">
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Данные представителя</h3>
                        </div>
                        <div class="account__personal-list account__grid--tripl">
                            <input type="text"  name="last_name"   placeholder="Фамилия *" required
                                   value="<?= htmlspecialchars($_POST['last_name']   ?? '') ?>">
                            <input type="text"  name="first_name"  placeholder="Имя *" required
                                   value="<?= htmlspecialchars($_POST['first_name']  ?? '') ?>">
                            <input type="text"  name="second_name" placeholder="Отчество"
                                   value="<?= htmlspecialchars($_POST['second_name'] ?? '') ?>">
                            <input type="email" name="email"       placeholder="Электропочта *" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="account__personal">
                        <div class="account__chapter">
                            <h3 class="account__subtitle">Сведения о компании</h3>
                        </div>
                        <div class="account__personal-list account__grid--range">
                            <input type="text" name="company"   placeholder="Компания *" required
                                   value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
                            <input type="text" name="site"      placeholder="Сайт"
                                   value="<?= htmlspecialchars($_POST['site']    ?? '') ?>">
                            <input type="number" name="rep_count" placeholder="Планируемое количество представителей" min="1"
                                   value="<?= htmlspecialchars($_POST['rep_count'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="join__politic">
                        <div class="join__politic-question">
                            <p class="join__politic-link">
                                Ознакомлен(а) и согласен(а) с <a href="#">Уставом</a> и <a href="#">Положением о членских взносах</a>
                            </p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_charter" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                        <div class="join__politic-question">
                            <p class="join__politic-link">Согласен с <a href="#">политикой обработки ПДн</a></p>
                            <div class="account__graduate-choice">
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="yes" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Да
                                </label>
                                <label class="account__graduate-item">
                                    <input type="radio" name="agree_pd" value="no" class="account__graduate-input">
                                    <span class="account__graduate-box"></span>Нет
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn authorization__btn">Вступить</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
