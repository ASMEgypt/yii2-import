<?php

namespace execut\import\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_logs".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property integer $level
 * @property string $category
 * @property string $prefix
 * @property string $message
 * @property integer $row_nbr
 * @property integer $column_nbr
 * @property string $value
 * @property string $import_file_id
 * @property string $import_settings_value_id
 *
 * @property \execut\import\models\File $importFile
 * @property \execut\import\models\SettingsValue $importSettingsValue
 */
class Log extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_logs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created', 'updated'], 'safe'],
            [['level', 'category', 'import_file_id'], 'required'],
            [['level', 'row_nbr', 'column_nbr', 'import_file_id', 'import_settings_value_id'], 'integer'],
            [['message'], 'string'],
            [['category', 'prefix', 'value'], 'string', 'max' => 255],
            [['import_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['import_file_id' => 'id']],
            [['import_settings_value_id'], 'exist', 'skipOnError' => true, 'targetClass' => SettingsValue::className(), 'targetAttribute' => ['import_settings_value_id' => 'id']],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('execut.import.models.base.Log', 'ID'),
            'created' => Yii::t('execut.import.models.base.Log', 'Created'),
            'updated' => Yii::t('execut.import.models.base.Log', 'Updated'),
            'level' => Yii::t('execut.import.models.base.Log', 'Level'),
            'category' => Yii::t('execut.import.models.base.Log', 'Category'),
            'prefix' => Yii::t('execut.import.models.base.Log', 'Prefix'),
            'message' => Yii::t('execut.import.models.base.Log', 'Message'),
            'row_nbr' => Yii::t('execut.import.models.base.Log', 'Row Nbr'),
            'column_nbr' => Yii::t('execut.import.models.base.Log', 'Column Nbr'),
            'value' => Yii::t('execut.import.models.base.Log', 'Value'),
            'import_file_id' => Yii::t('execut.import.models.base.Log', 'Import File ID'),
            'import_settings_value_id' => Yii::t('execut.import.models.base.Log', 'Import Settings Value ID'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImportFile()
    {
        return $this->hasOne(\execut\import\models\File::className(), ['id' => 'import_file_id'])->inverseOf('importLogs');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImportSettingsValue()
    {
        return $this->hasOne(\execut\import\models\SettingsValue::className(), ['id' => 'import_settings_value_id'])->inverseOf('importLogs');
    }
}
