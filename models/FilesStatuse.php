<?php
/**
 * User: execut
 * Date: 18.07.16
 * Time: 9:54
 */

namespace execut\import\models;


use execut\CacheTrait;

class FilesStatuse extends base\FilesStatuse
{
    use CacheTrait;
    const NEW = 'new';
    const RELOAD = 'reload';
    const DELETE = 'delete';
    const LOADED = 'loaded';
    const LOADING = 'loading';
    const DELETING = 'deleting';
    const ERROR = 'error';
    const STOPED = 'stoped';
    const STOP = 'stop';
    public static function find() {
        return new \execut\import\models\queries\FilesStatuse(__CLASS__);
    }

    public static function getIdByKey($key) {
        return self::_cacheStatic(function () use ($key) {return self::find()->byKey($key)->select('id')->createCommand()->queryScalar();}, $key);
    }
}