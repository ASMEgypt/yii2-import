<?php

namespace execut\import\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_settings_sheets".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property integer $order
 * @property string $import_setting_id
 *
 * @property \execut\import\models\SettingsSet[] $settingsSets
 * @property \execut\import\models\Setting $setting
 */
class SettingsSheet extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_settings_sheets';
    }

//    /**
//     * @inheritdoc
//     */
//    public function rules()
//    {
//        return ArrayHelper::merge(parent::rules(), [
//            [['created', 'updated'], 'safe'],
//            [['name', 'order', 'import_setting_id'], 'required'],
//            [['order', 'import_setting_id'], 'integer'],
//            [['name'], 'string', 'max' => 255],
//            [['import_setting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Setting::class, 'targetAttribute' => ['import_setting_id' => 'id']],
//        ]);
//    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingsSets()
    {
        return $this->hasMany(\execut\import\models\SettingsSet::class, ['import_settings_sheet_id' => 'id'])->inverseOf('settingsSheet');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetting()
    {
        return $this->hasOne(\execut\import\models\Setting::class, ['id' => 'import_setting_id'])->inverseOf('settingsSheets');
    }
}
