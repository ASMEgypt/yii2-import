<?php
/**
 * User: execut
 * Date: 18.07.16
 * Time: 9:54
 */

namespace execut\import\models;
class FilesStatuse extends base\FilesStatuse
{
    const NEW = 'new';
    const RELOAD = 'reload';
    const DELETE = 'delete';
    const LOADED = 'loaded';
    const LOADING = 'loading';
    const DELETING = 'deleting';
    const ERROR = 'error';
    const STOPED = 'stoped';
    const STOP = 'stop';
    protected static $statusesByKeys = [];
    public static function find() {
        return new \execut\import\models\queries\FilesStatuse(__CLASS__);
    }

    public static function getIdByKey($key)
    {
        if (empty(self::$statusesByKeys[$key])) {
            self::$statusesByKeys[$key] = self::find()->byKey($key)->select('id')->createCommand()->queryScalar();
        }

        return self::$statusesByKeys[$key];
    }
}