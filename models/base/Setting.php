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
            [['site_other_fields'], 'string', 'max' => 1000],
            [['import_files_encoding_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilesEncoding::class, 'targetAttribute' => ['import_files_encoding_id' => 'id']],
            [['import_files_source_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilesSource::class, 'targetAttribute' => ['import_files_source_id' => 'id']],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(\execut\import\models\File::class, ['import_setting_id' => 'id'])->inverseOf('importSetting');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilesEncoding()
    {
        return $this->hasOne(\execut\import\models\FilesEncoding::class, ['id' => 'import_files_encoding_id'])->inverseOf('settings');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilesSource()
    {
        return $this->hasOne(\execut\import\models\FilesSource::class, ['id' => 'import_files_source_id'])->inverseOf('settings');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingsSheets()
    {
        return $this->hasMany(\execut\import\models\SettingsSheet::class, ['import_setting_id' => 'id'])->inverseOf('setting');
    }
}
