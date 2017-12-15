<?php

namespace execut\import\models\forms;

use kartik\detail\DetailView;
use kartik\grid\ActionColumn;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class ImportLogs extends \execut\import\models\Log
{
    public function getDataProvider() {
        $query = self::find();
        $query->andFilterWhere([
            'id' => $this->id,
            'import_file_id' => $this->import_file_id,
        ]);

        $equalAttributes = [
            'level',
            'row_nbr',
            'column_nbr',
        ];

        foreach ($equalAttributes as $attribute) {
            $query->andFilterWhere([
                $attribute => $this->$attribute,
            ]);
        }
        $likeAttributes = [
            'category',
            'prefix',
            'message',
            'value'
        ];

        foreach ($likeAttributes as $attribute) {
            $query->andFilterWhere(['like', $attribute, $this->$attribute]);
        }

        $query->with('importSettingsValue');

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    public function rules() {
        return [[['id', 'level', 'category', 'prefix', 'message', 'row_nbr', 'column_nbr', 'value'], 'safe']];
    }

    public static function getGridColumns() {
        return [
            'id',
            'level',
            'category',
            'prefix',
            'message',
            'row_nbr',
            'column_nbr',
            'value',
//            'importFile',
            'importSettingsValue',
            [
                'class' => ActionColumn::className(),
                'controller' => 'import-logs',
                'buttons' => [
                    'update' => function () {},
//                    'view' => function () {},
//                    'delete' => function () {},
                ],
            ],
        ];
    }


    public function attributeLabels()
    {
        return [
            'level' => 'Уровень',
            'category' => 'Категория',
            'message' => 'Сообщение',
            'prefix' => 'Префикс',
            'rowNbr' => 'Строчка',
            'columnNbr' => 'Колонка',
            'value' => 'Значение',
            'importSettingsValue' => 'Результат',
        ];
    }

//    public static function getFormFields() {
//        return [
//            [
//                'type' => DetailView::INPUT_SELECT2,
//                'attribute' => 'import_setting_id',
//                'widgetOptions'=> [
//                    'pluginOptions' => [
//                        'allowClear' => true,
//                    ],
//                    'data' => ArrayHelper::merge(['' => ''], ArrayHelper::map(Setting::find()->all(), 'id', 'name')),
//                ],
//                'format' => 'html',
//            ],
//            [
//                'type' => DetailView::INPUT_SELECT2,
//                'attribute' => 'import_files_statuse_id',
//                'widgetOptions'=> [
//                    'pluginOptions' => [
//                        'allowClear' => true,
//                    ],
//                    'data' => ArrayHelper::merge(['' => ''], ArrayHelper::map(FilesStatuse::find()->all(), 'id', 'name')),
//                ],
//                'format' => 'html',
//            ],
//            [
//                'type' => DetailView::INPUT_FILEINPUT,
//                'attribute' => 'contentFile',
//            ],
//            [
//                'attribute' => 'name',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'extension',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'mime_type',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'md5',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'created',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'updated',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'rows_count',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'rows_errors',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'rows_success',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'start_date',
//                'displayOnly'=>true,
//            ],
//            [
//                'attribute' => 'end_date',
//                'displayOnly'=>true,
//            ],
//        ];
//    }

    public function search() {
        return $this->getDataProvider();
    }
}
