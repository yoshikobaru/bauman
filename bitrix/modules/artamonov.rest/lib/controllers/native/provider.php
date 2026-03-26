<?php

namespace Artamonov\Rest\Controllers\Native;


use \Bitrix\Main\Loader;
use \Bitrix\Main\LoaderException;

class Provider
{
    public function __construct()
    {
        if (!config()->get('useNativeRoute')) {
            response()->json('The use of native routes is disabled in the settings');
        }
    }

    public function get()
    {
        $response = [];

        $class = request()->get('class');
        $method = request()->get('method');

        try {
            $this->checkClass($class);
        } catch (LoaderException $e) {
        }

        try {
            $class = new \ReflectionClass($class);
            $response['class']['name'] = $class->getName();

            if ($class->getNamespaceName()) {
                $response['class']['namespace'] = $class->getNamespaceName();
            }
            if ($class->getDocComment()) {
                $response['class']['doc'] = $class->getDocComment();
            }
            $response['class']['file'] = $class->getFileName();

            if (!$class->hasMethod($method)) {
                $response['methods'] = $class->getMethods();
                journal()->add('request-response', ['request' => request()->get(), 'response' => $response]);
                response()->json($response);
            }

            $phpVersion = explode('.', PHP_VERSION)[0];

            $method = $class->getMethod($method);
            $response['method']['name'] = $method->getName();

            if ($method->getDocComment()) {
                $response['method']['doc'] = $method->getDocComment();
            }

            $response['method']['deprecated'] = $method->isDeprecated();
            $response['method']['static'] = $method->isStatic();

            $args = $method->getParameters();
            if (count($args) > 0) {
                foreach ($args as $arg) {
                    $param['name'] = $arg->getName();
                    if ($arg->isDefaultValueAvailable()) {
                        $param['defaultValue'] = $arg->getDefaultValue();
                    }
                    if ($phpVersion >= 7 && $arg->hasType()) {
                        $param['type'] = $arg->getType();
                    }
                    $param['isPassedByReference'] = $arg->isPassedByReference();
                    $response['method']['parameters'][$param['name']] = $param;
                }
            }

        } catch (\ReflectionException $e) {
        }

        journal()->add('request-response', ['request' => request()->get(), 'response' => $response]);
        response()->json($response);
    }

    public function exec()
    {
        $response = [];

        $class = request()->get('class');
        $method = request()->get('method');
        $args = request()->get('parameters');
        $callback = request()->get('callback');

        try {
            $this->checkClass($class);
        } catch (LoaderException $e) {
        }

        try {
            $rClass = new \ReflectionClass($class);
            $this->checkMethod($rClass, $method);
            $method = $rClass->getMethod($method);
            $object = null;

            if (!$method->isStatic()) {
                $object = new $class();
            }

            if (count($args) > 0) {
                $result = $method->invokeArgs($object, $args);
            } else {
                $result = $method->invoke($object);
            }

            if ($result) {

                if (is_object($result)) {

                    if (method_exists($result, 'SelectedRowsCount')) { // old api

                        if (!$callback) $callback = 'fetch';

                        if ($result->SelectedRowsCount() > 1) {
                            if (!method_exists($result, $callback)) {
                                $response = [
                                    'error' => true,
                                    'message' => 'Result doesn`t have a ' . $callback . '() method'
                                ];
                                journal()->add('request-response', ['request' => request()->get(), 'response' => $response]);
                                response()->json($response);
                            }

                            $compareCallback = mb_strtolower($callback);

                            if ($compareCallback === 'fetch') {
                                while ($row = $result->$callback()) {
                                    $response[] = $row;
                                }
                            } else {
                                $response = $result->$callback();
                            }

                        } else {
                            $response = $result->$callback();
                        }

                    } else if (method_exists($result, 'getSelectedRowsCount')) { // d7 api

                        if (!$callback) $callback = 'fetchRaw';

                        if ($result->getSelectedRowsCount() > 1) {
                            if (!method_exists($result, $callback)) {
                                $response = [
                                    'error' => true,
                                    'message' => 'Result doesn`t have a ' . $callback . '() method'
                                ];
                                journal()->add('request-response', ['request' => request()->get(), 'response' => $response]);
                                response()->json($response);
                            }

                            $compareCallback = mb_strtolower($callback);

                            if ($compareCallback === 'fetch' || $compareCallback === 'fetchraw') {
                                while ($row = $result->$callback()) {
                                    $response[] = $row;
                                }
                            } else {
                                $response = $result->$callback();
                            }

                        } else {
                            $response = $result->$callback();
                        }
                    }

                } else {
                    $response = $result;
                }

            } else if ($object->LAST_ERROR) {
                $response = [
                    'error' => true,
                    'message' => $object->LAST_ERROR
                ];
                journal()->add('request-response', ['request' => request()->get(), 'response' => $response]);
                response()->json($response);
            }

        } catch (\ReflectionException $e) {
        }
        journal()->add('request-response', ['request' => request()->get(), 'response' => $response]);
        response()->json($response);
    }

    /**
     * @param $class
     * @return bool
     * @throws LoaderException
     */
    private function checkClass($class)
    {
        if (!class_exists($class)) {
            $map = [
                'cadvbanner' => 'advertising',
                'cadvcontract' => 'advertising',
                'cadvtype' => 'advertising',
                'cbpactivity' => 'bizproc',
                'cbpdocument' => 'bizproc',
                'cbpruntime' => 'bizproc',
                'cbpworkflow' => 'bizproc',
                'cextra' => 'catalog',
                'cprice' => 'catalog',
                'cfilterdictionary' => 'forum',
                'cfilterletter' => 'forum',
                'cfilterunquotablewords' => 'forum',
                'ccertification' => 'learning',
                'cchapter' => 'learning',
                'ccourse' => 'learning',
                'ccourseimport' => 'learning',
                'ccoursepackage' => 'learning',
                'cgradebook' => 'learning',
                'clanswer' => 'learning',
                'clquestion' => 'learning',
                'clesson' => 'learning',
                'cstudent' => 'learning',
                'ctest' => 'learning',
                'ctestattempt' => 'learning',
                'ctestresult' => 'learning',
                'cpushmanager' => 'pull',
                'csitemap' => 'search',
                'csocnetfeatures' => 'socialnetwork',
                'csocnetfeaturesPerms' => 'socialnetwork',
                'csocnetgroup' => 'socialnetwork',
                'csocnetgroupSubject' => 'socialnetwork',
                'csocnetmessages' => 'socialnetwork',
                'csocnetsmile' => 'socialnetwork',
                'csocnettextparser' => 'socialnetwork',
                'csocnettools' => 'socialnetwork',
                'csocnetuser' => 'socialnetwork',
                'csocnetuserperms' => 'socialnetwork',
                'csocnetuserrelations' => 'socialnetwork',
                'csocnetusertogroup' => 'socialnetwork',
                'cadv' => 'statistic',
                'cautodetect' => 'statistic',
                'ccountry' => 'statistic',
                'cguest' => 'statistic',
                'chit' => 'statistic',
                'cpage' => 'statistic',
                'cpath' => 'statistic',
                'cphrase' => 'statistic',
                'creferer' => 'statistic',
                'csearcher' => 'statistic',
                'csearcherHit' => 'statistic',
                'csession' => 'statistic',
                'cstatevent' => 'statistic',
                'cstateventType' => 'statistic',
                'cstatistics' => 'statistic',
                'cstoplist' => 'statistic',
                'ctraffic' => 'statistic',
                'cuseronline' => 'statistic',
                'cpostinggeneral' => 'subscribe',
                'cposting' => 'subscribe',
                'csubscriptiongeneral' => 'subscribe',
                'csubscription' => 'subscribe',
                'crubric' => 'subscribe',
                'cpostingtemplate' => 'subscribe',
                'cticket' => 'support',
                'cticketdictionary' => 'support',
            ];
            $blackList = [
                '..',
                '.',
                settings()->get('module')['id'],
                'bitrix.eshop',
                'bitrix.sitecommunity',
                'bitrix.sitecorporate',
                'bitrix.siteinfoportal',
                'bitrix.sitepersonal',
            ];
            $classSearch = mb_strtolower($class);
            $module = $map[$classSearch];
            if ($module && Loader::includeModule($module) && class_exists($class)) {
                return true;
            }
            $modulesPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules';
            $modules = array_diff(scandir($modulesPath), $blackList);
            if (count($modules) > 0) {
                foreach ($modules as $module) {
                    if (is_file($modulesPath . '/' . $module) || mb_strpos($classSearch, $module) === false) continue;
                    if (Loader::includeModule($module) && class_exists($class)) {
                        return true;
                    }
                }
            }
            $response = [
                'error' => true,
                'message' => 'Class ' . $class . ' doesn`t exist'
            ];
            journal()->add('request-response', ['request' => request()->get(), 'response' => $response]);
            response()->json($response);
        }
        return true;
    }

    /**
     * @param $class
     * @param $method
     */
    private function checkMethod($class, $method)
    {
        if (!$class->hasMethod($method)) {
            $response = [
                'error' => true,
                'message' => 'Method ' . $method . ' doesn`t exist'
            ];
            journal()->add('request-response', ['request' => request()->get(), 'response' => $response]);
            response()->json($response);
        }
    }
}
