<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/19/17
 * Time: 5:49 PM
 */

namespace execut\import\example\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class Product extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'date' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['id', 'name', 'price'], 'required'],
        ];
    }

    public static function tableName()
    {
        return 'example_products';
    }
}