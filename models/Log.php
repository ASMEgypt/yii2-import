<?php

namespace execut\import\models;
use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\ModelsHelperTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Log extends \execut\import\models\base\Log
{
    use ModelsHelperTrait, BehaviorStub;

    const MODEL_NAME = '{n,plural,=0{Files} =1{File} other{Files}}';

    public function behaviors()
    {
        return [
            'fields' => [
                'class' => Behavior::class,
                'module' => 'import',
                'fields' => $this->getStandardFields(['visible', 'name', 'actions'], [
                    'level',
                    'category',
                    'prefix',
                    'message',
                    'row_nbr',
                    'column_nbr',
                    'value',
                    'settingsValue' => [
                        'relation' => 'settingsValue',
                    ],
                ]),
                'plugins' => [],
            ],
            'date' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}
