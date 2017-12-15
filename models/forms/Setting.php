<?php

namespace execut\import\models\forms;

use execut\import\models\base\FilesEncoding;
use execut\import\models\FilesSource;
use kartik\detail\DetailView;
use kartik\grid\ActionColumn;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_settings".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property integer $ignored_lines
 * @property string $email
 * @property string $email_title_match
 * @property string $csv_delimiter
 * @property string $csv_enclosure
 * @property string $import_files_source_id
 *
 * @property \execut\import\models\File[] $importFiles
 * @property \execut\import\models\FilesSource $importFilesSource
 * @property \execut\import\models\SettingsSheet[] $importSettingsSheets
 */
class Setting extends \execut\import\models\Setting
{
    public $schedule = null;
//    public $scheduler_event_id = null;
//    public $importSettingsValues = [];
//    public $importSettingsSets = [
//        'importSettingsValues' => [],
//        [
//            'importSettingsValues' => [],
//        ]
//    ];
    public function getDataProvider() {
        return new ActiveDataProvider([
            'query' => self::find(),
        ]);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'sheets',
            'importSettingsValues',
        ]);
    }

    public static function getFormFields() {
        $labelClass = DetailView::TYPE_INFO;
        return [
            [
                'attribute' => 'id',
                'displayOnly'=>true,
            ],
            [
                'attribute' => 'name',
            ],
            [
                'attribute' => 'ignored_lines',
                'type' => DetailView::INPUT_TEXT,
                'options' => [
                    'type' => 'number',
                ],
            ],
            [
                'attribute' => 'is_check_mime_type',
                'type' => DetailView::INPUT_CHECKBOX,
            ],
            [
                'attribute' => 'created',
                'displayOnly'=>true,
            ],
            [
                'attribute' => 'updated',
                'displayOnly'=>true,
            ],
            [
                'group'=>true,
                'label'=>'Настройки Csv',
                'rowOptions'=>['class'=>$labelClass]
            ],
            [
                'attribute' => 'csv_delimiter',
            ],
            [
                'attribute' => 'csv_enclosure',
            ],
            [
                'attribute' => 'import_files_source_id',
                'type' => 'dropDownList',
                'items' => ArrayHelper::merge(['' => ''], self::getImportFilesSourcesList()),
                'value' => function ($row, $detailView) {
                    $row = $detailView->model;
                    if ($row->importFilesSource) {
                        return $row->importFilesSource->name;
                    }
                },
            ],
            [
                'group'=>true,
                'label'=>'Настройки Email',
                'rowOptions'=>['class'=>$labelClass]
            ],
            [
                'attribute' => 'email',
            ],
            [
                'attribute'=> 'email_title_match',
            ],
            [
                'attribute' => 'import_files_encoding_id',
                'type' => 'dropDownList',
                'items' => \execut\yii\helpers\ArrayHelper::map(FilesEncoding::find()->all(), 'id', 'name'),
                'value' => function ($row, $detailView) {
                    $row = $detailView->model;
                    if ($row->importFilesEncoding) {
                        return $row->importFilesEncoding->name;
                    }
                },
            ],
            [
                'group'=>true,
                'label'=>'Настройки FTP',
                'rowOptions'=>['class'=>$labelClass]
            ],
            [
                'attribute' => 'ftp_host',
            ],
            [
                'type' => DetailView::INPUT_CHECKBOX,
                'attribute' => 'ftp_ssl',
            ],
            [
                'attribute' => 'ftp_port',
            ],
            [
                'attribute' => 'ftp_timeout',
            ],
            [
                'attribute' => 'ftp_login',
            ],
            [
                'attribute' => 'ftp_password',
            ],
            [
                'attribute' => 'ftp_dir',
            ],
            [
                'attribute' => 'ftp_file_name',
            ],
            [
                'group'=>true,
                'label'=>'Настройки Сайта',
                'rowOptions'=>['class'=>$labelClass]
            ],
            [
                'attribute' => 'site_host',
            ],
            [
                'attribute' => 'site_auth_url',
            ],
            [
                'attribute' => 'site_auth_method',
            ],
            [
                'attribute' => 'site_login_field',
            ],
            [
                'attribute' => 'site_password_field',
            ],
            [
                'attribute' => 'site_other_fields',
            ],
            [
                'attribute' => 'site_login',
            ],
            [
                'attribute' => 'site_password',
            ],
            [
                'attribute' => 'site_file_url',
            ],
        ];
    }

    public static function getSheetsNumbers() {
        return [1,2,3,4,5];
    }

    public function getGridColumns() {
        return [
            'id',
            'name',
            'importFilesSource.name',
            'email',
            [
                'attribute'=> 'email_title_match',
            ],
            'created',
            'updated',
            [
                'class' => ActionColumn::className(),
                'buttons' => [
//                    'create' => function () {},
//                    'view' => function () {},
//                    'delete' => function () {},
                ],
            ],
        ];
    }

    public function getImportSettingsSheets()
    {
        return $this->hasMany(\execut\import\models\forms\ImportSettingsSheets::className(), ['import_setting_id' => 'id'])->inverseOf('importSetting');
    }

    public function beforeDelete()
    {
        foreach ($this->importSettingsSheets as $sheet) {
            $sheet->delete();
        }

        foreach ($this->importFiles as $file) {
            $file->delete();
        }

        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    public function search() {
        return $this->getDataProvider();
    }
}
