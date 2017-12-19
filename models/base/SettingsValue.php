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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('execut.import.models.base.SettingsValue', 'ID'),
            'created' => Yii::t('execut.import.models.base.SettingsValue', 'Created'),
            'updated' => Yii::t('execut.import.models.base.SettingsValue', 'Updated'),
            'type' => Yii::t('execut.import.models.base.SettingsValue', 'Type'),
            'column_nbr' => Yii::t('execut.import.models.base.SettingsValue', 'Column Nbr'),
            'format' => Yii::t('execut.import.models.base.SettingsValue', 'Format'),
            'value_string' => Yii::t('execut.import.models.base.SettingsValue', 'Value String'),
            'value_option' => Yii::t('execut.import.models.base.SettingsValue', 'Value Option'),
            'import_settings_set_id' => Yii::t('execut.import.models.base.SettingsValue', 'Import Settings Set ID'),
            'number_delimiter' => Yii::t('execut.import.models.base.SettingsValue', 'Number Delimiter'),
        ]);
    }

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
