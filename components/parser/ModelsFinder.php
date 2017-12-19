<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 10/4/16
 * Time: 4:14 PM
 */

namespace execut\import\components\parser;


use execut\import\components\parser\exception\MoreThanOne;
use execut\import\components\parser\exception\NotFoundRecord;
use yii\base\Component;
use yii\helpers\Json;

class ModelsFinder extends Component
{
    public $isCreateAlways = false;
    public $isUpdateAlways = false;
    public $isCreateNotExisted = false;
    public $attributes = null;
    static $cache = [];
    public $query = null;
    public $prepareQuery = null;
    public $asArray = false;
    public $stack = null;

    public function find() {
        $attributesValues = $this->getAttributesValues();
        $cacheKey = $this->query->modelClass . ' ' . Json::encode($attributesValues);
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        if (count(self::$cache) > 10000) {
            echo 'clean(' . count(self::$cache) . ')';
            self::$cache = array_splice(self::$cache, -3000);
        }

        $q = $this->query;
        $q = clone $q;
        $modelClass = $q->modelClass;
        if ($this->isCreateAlways) {
            $result = new $modelClass;
        } else {
            $attributeForSearch = $this->getAttributesForSearch();
            $q->byAttributesScopes($attributeForSearch);

            $qForCount = clone $q;
            $qForCount->orderBy = null;
            $count = $qForCount->count();
            if ($count > 1) {
                $exception = new MoreThanOne();
                $exception->modelClass = $modelClass;
                $exception->attributes = $attributesValues;

                throw $exception;
            }

            if ($count) {
                if ($this->prepareQuery !== null) {
                    $callback = $this->prepareQuery;
                    $q = $callback($q);
                }

                $result = $q->one();
                if ($this->isUpdateAlways) {
                    $result->attributes = $attributesValues;
                }

                if ($this->asArray) {
                    $result = [$result];
                }

                return self::$cache[$cacheKey] = $result;
            } else if ($this->isCreateNotExisted) {
                $result = new $modelClass;
            } else {
                $exception = new NotFoundRecord();
                $exception->modelClass = $modelClass;
                $exception->attributes = $attributesValues;

                throw $exception;
            }
        }

        $result->attributes = $attributesValues;
        if ($this->asArray) {
            $result = [$result];
        }

        return self::$cache[$cacheKey] = $result;
    }

    public function findModel() {
        $attributesValues = $this->getAttributesValues();
        $cacheKey = $this->query->modelClass . ' ' . Json::encode($attributesValues);
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        if (count(self::$cache) > 10000) {
            echo 'clean(' . count(self::$cache) . ')';
            self::$cache = array_splice(self::$cache, -3000);
        }

        $q = $this->query;
        $q = clone $q;
        $modelClass = $q->modelClass;
        $result = new Result();
        if ($this->isCreateAlways) {
            $model = new $modelClass;
            $result->addModel($model);
        } else {
            $attributeForSearch = $this->getAttributesForSearch();
            $q->byAttributesScopes($attributeForSearch);

            $qForCount = clone $q;
            $qForCount->orderBy = null;
            $count = $qForCount->count();
            if ($count > 1) {
                $exception = new MoreThanOne();
                $exception->modelClass = $modelClass;
                $exception->attributes = $attributesValues;

                throw $exception;
            }

            if ($count) {
                if ($this->prepareQuery !== null) {
                    $callback = $this->prepareQuery;
                    $q = $callback($q, $result, $this->stack);
                }

                $model = $q->one();
                if ($this->isUpdateAlways) {
                    $model->attributes = $attributesValues;
                }

                $result->addModel($model);

                return self::$cache[$cacheKey] = $result;
            } else if ($this->isCreateNotExisted) {
                $model = new $modelClass;
                $result->addModel($model);
            } else {
                $exception = new NotFoundRecord();
                $exception->modelClass = $modelClass;
                $exception->attributes = $attributesValues;

                throw $exception;
            }
        }

        $model->attributes = $attributesValues;

        return self::$cache[$cacheKey] = $result;
    }

    /**
     * @return array
     */
    protected function getAttributesForSearch(): array
    {
        $attributes = $this->attributes;
        $attributeForSearch = [];
        foreach ($attributes as $attribute) {
            if ($attribute->isForSearchQuery) {
                $attributeForSearch[$attribute->key] = $attribute->value;
            }
        }
        return $attributeForSearch;
    }

    /**
     * @return array
     */
    protected function getAttributesValues(): array
    {
        $attributesValues = [];
        $attributes = $this->attributes;
        foreach ($attributes as $attribute) {
            $attributesValues[$attribute->key] = $attribute->value;
        }
        return $attributesValues;
    }
}