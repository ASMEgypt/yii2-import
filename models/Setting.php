<?php

namespace execut\import\models;

use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\fields\Boolean;
use execut\crudFields\fields\DropDown;
use execut\crudFields\fields\Email;
use execut\crudFields\fields\Group;
use execut\crudFields\fields\NumberField;
use execut\crudFields\ModelsHelperTrait;
use execut\import\components\Source;
use execut\importScheduler\models\ImportSettingsVsSchedulerEvents;
use execut\scheduler\models\SchedulerEvents;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

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
class Setting extends base\Setting
{
    use ModelsHelperTrait, BehaviorStub;
    const MODEL_NAME = '{n,plural,=0{Files} =1{File} other{Files}}';
    public static function find()
    {
        return new queries\Setting(static::class);
    }

//    public function rules()
//    {
//        return ArrayHelper::merge(parent::rules(), [
//            [['email'], 'email'],
//            ['ftp_ssl', 'default', 'value' => false,],
//        ]);
//    }

    public function getSource() {
        $adapter = $this->filesSource->getAdapterForSetting($this);
        $source = new Source([
            'adapter' => $adapter,
        ]);

        return $source;
    }

    public function behaviors()
    {
        return [
            'fields' => [
                'class' => Behavior::class,
                'module' => 'import',
                'fields' => $this->getStandardFields(['visible'], [
                    'ignored_lines' => [
                        'class' => NumberField::class,
                        'attribute' => 'ignored_lines',
                    ],
                    'is_check_mime_type' => [
                        'class' => Boolean::class,
                        'attribute' => 'is_check_mime_type',
                    ],
                    'csvGroup' => [
                        'class' => Group::class,
                        'label'=>'Настройки Csv',
                    ],
                    'csv_delimiter' => [
                        'attribute' => 'csv_delimiter',
                    ],
                    'csv_enclosure' => [
                        'attribute' => 'csv_enclosure',
                    ],
                    'import_files_source_id' => [
                        'class' => DropDown::class,
                        'attribute' => 'import_files_source_id',
                        'relation' => 'filesSource',
                    ],
                    'emailGroup' => [
                        'class' => Group::class,
                        'label'=>'Настройки Email',
                    ],
                    'email' => [
                        'class' => Email::class,
                        'attribute' => 'email',
                    ],
                    'email_title_match' => [
                        'attribute'=> 'email_title_match',
                    ],
                    'import_files_encoding_id' => [
                        'attribute' => 'import_files_encoding_id',
                        'relation' => $this->importFilesEncoding,
                    ],
//                    [
//                        'group'=>true,
//                        'label'=>'Настройки FTP',
//                        'rowOptions'=>['class'=>$labelClass]
//                    ],
//                    [
//                        'attribute' => 'ftp_host',
//                    ],
//                    [
//                        'type' => DetailView::INPUT_CHECKBOX,
//                        'attribute' => 'ftp_ssl',
//                    ],
//                    [
//                        'attribute' => 'ftp_port',
//                    ],
//                    [
//                        'attribute' => 'ftp_timeout',
//                    ],
//                    [
//                        'attribute' => 'ftp_login',
//                    ],
//                    [
//                        'attribute' => 'ftp_password',
//                    ],
//                    [
//                        'attribute' => 'ftp_dir',
//                    ],
//                    [
//                        'attribute' => 'ftp_file_name',
//                    ],
//                    [
//                        'group'=>true,
//                        'label'=>'Настройки Сайта',
//                        'rowOptions'=>['class'=>$labelClass]
//                    ],
//                    [
//                        'attribute' => 'site_host',
//                    ],
//                    [
//                        'attribute' => 'site_auth_url',
//                    ],
//                    [
//                        'attribute' => 'site_auth_method',
//                    ],
//                    [
//                        'attribute' => 'site_login_field',
//                    ],
//                    [
//                        'attribute' => 'site_password_field',
//                    ],
//                    [
//                        'attribute' => 'site_other_fields',
//                    ],
//                    [
//                        'attribute' => 'site_login',
//                    ],
//                    [
//                        'attribute' => 'site_password',
//                    ],
//                    [
//                        'attribute' => 'site_file_url',
//                    ],
                ]),
                'plugins' => \yii::$app->getModule('import')->getSettingsCrudFieldsPlugins(),
            ],
            'date' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public static function getImportFilesSourcesList() {
        return ArrayHelper::map(FilesSource::find()->all(), 'id', 'name');
    }

    public function getSiteOtherFields() {
        if (!empty($this->site_other_fields)) {
            return Json::decode($this->site_other_fields);
        }
    }

    public function getSchedulerEvents() {
        return $this->hasMany(SchedulerEvents::className(), [
            'id' => 'scheduler_event_id',
        ])->via('importSettingsVsSchedulerEvents');
    }

    public function getSettingsVsSchedulerEvents() {
        return $this->hasMany(ImportSettingsVsSchedulerEvents::className(), [
            'import_setting_id' => 'id',
        ]);
    }

    public function delete()
    {
        foreach ($this->importSettingsVsSchedulerEvents as $event) {
            $event->delete();
        }

        foreach ($this->importSettingsSheets as $sheet) {
            $sheet->delete();
        }

        return parent::delete(); // TODO: Change the autogenerated stub
    }

    public function __toString()
    {
        return '#' . $this->id . ' ' . $this->name;
    }
}
