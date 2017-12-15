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
 * @property \execut\import\models\SettingsSet[] $importSettingsSets
 * @property \execut\import\models\Setting $importSetting
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created', 'updated'], 'safe'],
            [['name', 'order', 'import_setting_id'], 'required'],
            [['order', 'import_setting_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['import_setting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Setting::className(), 'targetAttribute' => ['import_setting_id' => 'id']],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('execut.import.models.base.SettingsSheet', 'ID'),
            'created' => Yii::t('execut.import.models.base.SettingsSheet', 'Created'),
            'updated' => Yii::t('execut.import.models.base.SettingsSheet', 'Updated'),
            'name' => Yii::t('execut.import.models.base.SettingsSheet', 'Name'),
            'order' => Yii::t('execut.import.models.base.SettingsSheet', 'Order'),
            'import_setting_id' => Yii::t('execut.import.models.base.SettingsSheet', 'Import Setting ID'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImportSettingsSets()
    {
        return $this->hasMany(\execut\import\models\SettingsSet::className(), ['import_settings_sheet_id' => 'id'])->inverseOf('importSettingsSheet');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImportSetting()
    {
        return $this->hasOne(\execut\import\models\Setting::className(), ['id' => 'import_setting_id'])->inverseOf('importSettingsSheets');
    }
}
