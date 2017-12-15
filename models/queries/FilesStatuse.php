<?php
/**
 * User: execut
 * Date: 18.07.16
 * Time: 10:35
 */

namespace execut\import\models\queries;


use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class FilesStatuse extends ActiveQuery
{
    public function isNew() {
        return $this->byKey(\execut\import\models\FilesStatuse::NEW);
    }

    public function isReload() {
        return $this->byKey(\execut\import\models\FilesStatuse::RELOAD);
    }

    public function isDelete() {
        return $this->byKey(\execut\import\models\FilesStatuse::DELETE);
    }

    public function isLoaded() {
        return $this->byKey(\execut\import\models\FilesStatuse::LOADED);
    }

    public function isLoading() {
        return $this->byKey(\execut\import\models\FilesStatuse::LOADING);
    }

    public function isDeleting() {
        return $this->byKey(\execut\import\models\FilesStatuse::DELETING);
    }

    public function isNotLoading() {
        return $this->byNotKey(\execut\import\models\FilesStatuse::LOADING);
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

        return $this->andWhere([
            'IN',
            'key',
            $key,
        ]);
    }

    public function forSelect() {
        return ArrayHelper::map($this->select(['id', 'name'])->asArray()->all(), 'id', 'name');
    }
}