<?php

use \Artamonov\Rest\Foundation\Token;

require_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/interface/admin_lib.php';

const SM_SAFE_MODE = true;
const PERFMON_STOP = true;
const PUBLIC_AJAX_MODE = true;
const STOP_STATISTICS = true;
const NO_AGENT_STATISTIC = 'Y';
const NO_AGENT_CHECK = true;
const NO_KEEP_STATISTIC = true;
const DisableEventsCheck = true;

@set_time_limit(0);

$request =& $_POST;

$token = Token::getInstance();
$tokenCount = $token->generate($request['parameters'], $request['token']['update'] === 'Y');

if ($tokenCount > 0) {
    $request['token']['count'] += $tokenCount;
    CAdminMessage::ShowMessage([
        'TYPE' => 'PROGRESS',
        'DETAILS' => '#PROGRESS_BAR#' . '<div id="progress-bar-value" data-processed="' . $request['token']['count'] . '" style="margin-top: 10px;margin-bottom: -15px;"><span class="bx-ui-loc-ri-loader"></span>&nbsp;<span class="bx-ui-loc-ri-status-text">' . loc('ArtamonovRestTokenGenerated', ['#COUNT#' => $request['token']['count'], '#TOTAL_COUNT#' => $request['total']]) . '</span></div>',
        'HTML' => true,
        'PROGRESS_TOTAL' => 100,
        'PROGRESS_VALUE' => round(($request['token']['count'] / $request['total']) * 100, 1),
        'PROGRESS_TEMPLATE' => '<span class="bx-ui-loc-ri-percents">#PROGRESS_VALUE#</span>%'
    ]);
}
