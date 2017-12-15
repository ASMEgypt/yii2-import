<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 3/14/17
 * Time: 11:48 AM
 */

namespace execut\import\components\parser;


use yii\base\Component;

class Result extends Component
{
    protected $_models = [];

    public function setModels($models) {
        $this->_models = $models;
    }

    public function addModel($model) {
        $this->_models[] = $model;
    }

    public function getModels() {
        return $this->_models;
    }

    public function getModel($key = 0) {
        if (empty($this->_models[$key])) {
            return false;
        }

        return $this->_models[$key];
    }
}