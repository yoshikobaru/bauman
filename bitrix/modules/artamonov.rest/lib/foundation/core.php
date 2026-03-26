<?php

namespace Artamonov\Rest\Foundation;

use Bitrix\Main\EventManager;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class Core
{
    private static $_instance;

    public function run(): bool
    {
        if (!$this->checkAbilityRun()) {
            return false;
        }
        if (Config::getInstance()->get('useLateStart')) {
            EventManager::getInstance()->addEventHandler('main', 'OnProlog', ['\\Artamonov\\Rest\\Foundation\\Event', 'OnProlog']);
        } else {
            $this->start();
        }
        return true;
    }

    private function checkAbilityRun(): bool
    {
        if (
            defined('ADMIN_SECTION') ||
            mb_strpos($_SERVER['REQUEST_URI'], 'bitrix/') !== false ||
            !Config::getInstance()->get('useRestApi') ||
            !Config::getInstance()->get('pathRestApi')
        ) {
            return false;
        }
        if (Config::getInstance()->get('siteList') && defined('SITE_ID') === true) {
            $siteList = explode('|', Config::getInstance()->get('siteList'));
            if (!in_array(SITE_ID, $siteList)) {
                return false;
            }
        }
        if (Config::getInstance()->get('pathRestApi') === Helper::getInstance()->ROOT()) {
            return true;
        }
        $requestUri = $_SERVER['REQUEST_URI'];
        if (defined('SITE_DIR') === true && SITE_DIR !== '/') {
            $requestUri = str_replace(SITE_DIR, '', $requestUri);
        }
        $requestUri = explode('/', trim($requestUri, '/'))[0];
        if (Config::getInstance()->get('pathRestApi') === $requestUri) {
            return true;
        }
        return false;
    }


    public function start()
    {
        $this->constant();
        $this->header();
        $this->security();
        $this->controller();
        die;
    }

    private function constant()
    {
        define('SM_SAFE_MODE', true);
        define('PERFMON_STOP', true);
        define('PUBLIC_AJAX_MODE', true);
        define('STOP_STATISTICS', true);
        define('NO_AGENT_STATISTIC', 'Y');
        define('NO_AGENT_CHECK', true);
        define('NO_KEEP_STATISTIC', true);
        define('DisableEventsCheck', true);
    }

    private function header()
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 200');
        header('Copyright: 2017-' . date('Y') . ', ' . Settings::getInstance()->get('author')['copyright']);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD');

        $accessControlAllowHeaders = loc('ArtamonovRestAccessControlAllowHeadersDefaultValue');
        if (!empty(config()->get('accessControlAllowHeaders'))) {
            $accessControlAllowHeaders .= ', ' . config()->get('accessControlAllowHeaders');
        }
        header('Access-Control-Allow-Headers: ' . $accessControlAllowHeaders);

        if ($_SERVER['HTTP_ORIGIN'] && Config::getInstance()->get('useCorsFilter')) {
            $list = Config::getInstance()->get('corsListDomains');
            if (mb_strpos($list, '*') !== false) {
                header('Access-Control-Allow-Origin: *');
            } else {
                $list = str_replace(["\n", "\r"], '', $list);
                $list = explode(';', $list);
                if (in_array($_SERVER['HTTP_ORIGIN'], $list)) {
                    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
                }
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Max-Age: 604800');
            Response::getInstance()->ok();
        }
    }

    private function security()
    {
        // check controller
        $result = Security::getInstance()->hasController();
        if ($result['result'] === false) {
            Response::getInstance()->badRequest($result['message'] ?? '');
        }

        // check content type
        $result = Security::getInstance()->isValidContentType();
        if ($result['result'] === false) {
            Response::getInstance()->badRequest($result['message'] ?? '');
        }

        // check active
        $result = Security::getInstance()->isActive();
        if ($result['result'] === false) {
            Response::getInstance()->requestedHostUnavailable($result['message'] ?? '');
        }

        // check parameters
        if (!Security::getInstance()->isValidParameters()) {
            Response::getInstance()->badRequest(Security::getInstance()->getErrors());
        }

        // check authorization
        try {
            $result = Security::getInstance()->isAuthorized();
            if ($result['result'] === false) {
                Response::getInstance()->unauthorized($result['message'] ?? '');
            }
        } catch (ObjectPropertyException|SystemException $e) {
        }
    }

    private function controller()
    {
        Controller::getInstance()->run();
    }

    public static function getInstance(): Core
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    public function __call($name, $arguments)
    {
        Response::getInstance()->internalServerError('Method \'' . $name . '\' is not defined');
    }

    private function __clone()
    {
    }
}
