<?php
/**
 * User: execut
 * Date: 19.07.16
 * Time: 16:20
 */

namespace execut\import\components;


use yii\base\Component;

class Saver extends Component
{
    public function save($model) {
//        $model->id = 1;
//        if (!$model->validate()) {
//            echo $model;
//            var_dump($model->attributes);
//            var_dump($model->errors);
//        } else {
//            echo $model . ' is saved' . "\n";
//        }
//        return $model;

        if ($model->save()) {
//            echo $model->id . ' is saved' . "\n";
            return $model;
        } else {
            var_dump($model->errors);
        }
    }
}