<?php

namespace execut\import\models;

use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\fields\Boolean;
use execut\crudFields\fields\DropDown;
use execut\crudFields\fields\Email;
use execut\crudFields\fields\Group;
use execut\crudFields\fields\HasManyMultipleInput;
use execut\crudFields\fields\NumberField;
use execut\crudFields\ModelsHelperTrait;
use execut\import\components\Source;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
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
class Setting extends ActiveRecord
{
    use ModelsHelperTrait, BehaviorStub;
    const MODEL_NAME = '{n,plural,=0{Setting} =1{Setting} other{Settings}}';

    public function rules()
    {
        return $this->getBehavior('fields')->rules();
    }

    public static function find()
    {
        return new queries\Setting(static::class);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_settings';
    }

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
            'relationsSaver' => [
                'class' => SaveRelationsBehavior::class,
                'relations' => [
                    'settingsSheets'
                ],
            ],
            'fields' => [
                'class' => Behavior::class,
                'module' => 'import',
                'fields' => $this->getStandardFields(['visible'], [
                    'ignored_lines' => [
                        'class' => NumberField::class,
                        'attribute' => 'ignored_lines',
                        'required' => true,
                    ],
                    'is_check_mime_type' => [
                        'class' => Boolean::class,
                        'attribute' => 'is_check_mime_type',
                    ],
                    'import_files_source_id' => [
                        'class' => DropDown::class,
                        'attribute' => 'import_files_source_id',
                        'relation' => 'filesSource',
                        'required' => true,
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
                        'class' => DropDown::class,
                        'attribute' => 'import_files_encoding_id',
                        'relation' => 'filesEncoding',
                        'required' => true,
                    ],
                    'ftpGroup' => [
                        'class' => Group::class,
                        'label'=>'Настройки FTP',
                    ],
                    'ftp_host' => [
                        'attribute' => 'ftp_host',
                    ],
                    'ftp_ssl' => [
                        'class' => Boolean::class,
                        'attribute' => 'ftp_ssl',
                        'defaultValue' => false,
                    ],
                    'ftp_port' => [
                        'attribute' => 'ftp_port',
                    ],
                    'ftp_timeout' => [
                        'attribute' => 'ftp_timeout',
                    ],
                    'ftp_login' => [
                        'attribute' => 'ftp_login',
                    ],
                    'ftp_password' => [
                        'attribute' => 'ftp_password',
                    ],
                    'ftp_dir' => [
                        'attribute' => 'ftp_dir',
                    ],
                    'ftp_file_name' => [
                        'attribute' => 'ftp_file_name',
                    ],
                    'siteSettingsGroup' => [
                        'class' => Group::class,
                        'label'=>'Настройки Сайта',
                    ],
                    'site_host' => [
                        'attribute' => 'site_host',
                    ],
                    'site_auth_url' => [
                        'attribute' => 'site_auth_url',
                    ],
                    'site_auth_method' => [
                        'attribute' => 'site_auth_method',
                    ],
                    'site_login_field' => [
                        'attribute' => 'site_login_field',
                    ],
                    'site_password_field' => [
                        'attribute' => 'site_password_field',
                    ],
                    'site_other_fields' => [
                        'attribute' => 'site_other_fields',
                    ],
                    'site_login' => [
                        'attribute' => 'site_login',
                    ],
                    'site_password' => [
                        'attribute' => 'site_password',
                    ],
                    'site_file_url' => [
                        'attribute' => 'site_file_url',
                    ],
                    'sheetsGroup' => [
                        'class' => Group::class,
                        'label' => 'Листы',
                    ],
                    'settingsSheets' => [
                        'class' => HasManyMultipleInput::class,
                        'attribute' => 'settingsSheets',
                        'relation' => 'settingsSheets',
                        'column' => false,
                    ],
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

    public static function getFilesSourcesList() {
        return ArrayHelper::map(FilesSource::find()->all(), 'id', 'name');
    }

    public function getSiteOtherFields() {
        if (!empty($this->site_other_fields)) {
            return Json::decode($this->site_other_fields);
        }
    }

    public function delete()
    {
        foreach ($this->importSettingsVsSchedulerEvents as $event) {
            $event->delete();
        }

        foreach ($this->settingsSheets as $sheet) {
            $sheet->delete();
        }

        foreach ($this->files as $sheet) {
            $sheet->delete();
        }

        return parent::delete(); // TODO: Change the autogenerated stub
    }

    public function __toString()
    {
        return '#' . $this->id . ' ' . $this->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(\execut\import\models\File::className(), ['import_setting_id' => 'id'])->inverseOf('importSetting');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilesEncoding()
    {
        return $this->hasOne(\execut\import\models\FilesEncoding::className(), ['id' => 'import_files_encoding_id'])->inverseOf('settings');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilesSource()
    {
        return $this->hasOne(\execut\import\models\FilesSource::className(), ['id' => 'import_files_source_id'])->inverseOf('settings');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingsSheets()
    {
        return $this->hasMany(\execut\import\models\SettingsSheet::className(), ['import_setting_id' => 'id'])->inverseOf('setting');
    }
}
