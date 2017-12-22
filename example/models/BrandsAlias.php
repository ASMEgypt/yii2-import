<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/22/17
 * Time: 11:49 AM
 */

namespace execut\import\example\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class BrandsAlias extends ActiveRecord
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
            ['id', 'safe'],
            [['name', 'example_product_id'], 'required'],
        ];
    }

    public static function tableName()
    {
        return 'example_brands_aliases';
    }
}