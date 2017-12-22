<?php
namespace execut\import\components;
use execut\import\example\models\Article;
use execut\import\example\models\Brand;
use execut\import\example\models\Product;
use execut\import\models\Log;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\log\Logger;

use execut\import\components\parser\exception\Exception;
use execut\import\components\parser\Stack;

class Importer extends Component {
    public $rows = null;
    public $file = null;
    public $settings = null;
    public $checkedFileStatusRows = 1000;

    protected $lastCheckedRow = null;
    public function saveModels($relationParams) {
        /**
         * @var ActiveQuery $query
         */
        $query = $relationParams['query'];
        $attributesNames = [];
        $whereValues = [];
        $relationsModels = [];
        foreach ($relationParams['attributes'] as $attribute => $attributeParams) {
            if (!empty($attributeParams['isFind'])) {
                $attributesNames[] = $attribute;
                if (!empty($attributeParams['attributes'])) {
                    $models = $this->saveModels($attributeParams);
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
                    foreach ($this->rows as $rowNbr => $row) {
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

        $query->andWhere([
            'IN',
            $attributesNames,
            $whereValues,
        ])->indexBy(function ($row) use ($attributesNames, $whereValues) {
            $searchedAttributes = [];
            foreach ($attributesNames as $attributesName) {
                $searchedAttributes[$attributesName] = $row[$attributesName];
            }
            return implode('-', $searchedAttributes);
        });

        $modelsByUniqueKey = $query->all();
        $models = [];
        foreach ($this->rows as $rowNbr => $row) {
            $attributes = [];
            foreach ($relationParams['attributes'] as $attribute => $attributeParams) {
                if (!empty($attributeParams['attributes'])) {
                    if (empty($relationsModels[$rowNbr])) {
                        continue 2;
                    }

                    $attributes[$attribute] = $relationsModels[$rowNbr][$attribute]->id;
                } else {
                    if (empty($row[$attributeParams['column'] - 1])) {
                        continue 2;
                    }

                    $attributes[$attribute] = $row[$attributeParams['column'] - 1];
                }
            }

            $searchedAttributes = [];
            foreach ($attributesNames as $attributesName) {
                $searchedAttributes[$attributesName] = $attributes[$attributesName];
            }

            $uniqueKey = implode('-', $searchedAttributes);

            if (isset($modelsByUniqueKey[$uniqueKey])) {
                $model = $modelsByUniqueKey[$uniqueKey];
            } else {
                $model = new $query->modelClass;
                $modelsByUniqueKey[$uniqueKey] = $model;
            }

            $model->attributes = $attributes;

            $models[$rowNbr] = $model;
        }

        return $models;
    }

    public function run() {
        $extractors = $this->getExtractors();
        foreach ($extractors as $extractor) {
            if ($extractor->isImport) {
                $models = $extractor->getModels();
                foreach ($models as $model) {
                    $model->validate();
                    if (!empty($model->dirtyAttributes)) {
                        if (!$model->save()) {
                            var_dump($model->errors);
                        }
                    }
                }
            }
        }
        return;

        foreach ($attributes as $relation => $relationParams) {
            $models = $this->saveModels($relationParams);
            foreach ($models as $model) {
                $model->validate();
                if (!empty($model->dirtyAttributes)) {
                    if (!$model->save()) {
                        var_dump($model->errors);
                    }
                }
            }
        }

        return;

        $stacks = $this->getStacks();
        foreach ($this->rows as $rowKey => $row) {
            try {
                if ($this->lastCheckedRow >= $this->checkedFileStatusRows) {
                    if ($this->file->isStop()) {
                        $this->file->triggerStop();
                        return;
                    }

                    if ($this->file->isCancelLoading()) {
                        //                        $this->stdout('Cancel file loading ' . $this->file . "\n");
                        return;
                    }

                    $this->lastCheckedRow = 0;
                } else {
                    $this->lastCheckedRow++;
                }

                foreach ($stacks as $stack) {
                    $stack->currentRow = $rowKey;
                    $result = $stack->parse();
                    $this->file->triggerSuccessRow();
                }
            } catch (Exception $e) {
                $attributes = [
                    'level' => Logger::LEVEL_WARNING,
                    'category' => $e->getLogCategory(),
                    'message' => $e->getLogMessage(),
                    'import_file_id' => $this->file->id,
                    'row_nbr' => $rowKey + $this->file->setting->ignored_lines + 1,
                    'column_nbr' => $e->columnNbr
                ];
                $this->logError($attributes);
                $this->file->triggerErrorRow();
            } catch (\Exception $e) {
                $attributes = [
                    'level' => Logger::LEVEL_ERROR,
                    'category' => 'import.error',
                    'message' => $e->getMessage() . "\n" . $e->getTraceAsString(),
                    'import_file_id' => $this->file->id,
                    'row_nbr' => $rowKey + $this->file->setting->ignored_lines + 1,
                ];
                $this->logError($attributes);

                $this->file->triggerException();
                throw $e;
            }
        }
    }

    /**
     * @param $attributes
     */
    protected function logError($attributes)
    {
        $log = new Log($attributes);
        if (!$log->save()) {
            var_dump($log->errors);
            exit;
        }
    }

    /**
     * @return Stack[]
     */
    protected function getStacks()
    {
        /**
         * @var Stack[] $stacks
         */
        $stacks = [];
        foreach ($this->settings as $key => $stacksSetting) {
            $stack = new Stack($stacksSetting);
            $stack->rows = $this->rows;
            $stacks[] = $stack;
        }
        return $stacks;
    }

    protected $extractors = null;

    /**
     * @return array
     */
    public function getExtractor($id)
    {
        if ($this->hasExtractor($id)) {
            return $this->getExtractors()[$id];
        }
    }

    public function hasExtractor($id) {
        $extractors = $this->getExtractors();
        if (!empty($extractors[$id])) {
            return true;
        }
    }

    public function getExtractors()
    {
        if ($this->extractors !== null) {
            return $this->extractors;
        }

        $attributes = [
            'example_product_id' => [
                'query' => Product::find(),
                'isImport' => true,
                'attributes' => [
                    'name' => [
                        'isFind' => true,
                        'column' => 1,
                    ],
                    'price' => [
                        'column' => 2,
                    ],
                    'example_article_id' => [
                        'isFind' => true,
                    ],
                ],
            ],
            'example_article_id' => [
                'query' => Article::find(),
                'isImport' => false,
                'attributes' => [
                    'article' => [
                        'isFind' => true,
                        'column' => 3,
                    ],
                    'source' => [
                        'column' => 4,
                    ],
                    'example_brand_id' => [
                        'isFind' => true,
                    ],
                ],
            ],
            'example_brand_id' => [
                'isImport' => false,
                'query' => Brand::find(),
                'attributes' => [
                    'name' => [
                        'isFind' => true,
                        'column' => 5,
                    ],
                ],
            ],
        ];

        $extractors = [];
        foreach ($attributes as $extractorId => $params) {
            $params['importer'] = $this;
            $extractor = new ModelsExtractor($params);
            $extractors[$extractorId] = $extractor;
        }

        return $this->extractors = $extractors;
    }
}