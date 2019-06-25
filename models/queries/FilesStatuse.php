<?php
/**
 * User: execut
 * Date: 18.07.16
 * Time: 10:35
 */

namespace execut\import\models\queries;


use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use \execut\import\models;

class FilesStatuse extends ActiveQuery
{
    public function isNew() {
        return $this->byKey(models\FilesStatuse::NEW);
    }

    public function isReload() {
        return $this->byKey(models\FilesStatuse::RELOAD);
    }

    public function isDelete() {
        return $this->byKey(models\FilesStatuse::DELETE);
    }

    public function isLoaded() {
        return $this->byKey(models\FilesStatuse::LOADED);
    }

    public function isLoading() {
        return $this->byKey(models\FilesStatuse::LOADING);
    }

    public function isDeleting() {
        return $this->byKey(models\FilesStatuse::DELETING);
    }

    public function isNotLoading() {
        return $this->byNotKey(models\FilesStatuse::LOADING);
    }

    public function byNotKey($key) {
        if (!is_array($key)) {
            $key = [$key];
        }

        return $this->andWhere([
            'NOT IN',
            'key',
            $key,
        ]);
    }

    public function byKey($key) {
        if (!is_array($key)) {
            $key = [$key];
        }

        if (!count($key)) {
            return $this->andWhere('0=1');
        }

        return $this->andWhere([
            'IN',
            'key',
            $key,
        ]);
    }

    public function forSelect() {
        return ArrayHelper::map($this->select(['id', 'name'])->asArray()->all(), 'id', 'name');
    }

    public function isAllowedForKey($key) {
        $keysChains = [
            models\FilesStatuse::NEW => [],
            models\FilesStatuse::RELOAD => [],
            models\FilesStatuse::DELETE => [],
            models\FilesStatuse::LOADED => [models\FilesStatuse::RELOAD, models\FilesStatuse::DELETE],
            models\FilesStatuse::LOADING => [models\FilesStatuse::STOP],
            models\FilesStatuse::DELETING => [],
            models\FilesStatuse::STOP => [],
            models\FilesStatuse::STOPED => [models\FilesStatuse::RELOAD, models\FilesStatuse::DELETE],
            models\FilesStatuse::ERROR => [models\FilesStatuse::RELOAD, models\FilesStatuse::DELETE],
        ];

        $keys = [$key];
        if (!empty($keysChains[$key])) {
            $keys = ArrayHelper::merge($keysChains[$key], $keys);
        }

        return $this->byKey($keys);
    }
}