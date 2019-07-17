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
            [['import_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['import_file_id' => 'id']],
            [['import_settings_value_id'], 'exist', 'skipOnError' => true, 'targetClass' => SettingsValue::class, 'targetAttribute' => ['import_settings_value_id' => 'id']],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(\execut\import\models\File::class, ['id' => 'import_file_id'])->inverseOf('logs');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingsValue()
    {
        return $this->hasOne(\execut\import\models\SettingsValue::class, ['id' => 'import_settings_value_id'])->inverseOf('logs');
    }
}
