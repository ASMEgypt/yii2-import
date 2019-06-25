<?php

namespace execut\import\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_settings_values".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $type
 * @property string $column_nbr
 * @property string $format
 * @property string $value_string
 * @property string $value_option
 * @property string $import_settings_set_id
 * @property string $number_delimiter
 *
 * @property \execut\import\models\Log[] $logs
 * @property \execut\import\models\SettingsSet $settingsSet
 */
class SettingsValue extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_settings_values';
    }

    /**
     * @inheritdoc
     */
//    public function rules()
//    {
//        return ArrayHelper::merge(parent::rules(), [
//            [['created', 'updated'], 'safe'],
//            [['type', 'import_settings_set_id'], 'required'],
//            [['import_settings_set_id'], 'integer'],
//            [['type', 'column_nbr', 'format', 'value_string', 'value_option', 'number_delimiter'], 'string', 'max' => 255],
//            [['import_settings_set_id'], 'exist', 'skipOnError' => true, 'targetClass' => SettingsSet::className(), 'targetAttribute' => ['import_settings_set_id' => 'id']],
//        ]);
//    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(\execut\import\models\Log::className(), ['import_settings_value_id' => 'id'])->inverseOf('settingsValue');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingsSet()
    {
        return $this->hasOne(\execut\import\models\SettingsSet::className(), ['id' => 'import_settings_set_id'])->inverseOf('settingsValues');
    }
}
