<?php

namespace Artamonov\Rest\Controllers\Native;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use CUser;
use CFile;

class User
{
    private $user;

    public function __construct()
    {
        if (!config()->get('useNativeRoute')) {
            response()->json('The use of native routes is disabled in the settings');
        }
    }

    /**
     * Получить данные пользователя по ID, логину или токену
     *
     * @param int $id
     * @return array|false
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function user(int $id = 0)
    {
        if (!$this->user) {
            $filter = [];
            if ($id) {
                $filter = ['=ID' => $id];
            } else if (request()->get('id')) {
                $filter = ['=ID' => request()->get('id')];
            } else if (request()->get('login')) {
                $filter = ['=LOGIN' => request()->get('login')];
            } else if (request()->get('token')) {
                $filter = ['=' . settings()->getTokenFieldCode() => request()->get('token')];
            }
            if ($filter) $this->user = UserTable::getList(['select' => ['*', 'UF_*'], 'filter' => $filter, 'limit' => 1])->fetchRaw();
            // Завершить процесс
            if (!$this->user['ID']) {
                journal()->add('request-response', [
                    'request' => request()->get(),
                    'response' => [
                        'error' => 'User not found'
                    ]
                ]);
                response()->notFound();
            }
        }
        return $this->user;
    }

    /**
     * Создать нового пользователя
     */
    public function create()
    {
        $user = new CUser;
        $arFields = array_change_key_case(request()->get(), CASE_UPPER);
        if ($id = $user->Add($arFields)) {
            $response = ['user' => $this->user($id)];
            journal()->add('request-response', [
                'request' => request()->get(),
                'response' => $response
            ]);
            response()->created($response);
        } else {
            $response = ['error' => $user->LAST_ERROR];
            journal()->add('request-response', [
                'request' => request()->get(),
                'response' => $response
            ]);
            response()->json($response);
        }
    }

    /**
     * Получить данные пользователя по ID, логину или токену
     */
    public function get()
    {
        $response = $this->user();
        journal()->add('request-response', [
            'request' => request()->get(),
            'response' => $response
        ]);
        response()->json($response);
    }

    /**
     * Обновить данные пользователя
     */
    public function update()
    {
        $arFields = array_change_key_case(request()->get(), CASE_UPPER);
        $arFields = array_intersect_key($arFields, $this->user());
        $user = new CUser;
        $user->Update($this->user()['ID'], $arFields);
        $response = $user->LAST_ERROR ? ['error' => $user->LAST_ERROR] : ['user' => array_merge($this->user(), $arFields)];
        journal()->add('request-response', [
            'request' => request()->get(),
            'response' => $response
        ]);
        response()->json($response);
    }

    /**
     * Удалить пользователя
     */
    public function delete()
    {
        global $DB;
        $id = $this->user()['ID'];
        $response = [];
        $strSql = 'SELECT F.ID FROM	b_user U, b_file F WHERE U.ID=' . $id . ' and (F.ID=U.PERSONAL_PHOTO or F.ID=U.WORK_LOGO)';
        $z = $DB->Query($strSql, false, 'FILE: ' . __FILE__ . ' LINE:' . __LINE__);
        while ($zr = $z->fetch()) {
            CFile::Delete($zr['ID']);
        }
        if (!$DB->Query('DELETE FROM b_user_group WHERE USER_ID=' . $id)) {
            $response['error'][] = 'Failed attempt to delete from table: b_user_group';
        }
        if (!$DB->Query('DELETE FROM b_user_digest WHERE USER_ID=' . $id)) {
            $response['error'][] = 'Failed attempt to delete from table: b_user_digest';
        }
        if (!$DB->Query('DELETE FROM b_app_password WHERE USER_ID=' . $id)) {
            $response['error'][] = 'Failed attempt to delete from table: b_app_password';
        }
        if (!$DB->Query('DELETE FROM b_user WHERE ID=' . $id . ' AND ID<>1')) {
            $response['error'][] = 'Failed attempt to delete from table: b_app_password';
        }
        if ($response['error']) {
            journal()->add('request-response', [
                'request' => request()->get(),
                'response' => $response
            ]);
            response()->json($response);
        } else {
            $response['successful'] = true;
            journal()->add('request-response', [
                'request' => request()->get(),
                'response' => $response
            ]);
            response()->ok();
        }
    }
}
