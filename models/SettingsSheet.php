<?php

namespace execut\import\models;
use execut\CacheTrait;
use execut\import\components\parser\Stack;
use execut\import\components\Saver;
use execut\import\components\SettingsValueExtractor;
use execut\yii\helpers\ArrayHelper;
use yii\base\Exception;

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
class SettingsSheet extends base\SettingsSheet
{
    use CacheTrait;
    public static function getDictionaries() {
        return \yii::$app->import->getDictionaries();
    }

    public static function getParsersByTypesSettings() {
        return \yii::$app->import->getParsersByTypesSettings();
    }

    public function getSettings() {
        return self::_cacheStatic(function () {
            $settings = [];
            $extractor = new SettingsValueExtractor();
            $typesSettings = self::getParsersByTypesSettings();
            $saver = new Saver();
            foreach ($this->importSettingsSets as $set) {
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
        foreach ($this->importSettingsSets as $set) {
            $set->delete();
        }

        return parent::delete();
    }

    public function attributeLabels()
    {
         return [
            'id' => 'ID',
            'created' => 'Создано',
            'updated' => 'Обновлено',
            'name' => 'Название',
            'order' => 'Порядок',
            'import_setting_id' => 'Настройка',
        ];
    }
}
