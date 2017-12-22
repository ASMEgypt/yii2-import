<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/22/17
 * Time: 12:57 PM
 */

namespace execut\import\components;


use execut\import\ModelInterface;
use execut\import\Query;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class ModelsExtractor extends Component
{
    public $id = '';

    /**
     * @var ActiveQuery $query
     */
    public $query;
    public $isImport = true;
    public $attributes = [];
    /**
     * @var Importer
     */
    public $importer = null;
    protected $modelsByUniqueKey = [];
    public function getModels($isOnlyForSave = true) {
        $whereValues = [];
        $relationsModels = [];
        foreach ($this->attributes as $attribute => $attributeParams) {
            if (!empty($attributeParams['isFind'])) {
                if ($extractor = $this->importer->getExtractor($attribute)) {
                    $models = $extractor->getModels(false);
                    foreach ($models as $rowNbr => $model) {
                        if (empty($relationsModels[$rowNbr])) {
                            $relationsModels[$rowNbr] = [];
                        }

                        $relationsModels[$rowNbr][$attribute] = $model;
                        if (!$model->isNewRecord && !empty($model->dirtyAttributes) && empty($attributeParams['isNoUpdate'])) {
                            if (!$model->save()) {
                                var_dump($model->errors);
                            }
                        } else if ($model->isNewRecord && empty($attributeParams['isNoCreate'])) {
                            unset($whereValues[$rowNbr]);
                            if (!$model->save()) {
                                var_dump($model->errors);
                            }
                            // Поиск не нужен, модель новая
                            continue;
                        }

                        if (empty($whereValues[$rowNbr])) {
                            $whereValues[$rowNbr] = [];
                        }

                        $whereValues[$rowNbr][$attribute] = $model->id;
                    }
                } else {
                    foreach ($this->importer->rows as $rowNbr => $row) {
                        if (empty($row[$attributeParams['column'] - 1])) {
                            continue;
                        }

                        if (empty($whereValues[$rowNbr])) {
                            $whereValues[$rowNbr] = [];
                        }

                        $whereValues[$rowNbr][$attribute] = $row[$attributeParams['column'] - 1];
                    }
                }
            }
        }

        $result = [];
        foreach ($whereValues as $whereValue) {
            $uniqueKey = serialize($whereValue);
            if (!isset($this->modelsByUniqueKey[$uniqueKey])) {
                $result[$uniqueKey] = $whereValue;
            }
        }

        $whereValues = $result;

        if (count($whereValues)) {
            $attributesNames = $this->getAttributesNamesForFind();

            $query = clone $this->query;
            $query->indexBy(function ($row) use ($attributesNames, $whereValues) {
                $searchedAttributes = [];
                foreach ($attributesNames as $attributesName) {
                    $searchedAttributes[$attributesName] = $row[$attributesName];
                }
                return serialize($searchedAttributes);
            });

            if ($query instanceof Query) {
                $query->byImportAttributes([
                    'IN',
                    $attributesNames,
                    $whereValues,
                ]);
            } else {
                $query->andWhere([
                    'IN',
                    $attributesNames,
                    $whereValues,
                ]);
            }

            $modelsByUniqueKeys = [];
            $models = $query->all();
            foreach ($models as $uniqueKey => $model) {
                if ($model instanceof ModelInterface) {
                    $uniqueKeys = $model->getImportUniqueKeys($attributesNames);
                } else {
                    $uniqueKeys = [$uniqueKey];
                }

                foreach ($uniqueKeys as $uniqueKey) {
                    $modelsByUniqueKeys[$uniqueKey] = $model;
                }
            }

            $this->modelsByUniqueKey = ArrayHelper::merge($this->modelsByUniqueKey, $modelsByUniqueKeys);
        }

        $models = [];
        foreach ($this->importer->rows as $rowNbr => $row) {
            $attributes = [];
            foreach ($this->attributes as $attribute => $attributeParams) {
                if ($this->importer->hasExtractor($attribute)) {
                    if (empty($relationsModels[$rowNbr])) {
                        $attributes[$attribute] = null;
                    } else {
                        $attributes[$attribute] = $relationsModels[$rowNbr][$attribute]->id;
                    }
                } else {
                    if (empty($row[$attributeParams['column'] - 1])) {
                        continue 2;
                    } else {
                        $attributes[$attribute] = $row[$attributeParams['column'] - 1];
                    }
                }
            }

            $attributesNames = $this->getAttributesNamesForFind();
            $searchedAttributes = [];
            foreach ($attributesNames as $attributesName) {
                $searchedAttributes[$attributesName] = $attributes[$attributesName];
            }

            $uniqueKey = serialize($searchedAttributes);

            if (isset($this->modelsByUniqueKey[$uniqueKey])) {
                $model = $this->modelsByUniqueKey[$uniqueKey];
            } else {
                $model = new $this->query->modelClass;
                $this->modelsByUniqueKey[$uniqueKey] = $model;
            }

            if (!$isOnlyForSave || $model->isNewRecord || array_diff($model->getAttributes(array_keys($attributes)), $attributes)) {
                $model->attributes = $attributes;

                $models[$rowNbr] = $model;
            }
        }

        return $models;
    }

    /**
     * @return array
     */
    public function getAttributesNamesForFind(): array
    {
        $attributesNames = [];
        foreach ($this->attributes as $attribute => $attributeParams) {
            if (!empty($attributeParams['isFind'])) {
                $attributesNames[] = $attribute;
            }
        }
        return $attributesNames;
    }
}