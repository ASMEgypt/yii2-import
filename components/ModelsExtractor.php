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
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class ModelsExtractor extends Component
{
    public $id = '';

    /**
     * @var ActiveQuery $query
     */
    public $query;
    public $scopes = null;
    public $isImport = false;
    public $attributes = [];
    /**
     * @var Importer
     */
    public $importer = null;
    protected $modelsByUniqueKey = [];
    public $isNoCreate = false;
    public $isNoUpdate = false;
    public $isDelete = false;
    public $deletedIds = [];
    public $uniqueKeys = null;

    public function reset() {
        $this->modelsByUniqueKey = [];
    }

    public function deleteOldRecords() {
        if ($this->isDelete) {
            $ids = $this->deletedIds;
            if (!empty($ids)) {
                if (count($ids) > 500000) {
                    throw new Exception('Many than 500 000 records to delete. Dangerous situation.');
                }

                $modelClass = $this->query->modelClass;
                while ($idsPart = array_splice($ids, 0, 65534)) {
                    if (count($idsPart) > 0) {
                        $modelClass::deleteAll([
                            'id' => $idsPart
                        ]);
                    }
                }
            }
        }
    }

    public function getModels($isSave = true, $isMarkBad = true) {
        /**
         * @var ActiveRecord $model
         */
        $this->startOperation('extract');
        $whereValues = [];
        $relationsModels = [];
        $this->startOperation('construct where');
        foreach ($this->attributes as $attribute => $attributeParams) {
            if (empty($attributeParams['extractor'])) {
                $extractorId = $attribute;
            } else {
                $extractorId = $attributeParams['extractor'];
            }

            if (empty($attributeParams['value']) && ($extractor = $this->importer->getExtractor($extractorId))) {
                $models = $extractor->getModels(false, $isMarkBad && !empty($attributeParams['isFind']));
                foreach ($this->importer->rows as $rowNbr => $row) {
                    if ($this->isBadRow($rowNbr)) {
                        continue;
                    }

                    if (empty($models[$rowNbr])) {
                        if ($isMarkBad && !empty($attributeParams['isFind'])) {
                            $this->importer->setIsBadRow($rowNbr);
                        }

                        continue;
                    }

                    $model = $models[$rowNbr];

                    if (empty($relationsModels[$rowNbr])) {
                        $relationsModels[$rowNbr] = [];
                    }

                    if (empty($extractor->isNoCreate) || !$model->isNewRecord) {
                        $relationsModels[$rowNbr][$attribute] = $model;
                    }

                    if (!empty($attributeParams['isFind'])) {
                        if (!$model->isNewRecord && !empty($model->dirtyAttributes)) {
                            if (empty($extractor->isNoUpdate)) {
                                if (!$this->saveModel($model, $rowNbr)) {
                                    unset($whereValues[$rowNbr]);
                                    continue;
                                }
                            }
                        } else if ($model->isNewRecord) {
                            if (empty($extractor->isNoCreate)) {
                                // Поиск не нужен, модель новая
                                unset($whereValues[$rowNbr]);
                                $this->saveModel($model, $rowNbr);
                                continue;
                            }
                        }

                        if ($model->isNewRecord && !empty($extractor->isNoCreate)) {
                            $this->logError('Related record is not founded with attributes ' . serialize(array_filter($model->attributes)), $rowNbr, $model, null, $isMarkBad);
                            unset($whereValues[$rowNbr]);
                            continue;
                        }

                        if (empty($whereValues[$rowNbr])) {
                            $whereValues[$rowNbr] = [];
                        }

                        $whereValues[$rowNbr][$attribute] = (int)$model->id;
                    }
                }
            } else {
                if (!empty($attributeParams['isFind'])) {
                    foreach ($this->importer->rows as $rowNbr => $row) {
                        if ($this->isBadRow($rowNbr)) {
                            continue;
                        }

                        if (!empty($attributeParams['value'])) {
                            $whereValues[$rowNbr][$attribute] = $attributeParams['value'];
                            continue;
                        }

                        if (empty($attributeParams['column'])) {
                            return [];
                            throw new Exception('Column key is required for attribute params. Extractor: ' . $extractorId . '. Attribute params: ' . var_export($attributeParams, true));
                        }

                        if (empty($row[$attributeParams['column'] - 1])) {
                            $this->logError($attribute . ' is required for record find', $rowNbr, null, $attributeParams['column']);
                            unset($whereValues[$rowNbr]);
                            continue;
                        }

                        if (empty($whereValues[$rowNbr])) {
                            $whereValues[$rowNbr] = [];
                        }

                        $modelClass = $this->query->modelClass;
                        $value = $row[$attributeParams['column'] - 1];
                        if (method_exists($modelClass, 'filtrateAttribute')) {
                            $value = $modelClass::filtrateAttribute($attribute, $value);
                        }

                        $whereValues[$rowNbr][$attribute] = $value;
                    }
                }
            }
        }

        $result = [];
        foreach ($whereValues as $rowNbr => $whereValue) {
            if ($this->importer->isBadRow($rowNbr)) {
                continue;
            }

            $uniqueKey = serialize($whereValue);
            if (!isset($this->modelsByUniqueKey[$uniqueKey])) {
                $result[$uniqueKey] = $whereValue;
            }
        }

        $whereValues = $result;

        $this->endOperation('construct where');
        if (count($whereValues)) {
            $attributesNames = $this->getAttributesNamesForFind();
            $query = clone $this->query;
            $query->indexBy(function ($row) use ($attributesNames) {
                $searchedAttributes = [];
                foreach ($attributesNames as $attributesName) {
                    $modelClass = $this->query->modelClass;
                    $value = $row[$attributesName];
                    if (method_exists($modelClass, 'filtrateAttribute')) {
                        $value = $modelClass::filtrateAttribute($attributesName, $value);
                    }

                    $searchedAttributes[$attributesName] = $value;
                }
                return serialize($searchedAttributes);
            });

            if ($this->scopes !== null) {
                foreach ($this->scopes as $scope) {
                    $scope($query, [
                        'IN',
                        $attributesNames,
                        $whereValues,
                    ]);
                }
            } else {
                $query->andWhere([
                    'IN',
                    $attributesNames,
                    $whereValues,
                ]);
            }

            $this->startOperation('find');
            $models = $query->all();
            $this->endOperation('find');
            $this->startOperation('keys collect');
            foreach ($models as $uniqueKey => $model) {
                if ($this->uniqueKeys !== null) {
                    $callback = $this->uniqueKeys;
                    $uniqueKeys = $callback($model, $attributesNames, $whereValues);
                } else {
                    $uniqueKeys = [$uniqueKey];
                }

                foreach ($uniqueKeys as $uniqueKey) {
                    $this->modelsByUniqueKey[$uniqueKey] = $model;
                }
            }

            $this->endOperation('keys collect');
        }

        $models = [];
        $this->startOperation('models collect');
        foreach ($this->importer->rows as $rowNbr => $row) {
            if ($this->isBadRow($rowNbr)) {
                continue;
            }

            $attributes = [];
            foreach ($this->attributes as $attribute => $attributeParams) {
                if (empty($attributeParams['extractor'])) {
                    $extractorId = $attribute;
                } else {
                    $extractorId = $attributeParams['extractor'];
                }

                if (empty($attributeParams['value']) && $this->importer->hasExtractor($extractorId)) {
                    if (empty($relationsModels[$rowNbr]) || empty($relationsModels[$rowNbr][$attribute])) {
//                        $attributes[$attribute] = null;
                    } else {
                        $p = $relationsModels[$rowNbr][$attribute]->id;
                        $attributes[$attribute] = (int)$p;
                    }
                } else {
                    if (!empty($attributeParams['value'])) {
                        $attributes[$attribute] = $attributeParams['value'];
                    } else {
                        if (empty($attributeParams['column'])) {
                            throw new Exception('Not setted column for attribute ' . $attribute . ' for extractor ' . $this->id);
                        }

                        if (empty($row[$attributeParams['column'] - 1])) {
                            continue 2;
                        } else {
                            $value = $row[$attributeParams['column'] - 1];
                            if (!empty($attributeParams['numberDelimiter'])) {
                                if ($attributeParams['numberDelimiter'] == ',') {
                                    $value = str_replace('.', '', $value);
                                    $value = str_replace(',', '.', $value);
                                } else if ($attributeParams['numberDelimiter'] == '.') {
                                    $value = str_replace(',', '', $value);
                                }
                            }

                            $attributes[$attribute] = $value;
                        }
                    }
                }
            }

            $attributesNames = $this->getAttributesNamesForFind();
            $searchedAttributes = [];
            foreach ($attributesNames as $attributesName) {
                $modelClass = $this->query->modelClass;
                if (empty($attributes[$attributesName])) {
                    $value = null;
                } else {
                    $value = $attributes[$attributesName];
                }

                if (method_exists($modelClass, 'filtrateAttribute')) {
                    $value = $modelClass::filtrateAttribute($attributesName, $value);
                    $attributes[$attributesName] = $value;
                }

                $searchedAttributes[$attributesName] = $value;
            }

            $uniqueKey = serialize($searchedAttributes);
            if (isset($this->modelsByUniqueKey[$uniqueKey])) {
                $model = $this->modelsByUniqueKey[$uniqueKey];
            } else {
                $model = new $this->query->modelClass;
                $this->modelsByUniqueKey[$uniqueKey] = $model;
            }

            if ($this->isDelete && !$model->isNewRecord) {
                unset($this->deletedIds[$model->id]);
            }

            $modelAttributes = $model->getAttributes(array_keys($attributes));
            if (!$isSave || $model->isNewRecord || array_diff($attributes, $modelAttributes)) {
                $model->attributes = $attributes;

                $models[$rowNbr] = $model;
            }
        }

        $this->endOperation('models collect');

        $this->endOperation('extract');
        if ($isSave) {
            foreach ($models as $rowNbr => $model) {
                if (!$this->isBadRow($rowNbr)) {
                    $this->saveModel($model, $rowNbr);
                }
            }
        }

        return $models;
    }

    protected $times = [];
    protected function startOperation($name) {
        echo 'start ' . $name . ' ' . $this->id . "\n";
        $this->times[$name] = microtime(true);
    }

    protected function endOperation($name) {
        $time = microtime(true) - $this->times[$name];
        echo 'end ' . $name . ' ' . $this->id . ' after ' . $time . ' seconds' . "\n";
    }

    protected function triggerOperation($name) {
        echo $name . ' ' . $this->id . "\n";
    }

    protected function isBadRow($rowNbr) {
        return $this->importer->isBadRow($rowNbr);
    }

    protected function logError($message, $rowNbr, $model, $columnNbr = null, $isMarkBad = true) {
        if ($isMarkBad) {
            $this->importer->setIsBadRow($rowNbr);
        }

        $this->importer->logError($message, $rowNbr, $model, $columnNbr);
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

    /**
     * @param $model
     * @return mixed
     */
    protected function saveModel($model, $rowNbr)
    {
        $currentRowNbr = $this->importer->getCurrentStackRowNbr() + $rowNbr;
        $modelString = $model->tableName() . ' #' . $model->id . ' ' . serialize(array_filter($model->attributes));
        echo 'Row #' . $currentRowNbr . ': ';
        if (!$model->validate()) {
            $message = 'Error validate ' . $model->tableName() . ' #' . $model->id . ' ' . serialize(array_filter($model->attributes)) . ' ' . serialize($model->errors);
            $this->logError($message, $rowNbr, $model);
            return false;
        }

        $isNewRecord = ($model->isNewRecord && !$this->isNoCreate);
        $isUpdatedRecord = (!$model->isNewRecord && !$this->isNoUpdate && !empty($model->dirtyAttributes));

        if ($isNewRecord || $isUpdatedRecord) {
            if ($isNewRecord) {
                $reason = 'created';
            } else {
                $reason = 'updated';
            }

            echo 'Saving ' . $modelString . ' because they is ' . $reason . "\n";
            if ($isUpdatedRecord) {
                echo 'Changed attributes ' . serialize(array_keys($model->dirtyAttributes)) . "\n";
                $oldValues = [];
                foreach ($model->dirtyAttributes as $attribute => $value) {
                    $oldValues[$attribute] = $model->oldAttributes[$attribute];
                }

                echo 'Old values ' . serialize($oldValues) . "\n";
                echo 'New values ' . serialize($model->dirtyAttributes) . "\n";
            }

            if (!$model->save()) {
                $this->logError('Error while saving ' . $modelString, $rowNbr, $model);
            }
        } else {
            echo $modelString . ' Is skipped because is not changed' . "\n";
        }

        return true;
    }
}