<?php

namespace execut\import\models;
use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\fields\HasManyMultipleInput;
use execut\crudFields\fields\Hidden;
use execut\crudFields\fields\NumberField;
use execut\crudFields\ModelsHelperTrait;
use execut\import\components\Saver;
use execut\import\components\SettingsValueExtractor;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
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
class SettingsSheet extends base\SettingsSheet
{
    use BehaviorStub, ModelsHelperTrait;
    protected static $settingsByIds = [];

    public static function find()
    {
        return new queries\SettingsSheet(static::class);
    }

    public function behaviors()
    {
        return [
            'relationsSaver' => [
                'class' => SaveRelationsBehavior::class,
                'relations' => [
                    'settingsSets'
                ],
            ],
            'fields' => [
                'class' => Behavior::class,
                'module' => 'import',
                'fields' => $this->getStandardFields(['visible'], [
                    'import_setting_id' => [
                        'class' => Hidden::class,
                        'attribute' => 'import_setting_id',
                    ],
                    'order' => [
                        'class' => NumberField::class,
                        'attribute' => 'order',
                        'defaultValue' => 1,
                    ],
                    'settingsSets' => [
                        'class' => HasManyMultipleInput::class,
                        'nameAttribute' => null,
                        'required' => true,
                        'attribute' => 'settingsSets',
                        'relation' => 'settingsSets',
                    ],
                ]),
                'plugins' => \yii::$app->getModule('import')->getSettingsSheetsCrudFieldsPlugins(),
            ],
            'date' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
    }


    public static function getDictionaries() {
        return \yii::$app->getModule('import')->getDictionaries();
    }

    public static function getParsersByTypesSettings() {
        return \yii::$app->getModule('import')->getParsersByTypesSettings();
    }

    public function getSettings() {
        if (!empty(self::$settingsByIds[$this->id])) {
            return self::$settingsByIds[$this->id];
        }

        $settings = [];
        $extractor = new SettingsValueExtractor();
        $typesSettings = self::getParsersByTypesSettings();
        foreach ($this->settingsSets as $set) {
            if ($set->type === 'details_base_id_new') {
                continue;
            }

            $typeSettings = [];
            foreach ($set->settingsValues as $value) {
                $extractor->model = $value;
                $typeSettings = ArrayHelper::merge($typeSettings, $extractor->extract());
            }

//            foreach ($typeSettings as $setting) {
//                foreach ($settings['attributes'] as $attribute) {
//                }
//            }

            $settings = ArrayHelper::merge($settings, $typesSettings[$set->type]);
            $settings = ArrayHelper::merge($settings, $typeSettings);
        }

        self::$settingsByIds[$this->id] = $settings;

        return $settings;
    }

    public function delete()
    {
        foreach ($this->settingsSets as $set) {
            $set->delete();
        }

        return parent::delete();
    }
}
