<?php

namespace execut\import\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_files".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property string $extension
 * @property string $mime_type
 * @property resource $content
 * @property string $md5
 * @property string $import_files_source_id
 * @property string $use_id
 * @property string $import_files_statuse_id
 * @property string $import_setting_id
 * @property integer $rows_count
 * @property integer $rows_errors
 * @property integer $rows_success
 * @property string $start_date
 * @property string $end_date
 *
 * @property \execut\import\models\FilesSource $importFilesSource
 * @property \execut\import\models\FilesStatuse $importFilesStatuse
 * @property \execut\import\models\Setting $importSetting
 * @property \execut\import\models\User $use
 * @property \execut\import\models\Log[] $importLogs
 */
class File extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created', 'updated', 'start_date', 'end_date'], 'safe'],
            [['name', 'extension', 'mime_type', 'content', 'md5', 'import_files_source_id', 'import_files_statuse_id', 'import_setting_id'], 'required'],
            'content' => [['content'], 'string'],
            [['import_files_source_id', 'use_id', 'import_files_statuse_id', 'import_setting_id', 'rows_count', 'rows_errors', 'rows_success'], 'integer'],
            [['name', 'extension', 'mime_type'], 'string', 'max' => 255],
            [['md5'], 'string', 'max' => 64],
            [['import_files_source_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilesSource::className(), 'targetAttribute' => ['import_files_source_id' => 'id']],
            [['import_files_statuse_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilesStatuse::className(), 'targetAttribute' => ['import_files_statuse_id' => 'id']],
            [['import_setting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Setting::className(), 'targetAttribute' => ['import_setting_id' => 'id']],
            [['use_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['use_id' => 'id']],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('execut.import.models.base.File', 'ID'),
            'created' => Yii::t('execut.import.models.base.File', 'Created'),
            'updated' => Yii::t('execut.import.models.base.File', 'Updated'),
            'name' => Yii::t('execut.import.models.base.File', 'Name'),
            'extension' => Yii::t('execut.import.models.base.File', 'Extension'),
            'mime_type' => Yii::t('execut.import.models.base.File', 'Mime Type'),
            'content' => Yii::t('execut.import.models.base.File', 'Content'),
            'md5' => Yii::t('execut.import.models.base.File', 'Md5'),
            'import_files_source_id' => Yii::t('execut.import.models.base.File', 'Import Files Source ID'),
            'use_id' => Yii::t('execut.import.models.base.File', 'Use ID'),
            'import_files_statuse_id' => Yii::t('execut.import.models.base.File', 'Import Files Statuse ID'),
            'import_setting_id' => Yii::t('execut.import.models.base.File', 'Import Setting ID'),
            'rows_count' => Yii::t('execut.import.models.base.File', 'Rows Count'),
            'rows_errors' => Yii::t('execut.import.models.base.File', 'Rows Errors'),
            'rows_success' => Yii::t('execut.import.models.base.File', 'Rows Success'),
            'start_date' => Yii::t('execut.import.models.base.File', 'Start Date'),
            'end_date' => Yii::t('execut.import.models.base.File', 'End Date'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(\execut\import\models\FilesSource::className(), ['id' => 'import_files_source_id'])->inverseOf('files');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatuse()
    {
        return $this->hasOne(\execut\import\models\FilesStatuse::className(), ['id' => 'import_files_statuse_id'])->inverseOf('files');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetting()
    {
        return $this->hasOne(\execut\import\models\Setting::className(), ['id' => 'import_setting_id'])->inverseOf('files');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUse()
    {
        return $this->hasOne(\execut\import\models\User::className(), ['id' => 'use_id'])->inverseOf('files');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(\execut\import\models\Log::className(), ['import_file_id' => 'id'])->inverseOf('file');
    }
}
