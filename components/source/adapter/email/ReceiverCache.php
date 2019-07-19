<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 2:26 PM
 */

namespace execut\import\components\source\adapter\email;

class ReceiverCache
{
    public function set($mails) {
        return \yii::$app->cache->set(__CLASS__, $mails, 60);
    }
    public function get() {
        return \yii::$app->cache->get(__CLASS__);
    }
}