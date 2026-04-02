<?php
/**
 * ВРЕМЕННЫЙ контроллер для разработки — удалить после завершения Блока 1.
 * Создаёт группы пользователей и UF_* поля в Bitrix.
 */
class SetupController
{
    /** Коды групп и их названия */
    private static array $groups = [
        'PO_REGISTERED'     => 'Зарегистрированный (без членства)',
        'PO_MEMBER_BASIC'   => 'Член общества — Базовое',
        'PO_MEMBER_PREMIUM' => 'Член общества — Привилегированное',
        'PO_PARTNER'        => 'Партнёр (юр. лицо)',
        'PO_MODERATOR'      => 'Модератор / Сотрудник',
    ];

    /** Пользовательские поля для сущности USER */
    private static array $userFields = [
        [
            'FIELD_NAME'        => 'UF_MEMBERSHIP_TYPE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_MEMBERSHIP_TYPE',
            'SORT'              => 100,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Тип членства', 'en' => 'Membership type'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Тип членства'],
            'LIST_FILTER_LABEL' => ['ru' => 'Тип членства'],
            'HELP_MESSAGE'      => ['ru' => 'basic / premium / partner / honorary'],
        ],
        [
            'FIELD_NAME'        => 'UF_MEMBERSHIP_STATUS',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_MEMBERSHIP_STATUS',
            'SORT'              => 110,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Статус заявки', 'en' => 'Membership status'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Статус заявки'],
            'LIST_FILTER_LABEL' => ['ru' => 'Статус заявки'],
            'HELP_MESSAGE'      => ['ru' => 'pending / active / rejected'],
        ],
        [
            'FIELD_NAME'        => 'UF_MEMBERSHIP_EXPIRES',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => 'UF_MEMBERSHIP_EXPIRES',
            'SORT'              => 120,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Членство до', 'en' => 'Membership expires'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Членство до'],
            'LIST_FILTER_LABEL' => ['ru' => 'Членство до'],
        ],
        [
            'FIELD_NAME'        => 'UF_GRADUATE_YEAR',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_GRADUATE_YEAR',
            'SORT'              => 200,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Год окончания МГТУ', 'en' => 'Graduation year'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Год выпуска'],
            'LIST_FILTER_LABEL' => ['ru' => 'Год выпуска'],
        ],
        [
            'FIELD_NAME'        => 'UF_GRADUATE_DEPT',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_GRADUATE_DEPT',
            'SORT'              => 210,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Кафедра / факультет', 'en' => 'Department'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Кафедра'],
            'LIST_FILTER_LABEL' => ['ru' => 'Кафедра'],
        ],
        [
            'FIELD_NAME'        => 'UF_TELEGRAM',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_TELEGRAM',
            'SORT'              => 220,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Telegram', 'en' => 'Telegram'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Telegram'],
            'LIST_FILTER_LABEL' => ['ru' => 'Telegram'],
        ],
        [
            'FIELD_NAME'        => 'UF_DIPLOMA_SERIES',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_DIPLOMA_SERIES',
            'SORT'              => 300,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Серия диплома', 'en' => 'Diploma series'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Серия диплома'],
            'LIST_FILTER_LABEL' => ['ru' => 'Серия диплома'],
        ],
        [
            'FIELD_NAME'        => 'UF_DIPLOMA_NUMBER',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_DIPLOMA_NUMBER',
            'SORT'              => 310,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Номер диплома', 'en' => 'Diploma number'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Номер диплома'],
            'LIST_FILTER_LABEL' => ['ru' => 'Номер диплома'],
        ],
        [
            'FIELD_NAME'        => 'UF_DIPLOMA_DATE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_DIPLOMA_DATE',
            'SORT'              => 320,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Дата выдачи диплома', 'en' => 'Diploma date'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Дата диплома'],
            'LIST_FILTER_LABEL' => ['ru' => 'Дата диплома'],
        ],
        [
            'FIELD_NAME'        => 'UF_COMPANY_ID',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_COMPANY_ID',
            'SORT'              => 400,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'ID компании (юр. лицо)', 'en' => 'Company ID'],
            'LIST_COLUMN_LABEL' => ['ru' => 'ID компании'],
            'LIST_FILTER_LABEL' => ['ru' => 'ID компании'],
        ],
    ];

    /**
     * Показывает текущее состояние: какие группы и поля уже существуют.
     */
    public function status(): array
    {
        $existingGroups = [];
        $dbGroups = CGroup::GetList([], []);
        while ($group = $dbGroups->Fetch()) {
            $existingGroups[$group['STRING_ID']] = (int)$group['ID'];
        }

        $existingFields = [];
        $oUserTypeManager = new CUserTypeManager();
        $fields = $oUserTypeManager->GetUserFields('USER', 0, LANGUAGE_ID);
        foreach ($fields as $code => $field) {
            $existingFields[] = $code;
        }

        $groupStatus = [];
        foreach (self::$groups as $code => $name) {
            $groupStatus[$code] = isset($existingGroups[$code])
                ? ['exists' => true, 'id' => $existingGroups[$code]]
                : ['exists' => false];
        }

        $fieldStatus = [];
        foreach (self::$userFields as $field) {
            $fieldStatus[$field['FIELD_NAME']] = in_array($field['FIELD_NAME'], $existingFields)
                ? ['exists' => true]
                : ['exists' => false];
        }

        return [
            'groups' => $groupStatus,
            'fields' => $fieldStatus,
        ];
    }

    /**
     * Создаёт все группы и UF_* поля, пропуская уже существующие.
     */
    public function install(): array
    {
        $result = [
            'groups' => [],
            'fields' => [],
            'errors' => [],
        ];

        // — Группы —
        $existingGroups = [];
        $dbGroups = CGroup::GetList([], []);
        while ($group = $dbGroups->Fetch()) {
            $existingGroups[$group['STRING_ID']] = (int)$group['ID'];
        }

        foreach (self::$groups as $code => $name) {
            if (isset($existingGroups[$code])) {
                $result['groups'][$code] = ['status' => 'exists', 'id' => $existingGroups[$code]];
                continue;
            }

            $oGroup = new CGroup();
            $groupId = $oGroup->Add([
                'ACTIVE'    => 'Y',
                'NAME'      => $name,
                'STRING_ID' => $code,
            ]);

            if ($groupId) {
                $result['groups'][$code] = ['status' => 'created', 'id' => (int)$groupId];
            } else {
                $result['errors'][] = "Группа {$code}: " . $oGroup->LAST_ERROR;
                $result['groups'][$code] = ['status' => 'error'];
            }
        }

        // — UF_* поля —
        $oUserTypeEntity = new CUserTypeEntity();
        $existingFields = [];
        $dbFields = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER']);
        while ($f = $dbFields->Fetch()) {
            $existingFields[$f['FIELD_NAME']] = (int)$f['ID'];
        }

        foreach (self::$userFields as $field) {
            $fieldName = $field['FIELD_NAME'];

            if (isset($existingFields[$fieldName])) {
                $result['fields'][$fieldName] = ['status' => 'exists', 'id' => $existingFields[$fieldName]];
                continue;
            }

            $fieldData = array_merge($field, ['ENTITY_ID' => 'USER']);
            $fieldId = $oUserTypeEntity->Add($fieldData);

            if ($fieldId) {
                $result['fields'][$fieldName] = ['status' => 'created', 'id' => (int)$fieldId];
            } else {
                $result['errors'][] = "Поле {$fieldName}: " . $oUserTypeEntity->LAST_ERROR;
                $result['fields'][$fieldName] = ['status' => 'error'];
            }
        }

        return $result;
    }
}
