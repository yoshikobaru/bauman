<?php

namespace Artamonov\Rest\Foundation;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Data\ManagedCache;
use Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\TextField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\SystemException;

/**
 * Class RequestResponseTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DATETIME datetime mandatory
 * <li> IP string(20) optional
 * <li> METHOD string(10) optional
 * <li> CLIENT_ID string(60) optional
 * <li> REQUEST text optional
 * <li> RESPONSE text optional
 * </ul>
 *
 * @package Artamonov\Rest\Foundation
 **/
class RequestResponseTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return settings()->get('config')['table']['request-response'];
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws SystemException
     */
    public static function getMap()
    {
        return [
            'ID' => (new IntegerField('ID',
                []
            ))->configureTitle(loc('ArtamonovRestId'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),
            'DATETIME' => (new DatetimeField('DATETIME',
                []
            ))->configureTitle(loc('ArtamonovRestDateTime'))
                ->configureRequired(true),
            'IP' => (new StringField('IP',
                [
                    'validation' => [__CLASS__, 'validateIp'],
                ]
            ))->configureTitle(loc('ArtamonovRestIp')),
            'METHOD' => (new StringField('METHOD',
                [
                    'validation' => [__CLASS__, 'validateMethod'],
                ]
            ))->configureTitle(loc('ArtamonovRestMethod')),
            'CLIENT_ID' => (new StringField('CLIENT_ID',
                [
                    'validation' => [__CLASS__, 'validateClientId'],
                ]
            ))->configureTitle(loc('ArtamonovRestClientId')),
            'REQUEST' => (new TextField('REQUEST',
                []
            ))->configureTitle(loc('ArtamonovRestTabRequestTitle')),
            'RESPONSE' => (new TextField('RESPONSE',
                []
            ))->configureTitle(loc('ArtamonovRestTabResponseTitle')),
        ];
    }

    /**
     * Returns validators for IP field.
     *
     * @return LengthValidator[]
     * @throws ArgumentTypeException
     */
    public static function validateIp(): array
    {
        return [
            new LengthValidator(null, 20),
        ];
    }

    /**
     * Returns validators for METHOD field.
     *
     * @return LengthValidator[]
     * @throws ArgumentTypeException
     */
    public static function validateMethod(): array
    {
        return [
            new LengthValidator(null, 10),
        ];
    }

    /**
     * Returns validators for CLIENT_ID field.
     *
     * @return LengthValidator[]
     * @throws ArgumentTypeException
     */
    public static function validateClientId(): array
    {
        return [
            new LengthValidator(null, 60),
        ];
    }

    /**
     * @param string $cacheId
     *
     * @return void
     */
    public static function clearCache(string $cacheId = '')
    {
        $cache = Application::getInstance()->getCache();
        $managedCache = Application::getInstance()->getManagedCache();
        if ($cacheId <> '') {
            $cache->clean($cacheId, self::getTableName());
            $managedCache->clean($cacheId, 'orm_' . self::getTableName());
        } else {
            $cache->cleanDir(self::getTableName());
            $managedCache->cleanDir('orm_' . self::getTableName());
        }
    }
}