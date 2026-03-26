<?php

namespace Artamonov\Rest\Foundation;


use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use CSite;
use CUserTypeEntity;

class Config
{
    private static $_instance;
    private $parameters;
    private $data;

    public function get($code)
    {
        if (!is_array($this->parameters)) {
            try {
                $this->parameters = Option::getForModule(Settings::getInstance()->get('module')['id']);
            } catch (ArgumentNullException $e) {
                return false;
            }
        }
        return $this->parameters[$code] ?? $this->parameters[mb_strtolower($code)] ?? false;
    }

    public function save()
    {
        $this->prepare();
        foreach ($this->data as $code => $value) {
            try {
                Option::set(Settings::getInstance()->get('module')['id'], $code, $value, false);
                $this->parameters[$code] = $value;
            } catch (ArgumentOutOfRangeException $e) {
            }
        }
        $this->addTokenField();
        $this->registerEventHandlerForGenerateToken();
    }

    public function restore()
    {
        $this->prepare();
        foreach ($this->data as $code => $value) {
            try {
                Option::delete(Settings::getInstance()->get('module')['id'], ['name' => $code]);
                unset($this->parameters[$code], $this->parameters[mb_strtolower($code)]);
            } catch (ArgumentNullException $e) {
            }
        }
        $this->registerEventHandlerForGenerateToken();
    }

    private function prepare()
    {
        $configs =& $_POST;

        switch ($configs['form']) {

            case 'rest-api-config':
                // Checkboxes
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useRestApi'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useRestApi'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useLateStart'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useLateStart'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useJournal'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useJournal'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'showExamples'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'showExamples'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useExampleRoute'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useExampleRoute'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useNativeRoute'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useNativeRoute'] = false;
                }
                // Arrays
                if (isset($configs[Settings::getInstance()->get('config')['prefix'] . 'siteList'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'siteList'] = implode('|', $configs[Settings::getInstance()->get('config')['prefix'] . 'siteList']);
                } else {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'siteList'] = false;
                    $by = 'ID';
                    $order = 'ASC';
                    $result = CSite::GetList($by, $order, ['ACTIVE' => 'Y', 'DEFAULT' => 'Y']);
                    while ($site = $result->fetch()) {
                        $configs[Settings::getInstance()->get('config')['prefix'] . 'siteList'] = $site['ID'];
                    }
                }
                if (isset($configs[Settings::getInstance()->get('config')['prefix'] . 'accessDocumentation'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessDocumentation'] = implode('|', $configs[Settings::getInstance()->get('config')['prefix'] . 'accessDocumentation']);
                } else {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessDocumentation'] = false;
                }
                if (isset($configs[Settings::getInstance()->get('config')['prefix'] . 'accessSecurity'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessSecurity'] = implode('|', $configs[Settings::getInstance()->get('config')['prefix'] . 'accessSecurity']);
                } else {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessSecurity'] = false;
                }
                if (isset($configs[Settings::getInstance()->get('config')['prefix'] . 'accessJournal'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessJournal'] = implode('|', $configs[Settings::getInstance()->get('config')['prefix'] . 'accessJournal']);
                } else {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessJournal'] = false;
                }
                if (isset($configs[Settings::getInstance()->get('config')['prefix'] . 'accessSupport'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessSupport'] = implode('|', $configs[Settings::getInstance()->get('config')['prefix'] . 'accessSupport']);
                } else {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessSupport'] = false;
                }
                if (isset($configs[Settings::getInstance()->get('config')['prefix'] . 'accessMenuItems'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessMenuItems'] = implode('|', $configs[Settings::getInstance()->get('config')['prefix'] . 'accessMenuItems']);
                } else {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'accessMenuItems'] = false;
                }
                // Strings
                if (!empty($configs[Settings::getInstance()->get('config')['prefix'] . 'pathRestApi'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'pathRestApi'] = str_replace(' ', '', trim($configs[Settings::getInstance()->get('config')['prefix'] . 'pathRestApi'], '/'));
                }
                if (!empty($configs[Settings::getInstance()->get('config')['prefix'] . 'localRouteMap'])) {
                    $localRouteMap = str_replace(' ', '', trim($configs[Settings::getInstance()->get('config')['prefix'] . 'localRouteMap'], '/'));
                    if (!Directory::isDirectoryExists(Loader::getDocumentRoot() . '/' . $localRouteMap)) {
                        echo \CAdminMessage::ShowMessage(loc('ArtamonovRestLocalRouteMapPathError', ['#PATH#' => $localRouteMap]));
                        $localRouteMap = '';
                    }
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'localRouteMap'] = $localRouteMap;
                }
                break;

            case 'rest-api-security':
                // Checkboxes
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useLoginPassword'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useLoginPassword'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useToken'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useToken'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'checkExpireToken'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'checkExpireToken'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useGenerateTokenRegisterUser'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useGenerateTokenRegisterUser'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useRequestLimit'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useRequestLimit'] = false;
                }
                if (!isset($configs[Settings::getInstance()->get('config')['prefix'] . 'useCorsFilter'])) {
                    $configs[Settings::getInstance()->get('config')['prefix'] . 'useCorsFilter'] = false;
                }
                if (isset($configs[Settings::getInstance()->get('config')['prefix'] . 'corsListDomains'])) {
                    $list = &$configs[Settings::getInstance()->get('config')['prefix'] . 'corsListDomains'];
                    $list = str_replace([' ', "\n\r", "\n", "\r"], '', $list);
                    $list = explode(';', $list);
                    foreach ($list as &$item) {
                        $item = rtrim($item, '/');
                    }
                    $list = implode(";\n", $list);
                }
                // Prepare request limit parameters
                $configs[Settings::getInstance()->get('config')['prefix'] . 'requestLimit'] = [];
                foreach ($configs as $key => $value) {
                    if (mb_strpos($key, 'data:requestLimit') !== false) {
                        $code = explode('-', mb_strtolower(str_replace('data:requestLimit', '', $key)));
                        $id = $code[1];
                        $code = $code[0];
                        $configs[Settings::getInstance()->get('config')['prefix'] . 'requestLimit'][$id][$code] = $value;
                        unset($configs[$key]);
                    }
                }
                $configs[Settings::getInstance()->get('config')['prefix'] . 'requestLimit'] = json_encode($configs[Settings::getInstance()->get('config')['prefix'] . 'requestLimit']);
                break;
        }

        foreach ($configs as $key => $value) {
            if (mb_stripos($key, Settings::getInstance()->get('config')['prefix']) !== false) {
                $code = str_replace(Settings::getInstance()->get('config')['prefix'], '', $key);
                $this->data[$code] = trim($value);
            }
        }
    }

    private function addTokenField()
    {
        $entity = new CUserTypeEntity;
        $fieldToken = [
            'ENTITY_ID' => 'USER',
            'FIELD_NAME' => Settings::getInstance()->getTokenFieldCode(),
            'XML_ID' => Settings::getInstance()->getTokenFieldCode(),
            'USER_TYPE_ID' => 'string',
            'SORT' => 100,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 40,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => ''
            ],
            'EDIT_FORM_LABEL' => [
                'en' => '',
                'ru' => loc('ArtamonovRestTokenField')
            ],
            'LIST_COLUMN_LABEL' => [
                'en' => '',
                'ru' => loc('ArtamonovRestTokenField')
            ],
            'LIST_FILTER_LABEL' => [
                'en' => '',
                'ru' => loc('ArtamonovRestTokenField')
            ],
            'HELP_MESSAGE' => [
                'en' => '',
                'ru' => loc('ArtamonovRestTokenFieldHint', ['#MODULE_NAME#' => Settings::getInstance()->get('module')['name']])
            ],
        ];
        $entity->Add($fieldToken);
        $entity = new CUserTypeEntity;
        $fieldTokenExpire = [
            'ENTITY_ID' => 'USER',
            'FIELD_NAME' => Settings::getInstance()->get('config')['token']['expire']['code'],
            'XML_ID' => Settings::getInstance()->get('config')['token']['expire']['code'],
            'USER_TYPE_ID' => 'datetime',
            'SORT' => 100,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 40,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => ''
            ],
            'EDIT_FORM_LABEL' => [
                'en' => '',
                'ru' => loc('ArtamonovRestTokenFieldExpire')
            ],
            'LIST_COLUMN_LABEL' => [
                'en' => '',
                'ru' => loc('ArtamonovRestTokenFieldExpire')
            ],
            'LIST_FILTER_LABEL' => [
                'en' => '',
                'ru' => loc('ArtamonovRestTokenFieldExpire')
            ],
            'HELP_MESSAGE' => [
                'en' => '',
                'ru' => loc('ArtamonovRestTokenFieldHint', ['#MODULE_NAME#' => Settings::getInstance()->get('module')['name']])
            ],
        ];
        $entity->Add($fieldTokenExpire);
    }

    private function registerEventHandlerForGenerateToken()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        if ($this->get('useGenerateTokenRegisterUser')) {
            $eventManager->registerEventHandler('main', 'OnBeforeUserAdd', 'main', '\\Artamonov\\Rest\\Foundation\\Event', 'OnBeforeUserAdd');
            $eventManager->registerEventHandler('main', 'OnBeforeUserRegister', 'main', '\\Artamonov\\Rest\\Foundation\\Event', 'OnBeforeUserRegister');
            $eventManager->registerEventHandler('main', 'OnBeforeUserSimpleRegister', 'main', '\\Artamonov\\Rest\\Foundation\\Event', 'OnBeforeUserSimpleRegister');
        } else {
            $eventManager->unRegisterEventHandler('main', 'OnBeforeUserAdd', 'main', '\\Artamonov\\Rest\\Foundation\\Event', 'OnBeforeUserAdd');
            $eventManager->unRegisterEventHandler('main', 'OnBeforeUserRegister', 'main', '\\Artamonov\\Rest\\Foundation\\Event', 'OnBeforeUserRegister');
            $eventManager->unRegisterEventHandler('main', 'OnBeforeUserSimpleRegister', 'main', '\\Artamonov\\Rest\\Foundation\\Event', 'OnBeforeUserSimpleRegister');
        }
    }

    public static function getInstance(): Config
    {
        if (is_null(self::$_instance)) {
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
