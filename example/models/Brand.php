<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/22/17
 * Time: 11:49 AM
 */

namespace execut\import\example\models;


use execut\import\ModelInterface;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class Brand extends ActiveRecord // implements ModelInterface
{
//    public function getImportUniqueKeys($attributesNames)
//    {
//        $uniqueKeys = [
//            serialize(['name' => $this->name]),
//        ];
//        foreach ($this->aliases as $alias) {
//            $uniqueKeys[] = serialize(['name' => $alias->name]);
//        }
//
//        return $uniqueKeys;
//    }

    public static function find()
    {
        return new \execut\import\example\models\queries\Brand(self::class);
    }

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
            [['name'], 'required'],
        ];
    }

    public static function tableName()
    {
        return 'example_brands';
    }

    public function getAliases() {
        return $this->hasMany(BrandsAlias::class, [
            'example_brand_id' => 'id',
        ]);
    }
}