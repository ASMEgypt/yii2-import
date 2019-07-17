<?php

namespace execut\import\models;
use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\fields\HasOneSelect2;
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
//                    'file' => [
//                        'class' => HasOneSelect2::class,
//                        'attribute' => 'import_file_id',
//                        'relation' => 'file',
//                        'scope' => false,
//                    ],
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
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function search()
    {
        $dataProvider = $this->getBehavior('fields')->search();

        return $dataProvider;
    }
}
