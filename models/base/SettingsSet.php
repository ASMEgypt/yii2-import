<?php

namespace execut\import\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_settings_sets".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property string $type
 * @property string $import_settings_sheet_id
 *
 * @property \execut\import\models\SettingsSheet $settingsSheet
 * @property \execut\import\models\SettingsValue[] $settingsValues
 */
class SettingsSet extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_settings_sets';
    }

//    /**
//     * @inheritdoc
//     */
//    public function rules()
//    {
//        return ArrayHelper::merge(parent::rules(), [
//            [['created', 'updated'], 'safe'],
//            [['type', 'import_settings_sheet_id'], 'required'],
//            [['import_settings_sheet_id'], 'integer'],
//            [['type'], 'string', 'max' => 255],
//            [['import_settings_sheet_id'], 'exist', 'skipOnError' => true, 'targetClass' => SettingsSheet::className(), 'targetAttribute' => ['import_settings_sheet_id' => 'id']],
//        ]);
//    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingsSheet()
    {
        return $this->hasOne(\execut\import\models\SettingsSheet::className(), ['id' => 'import_settings_sheet_id'])->inverseOf('settingsSets');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingsValues()
    {
        return $this->hasMany(\execut\import\models\SettingsValue::className(), ['import_settings_set_id' => 'id'])->inverseOf('settingsSet');
    }
}
