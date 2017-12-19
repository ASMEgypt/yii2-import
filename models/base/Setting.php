<?php

namespace execut\import\models\base;

use Yii;
use yii\db\ActiveRecord;
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
 * @property string $csv_delimiter_old
 * @property string $csv_enclosure_old
 * @property integer $import_files_source_id
 * @property integer $import_files_encoding_id
 * @property string $csv_enclosure
 * @property string $csv_delimiter
 * @property string $ftp_host
 * @property boolean $ftp_ssl
 * @property integer $ftp_port
 * @property integer $ftp_timeout
 * @property string $ftp_login
 * @property string $ftp_password
 * @property string $ftp_dir
 * @property string $ftp_file_name
 * @property string $site_host
 * @property string $site_auth_url
 * @property string $site_auth_method
 * @property string $site_login_field
 * @property string $site_password_field
 * @property string $site_other_fields
 * @property string $site_login
 * @property string $site_password
 * @property string $site_file_url
 *
 * @property \execut\import\models\File[] $importFiles
 * @property \execut\import\models\FilesEncoding $importFilesEncoding
 * @property \execut\import\models\FilesSource $importFilesSource
 * @property \execut\import\models\SettingsSheet[] $importSettingsSheets
 */
class Setting extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created', 'updated', 'is_check_mime_type'], 'safe'],
            [['name', 'ignored_lines', 'import_files_source_id', 'import_files_encoding_id'], 'required'],
            [['ignored_lines', 'import_files_source_id', 'import_files_encoding_id', 'ftp_port', 'ftp_timeout'], 'integer'],
            [['ftp_ssl'], 'boolean'],
            [['name', 'email', 'email_title_match', 'csv_enclosure', 'csv_delimiter', 'ftp_host', 'ftp_login', 'ftp_password', 'ftp_dir', 'ftp_file_name', 'site_host', 'site_auth_url', 'site_auth_method', 'site_login_field', 'site_password_field', 'site_login', 'site_password', 'site_file_url'], 'string', 'max' => 255],
            [['csv_delimiter_old', 'csv_enclosure_old'], 'string', 'max' => 1],
            [['site_other_fields'], 'string', 'max' => 1000],
            [['import_files_encoding_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilesEncoding::className(), 'targetAttribute' => ['import_files_encoding_id' => 'id']],
            [['import_files_source_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilesSource::className(), 'targetAttribute' => ['import_files_source_id' => 'id']],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('execut.import.models.base.Setting', 'ID'),
            'created' => Yii::t('execut.import.models.base.Setting', 'Created'),
            'updated' => Yii::t('execut.import.models.base.Setting', 'Updated'),
            'name' => Yii::t('execut.import.models.base.Setting', 'Name'),
            'ignored_lines' => Yii::t('execut.import.models.base.Setting', 'Ignored Lines'),
            'email' => Yii::t('execut.import.models.base.Setting', 'Email'),
            'email_title_match' => Yii::t('execut.import.models.base.Setting', 'Email Title Match'),
            'csv_delimiter_old' => Yii::t('execut.import.models.base.Setting', 'Csv Delimiter Old'),
            'csv_enclosure_old' => Yii::t('execut.import.models.base.Setting', 'Csv Enclosure Old'),
            'import_files_source_id' => Yii::t('execut.import.models.base.Setting', 'Import Files Source ID'),
            'import_files_encoding_id' => Yii::t('execut.import.models.base.Setting', 'Import Files Encoding ID'),
            'csv_enclosure' => Yii::t('execut.import.models.base.Setting', 'Csv Enclosure'),
            'csv_delimiter' => Yii::t('execut.import.models.base.Setting', 'Csv Delimiter'),
            'ftp_host' => Yii::t('execut.import.models.base.Setting', 'Ftp Host'),
            'ftp_ssl' => Yii::t('execut.import.models.base.Setting', 'Ftp Ssl'),
            'ftp_port' => Yii::t('execut.import.models.base.Setting', 'Ftp Port'),
            'ftp_timeout' => Yii::t('execut.import.models.base.Setting', 'Ftp Timeout'),
            'ftp_login' => Yii::t('execut.import.models.base.Setting', 'Ftp Login'),
            'ftp_password' => Yii::t('execut.import.models.base.Setting', 'Ftp Password'),
            'ftp_dir' => Yii::t('execut.import.models.base.Setting', 'Ftp Dir'),
            'ftp_file_name' => Yii::t('execut.import.models.base.Setting', 'Ftp File Name'),
            'site_host' => Yii::t('execut.import.models.base.Setting', 'Site Host'),
            'site_auth_url' => Yii::t('execut.import.models.base.Setting', 'Site Auth Url'),
            'site_auth_method' => Yii::t('execut.import.models.base.Setting', 'Site Auth Method'),
            'site_login_field' => Yii::t('execut.import.models.base.Setting', 'Site Login Field'),
            'site_password_field' => Yii::t('execut.import.models.base.Setting', 'Site Password Field'),
            'site_other_fields' => Yii::t('execut.import.models.base.Setting', 'Site Other Fields'),
            'site_login' => Yii::t('execut.import.models.base.Setting', 'Site Login'),
            'site_password' => Yii::t('execut.import.models.base.Setting', 'Site Password'),
            'site_file_url' => Yii::t('execut.import.models.base.Setting', 'Site File Url'),
        ]);
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
