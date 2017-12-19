<?php

namespace execut\import\models;
use execut\CacheTrait;
use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\fields\HasManyMultipleInput;
use execut\crudFields\fields\Hidden;
use execut\crudFields\fields\NumberField;
use execut\crudFields\ModelsHelperTrait;
use execut\import\components\parser\Stack;
use execut\import\components\Saver;
use execut\import\components\SettingsValueExtractor;
use execut\yii\helpers\ArrayHelper;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

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
    use CacheTrait, BehaviorStub, ModelsHelperTrait;
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
        return self::_cacheStatic(function () {
            $settings = [];
            $extractor = new SettingsValueExtractor();
            $typesSettings = self::getParsersByTypesSettings();
            $saver = new Saver();
            foreach ($this->settingsSets as $set) {
                $typeSettings = [];
                foreach ($set->importSettingsValues as $value) {
                    $extractor->model = $value;
                    $typeSettings = ArrayHelper::merge($typeSettings, $extractor->extract());
                }

                foreach ($typeSettings as $typeSetting) {
                    $typeSetting['parser'] = $saver;
                }

                $settings[] = ArrayHelper::merge($typesSettings[$set->type], [
                    'parsers' => $typeSettings,
                ]);
            }

            return $settings;
        }, 'settings' . $this->id);
    }

    public function delete()
    {
        foreach ($this->settingsSets as $set) {
            $set->delete();
        }

        return parent::delete();
    }
}
