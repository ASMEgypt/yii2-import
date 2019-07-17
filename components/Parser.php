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
    public $query = null;
    public $row = null;
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
                $this->modelsFinder['class'] = ModelsFinder::class;
            }

            $this->modelsFinder = \yii::createObject($this->modelsFinder);
        }

        $this->modelsFinder->stack = $this->getStack();

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

    public function getAttributes() {
        $attributes = $this->attributes;
        foreach ($attributes as $attribute) {
            $attribute->row = $this->row;
        }

        return $attributes;
    }

    public function parse() {
        $settedAttributes = [];
        $attributes = $this->attributes;
        foreach ($attributes as $key => $attribute) {
            $settedAttributes[$key] = $attribute->value;
        }

        $modelsFinder = $this->getModelsFinder();

        $modelsFinder->attributes = $attributes;

        $result = $modelsFinder->findModel();
        foreach ($result->getModels() as $model) {
            if ($this->isValidate) {
                $this->validateAttributes($model, array_keys($settedAttributes));
            }
        }

        return $result;
    }

    /**
     * @param ActiveRecord $model
     * @param $attributes
     */
    protected function validateAttributes($model, $attributesKeys) {
        $columns = [];
        $attributes = $this->attributes;
        foreach ($attributesKeys as $attribute) {
            if (isset($attributes[$attribute]) && $attributes[$attribute]->column !== null) {
                if (!$model->validate([$attribute], false)) {
                    $e = new Validate();
                    $e->errors = $model->errors;
                    $e->columnNbr = $attributes[$attribute]->column;
                    $e->attribute = $attribute;

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
}