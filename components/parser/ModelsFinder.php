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
use execut\import\Query;
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
    public $advancedSearch = null;
    public $asArray = false;
    /**
     * @var Stack
     */
    public $stack = null;

    protected function findModelFromCache() {
        if ($model = $this->getCache()) {
            return $model;
        } else {
            $model = new $this->query->modelClass;
            $this->setCache($model);
        }

        return $model;
    }

    public function getCache() {
        $key = $this->getCacheKey(false);
        $subKey = $this->getCacheKey();
        if (isset(self::$cache[$key]) && isset(self::$cache[$key][$subKey])) {
            return self::$cache[$key][$subKey];
        }
    }

    public function setCache($model, $subKey = null) {
        $key = $this->getCacheKey(false);
        if ($subKey === null) {
            $subKey = $this->getCacheKey();
        }

        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = [];
        }

        self::$cache[$key][$subKey] = $model;
    }

    public function findModelNew() {
        if (!isset(self::$cache[$this->getCacheKey(false)])) {
            $this->initCache();
        }

        $result = new Result();
        $model = $this->findModelFromCache();
        $model->attributes = $this->getAttributesValues();
        $result->addModel($model);

        return $result;
    }

    public function findModel() {
        $cacheKey = $this->getCacheKey();
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
        $attributesValues = $this->getAttributesValues();
        if ($this->isCreateAlways) {
            $model = new $modelClass;
            $result->addModel($model);
        } else {
            $attributeForSearch = $this->getAttributesForSearch();
            if ($q instanceof Query) {
                $q->byImportAttributes($attributeForSearch);
            } else {
                $q->andWhere($attributeForSearch);
            }

            if ($this->prepareQuery !== null) {
                $callback = $this->prepareQuery;
                $q = $callback($q, $result, $this->stack);
            }

            if ($this->advancedSearch !== null) {
                $callback = $this->advancedSearch;
                $models = $callback($q, $result, $this->stack);
            } else {
                $models = $q->all();
            }

            if (count($models) > 1) {
                $exception = new MoreThanOne();
                $exception->modelClass = $modelClass;
                $exception->attributes = $attributesValues;

                throw $exception;
            }

            if (count($models) == 1) {
                $model = current($models);
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
        $attributes = $this->getAttributes();
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
        $attributes = $this->getAttributes();
        foreach ($attributes as $attribute) {
            $attributesValues[$attribute->key] = $attribute->value;
        }
        return $attributesValues;
    }

    /**
     * @param $row
     * @return array
     */
    protected function getAttributes(): array
    {
        return $this->attributes;
    }

    protected function initCache(): void
    {
        $attributesNames = $this->getSearchAttributesNames();

        $stack = $this->stack;
        $rows = $stack->rows;
        $findPairs = [];
        foreach ($rows as $rowNbr => $row) {
            $attributes = $this->getAttributesFromRow($rowNbr);
            $attributesValues = [];
            foreach ($attributes as $attribute) {
                if (!$attribute->isValid()) {
                    continue 2;
                }

                if (!$attribute->isForSearchQuery) {
                    continue;
                }

                $attributesValues[$attribute->key] = $attribute->value;
            }
            $findPairs[] = $attributesValues;
        }

        $q = clone $this->query;
        $q->andWhere([
            'IN',
            $attributesNames,
            $findPairs
        ]);

        $models = $q->all();
        foreach ($models as $model) {
            $attributeForSearch = [];
            foreach ($attributesNames as $attributesName) {
                $attributeForSearch[$attributesName] = $model->$attributesName;
            }

            $cacheKey = $q->modelClass . ' ' . Json::encode($attributeForSearch);
            $this->setCache($model, $cacheKey);
        }
    }

    /**
     * @return string
     */
    protected function getCacheKey($isWithAttributes = true): string
    {
        $attributeForSearch = $this->getAttributesForSearch();
        $cacheKey = $this->query->modelClass;
        if ($isWithAttributes) {
            $cacheKey .= ' ' . Json::encode($attributeForSearch);
        }

        return $cacheKey;
    }

    /**
     * @return array
     */
    protected function getSearchAttributesNames(): array
    {
        $attributesNames = [];
        $attributes = $this->getAttributesFromRow();
        foreach ($attributes as $attribute) {
            if (!$attribute->isForSearchQuery) {
                continue;
            }

            $attributesNames[] = $attribute->key;
        }
        return $attributesNames;
    }
}