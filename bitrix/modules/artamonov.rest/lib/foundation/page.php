<?php

namespace Artamonov\Rest\Foundation;

use Bitrix\Main\Localization\Loc;

class Page
{
    private static $_instance;

    public function checkAccess($code)
    {
        if ($code === 'accessConfig' && !$GLOBALS['USER']->IsAdmin()) {
            $GLOBALS['APPLICATION']->AuthForm(loc('ArtamonovRestAccessDenied'));
        } elseif ($code === 'accessMenuItems') {
            $groupsAccess = explode('|', config()->get($code));
            $userGroups = $GLOBALS['USER']->GetUserGroupArray();
            return !security()->checkAccessByGroups($groupsAccess, $userGroups) ? false : true;
        } else {
            $groupsAccess = explode('|', config()->get($code));
            $userGroups = $GLOBALS['USER']->GetUserGroupArray();
            if (!security()->checkAccessByGroups($groupsAccess, $userGroups) || !$this->checkAccess('accessMenuItems')) {
                $GLOBALS['APPLICATION']->AuthForm(loc('ArtamonovRestAccessDenied'));
            }
        }
    }

    public function loadLanguage($file)
    {
        Loc::loadLanguageFile($file);
    }

    public function loadMessages($file)
    {
        Loc::loadMessages($file);
    }

    public function loadCustomMessages($file)
    {
        Loc::loadCustomMessages($file);
    }

    public function setTitle($title)
    {
        $GLOBALS['APPLICATION']->SetTitle($title);
    }

    public function addCss($file)
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . $file)) {
            CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . settings()->get('module')['id'] . '/install/css', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/css', true, true);
        }
        $GLOBALS['APPLICATION']->SetAdditionalCSS($file);
    }

    public function addJs($file)
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . $file)) {
            CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . settings()->get('module')['id'] . '/install/js', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/' . settings()->get('module')['id'], true, true);
        }
        $GLOBALS['APPLICATION']->AddHeadScript($file);
    }

    public function addHeadString($string)
    {
        $GLOBALS['APPLICATION']->AddHeadString($string, true);
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance) && defined('ADMIN_SECTION')) {
            self::$_instance = new self;
            Loc::loadLanguageFile(__FILE__);
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    public function __call($name, $arguments)
    {
        response()->internalServerError('Method \'' . $name . '\' is not defined');
    }

    private function __clone()
    {
    }
}
