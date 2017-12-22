<?php
/**
 * User: execut
 * Date: 19.07.16
 * Time: 14:01
 */

namespace execut\import\components;


use execut\import\components\parser\Attribute;
use execut\import\components\parser\exception\ColumnIsEmpty;
use execut\import\components\parser\exception\MoreThanOne;
use execut\import\components\parser\exception\NotFoundRecord;
use execut\import\components\parser\exception\Validate;
use execut\import\components\parser\ModelsFinder;
use execut\yii\db\ActiveRecord;
use yii\base\Component;
use yii\base\Exception;

class Parser extends Component
{
    protected $attributes = [];
    public $isValidate = true;
    /**
     * @var ModelsFinder
     */
    protected $modelsFinder = [];
    protected $_stack = null;

    public function setModelsFinder($finder) {
        $this->modelsFinder = $finder;
    }

    public function setStack($stack) {
        $this->_stack = $stack;

        return $this;
    }

    public function getStack() {
        return $this->_stack;
    }

    /**
     * @return ModelsFinder
     */
    public function getModelsFinder() {
        if (is_array($this->modelsFinder)) {
            if (empty($this->modelsFinder['class'])) {
                $this->modelsFinder['class'] = ModelsFinder::className();
            }

            $this->modelsFinder = \yii::createObject($this->modelsFinder);
        }

        $this->modelsFinder->stack = $this->getStack();
        $this->modelsFinder->parser = $this;

        return $this->modelsFinder;
    }

    public function setAttributes($attributes) {
        $result = [];
        foreach ($attributes as $key => $attributeParams) {
            if (is_object($attributeParams)) {
                $attribute = $attributeParams;
            } else {
                if (!is_array($attributeParams)) {
                    $attributeParams = [
                        'value' => $attributeParams,
                    ];
                }

                if (empty($attributeParams['key'])) {
                    $attributeParams['key'] = $key;
                }

                $attribute = new Attribute($attributeParams);
            }

            $result[$key] = $attribute;
        }

        $this->attributes = $result;
    }

    public function parse() {
        $settedAttributes = [];
        $attributes = $this->getAttributesFromRow();
        foreach ($attributes as $key => $attribute) {
            $settedAttributes[$key] = $attribute->value;
        }

        $modelsFinder = $this->getModelsFinder();
        $this->prevalidateAttributes();
        $result = $modelsFinder->findModel();
        foreach ($result->getModels() as $model) {
            if ($this->isValidate) {
                $this->validateAttributes($model, array_keys($settedAttributes));
            }
        }

        return $result;
    }

    public function prevalidateAttributes() {
        $attributes = $this->getAttributesFromRow();
        foreach ($attributes as $attribute)
        if (!$attribute->isValid()) {
            $exception = new ColumnIsEmpty();
            $exception->columnNbr = $attribute->column;
            $exception->attribute = $attribute->key;

            throw $exception;
        }
    }

    /**
     * @param ActiveRecord $model
     * @param $attributes
     */
    protected function validateAttributes($model, $attributesKeys) {
        $attributes = $this->getAttributesFromRow();
        foreach ($attributesKeys as $attributeKey) {
            if (isset($attributes[$attributeKey]) && $attributes[$attributeKey]->column !== null) {
                if (!$model->validate([$attributeKey], false)) {
                    $e = new Validate();
                    $e->errors = $model->errors;
                    $e->columnNbr = $attributes[$attributeKey]->column;
                    $e->attribute = $attributeKey;

                    throw $e;
                }
            }
        }
    }

    /**
     * @param $modelClass
     * @param $result
     * @return mixed
     */
    protected function createModel($modelClass, $result)
    {
        $model = new $modelClass;
        foreach ($result as $key => $value) {
            $result[$key] = (string) $value;
        }
        $model->attributes = $result;

        return $model;
    }

    /**
     * @param $row
     * @return array
     */
    public function getAttributesFromRow($rowNbr = null): array
    {
        $row = $this->getStack()->getRow($rowNbr);
        $attributes = $this->attributes;
        foreach ($attributes as $attribute) {
            $attribute->row = $row;
        }

        return $attributes;
    }
}