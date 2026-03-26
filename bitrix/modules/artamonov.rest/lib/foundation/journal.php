<?php

namespace Artamonov\Rest\Foundation;


class Journal
{
    private static $_instance;

    public function add($journal, $data = [], string $clientId = '')
    {
        switch ($journal) {
            case 'request-response':
                $this->addRequestResponse($data['request'], $data['response'], $clientId);
                break;
            case 'request-limit':
                $this->addRequestLimit($data, $clientId);
                break;
        }
    }

    public function get($type, $filter = [], $sort = [], $limit = 20)
    {
        switch ($type) {
            case 'request-response':
                return $this->getRequestResponse($filter, $sort, $limit);
            case 'request-limit':
                return $this->getRequestLimit($filter, $sort);
            default:
                return false;
        }
    }

    public function delete($type, $ids)
    {
        switch ($type) {
            case 'request-response':
                $this->deleteRequestResponse($ids);
                break;
        }
        switch ($type) {
            case 'request-limit':
                $this->deleteRequestLimit($ids);
                break;
        }
    }

    // Request-Response

    private function addRequestResponse($request = [], $response = [], string $clientId = '')
    {
        if (!config()->get('useJournal')) return false;
        if ($clientId === '') {
            if (security()->get()['auth']['type'] === helper()->login()) {
                $clientId = request()->header('authorization-login');
            } else {
                if (request()->header('authorization-token')) {
                    $clientId = request()->header('authorization-token');
                    if (config()->get('tokenKey')) {
                        $clientId = explode(':', request()->header('authorization-token'));
                        $clientId = $clientId[1];
                    }
                }
            }
        }
        if (!$request) {
            $request = [
                'path' => request()->path(),
                'params' => request()->get(),
                'map' => request()->map(),
            ];
        }
        $data = [
            'METHOD' => request()->method(),
            'IP' => request()->ip(),
            'CLIENT_ID' => $clientId,
            'DATETIME' => new \Bitrix\Main\Type\DateTime(),
            'REQUEST' => json_encode($request, JSON_UNESCAPED_UNICODE),
        ];
        if ($response) $data['RESPONSE'] = json_encode($response, JSON_UNESCAPED_UNICODE);
        return RequestResponseTable::add($data);
    }

    private function getRequestResponse($filter = [], $sort = [], $limit = 0)
    {
        $sql = 'SELECT * FROM ' . settings()->get('config')['table']['request-response'];
        if (is_array($filter)) foreach ($filter as $k => $v) if (empty($v)) unset($filter[$k]);
        if ($filter) {
            $sql .= ' WHERE';
            if (!$filter['DATETIME_FROM']) {
                $filter['DATETIME_FROM'] = '2015-12-31';
            }
            if (!$filter['DATETIME_TO']) {
                $filter['DATETIME_TO'] = '2100-12-31';
            }
            $sql .= ' (DATETIME BETWEEN "' . date_format(date_create($filter['DATETIME_FROM']), 'Y-m-d 00:00:00') . '" AND "' . date_format(date_create($filter['DATETIME_TO']), 'Y-m-d 23:59:59') . '")';
            unset($filter['DATETIME_FROM'], $filter['DATETIME_TO']);
            foreach ($filter as $field => $value) {
                $sql .= ' AND ' . $field . '="' . $value . '"';
            }
        }
        if ($sort && $sort['field']) $sql .= ' ORDER BY ' . $sort['field'] . ' ' . $sort['order'];
        if ($limit > 1) $sql .= ' LIMIT ' . $limit;
        return db()->query($sql);
    }

    private function deleteRequestResponse($ids)
    {
        if ($ids === '*') {
            db()->truncateTable(RequestResponseTable::getTableName());
        } else if (is_array($ids)) {
            db()->queryExecute('DELETE FROM ' . RequestResponseTable::getTableName() . ' WHERE ID IN(' . implode(',', $ids) . ')');
        }
    }

    // Request-Limit

    private function addRequestLimit($data = [], string $clientId = '')
    {
        if ($clientId === '') {
            $clientId = $data['CLIENT_ID'];
        }
        $data = [
            'CLIENT_ID' => $clientId,
            'DATETIME' => new \Bitrix\Main\Type\DateTime(),
        ];
        db()->add(settings()->get('config')['table']['request-limit'], $data);
    }

    private function getRequestLimit($filter = [], $sort = [])
    {
        $sql = 'SELECT * FROM ' . settings()->get('config')['table']['request-limit'];
        if (is_array($filter)) foreach ($filter as $k => $v) if (empty($v)) unset($filter[$k]);
        if ($filter) {
            $sql .= ' WHERE';
            if (!$filter['DATETIME_FROM']) {
                $filter['DATETIME_FROM'] = '2000-12-31';
            }
            if (!$filter['DATETIME_TO']) {
                $filter['DATETIME_TO'] = '2100-12-31';
            }
            $sql .= ' (DATETIME BETWEEN "' . date_format(date_create($filter['DATETIME_FROM']), 'Y-m-d 00:00:00') . '" AND "' . date_format(date_create($filter['DATETIME_TO']), 'Y-m-d 23:59:59') . '")';
            unset($filter['DATETIME_FROM'], $filter['DATETIME_TO']);
            foreach ($filter as $field => $value) {
                $sql .= ' AND ' . $field . '="' . $value . '"';
            }
        }
        if ($sort && $sort['field']) $sql .= ' ORDER BY ' . $sort['field'] . ' ' . $sort['order'];
        return db()->query($sql);
    }

    private function deleteRequestLimit($ids)
    {
        if ($ids === '*') {
            db()->truncateTable(settings()->get('config')['table']['request-limit']);
        } else if (is_array($ids)) {
            db()->queryExecute('DELETE FROM ' . settings()->get('config')['table']['request-limit'] . ' WHERE ID IN(' . implode(',', $ids) . ')');
        }
    }

    public function getRequestLimitNumber($clientId, $dateForm, $dateTo)
    {
        return db()->query('SELECT COUNT(CLIENT_ID) as COUNT FROM ' . settings()->get('config')['table']['request-limit'] . ' WHERE CLIENT_ID="' . $clientId . '" AND (DATETIME BETWEEN "' . $dateForm . '" AND "' . $dateTo . '")')->fetchRaw()['COUNT'];
    }

    public static function getInstance()
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
        response()->internalServerError('Method \'' . $name . '\' is not defined');
    }

    private function __clone()
    {
    }
}
