<?php

namespace execut\import\models;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_settings_values".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property string $type
 * @property string $column_nbr
 * @property string $format
 * @property string $value_string
 * @property string $value_option
 * @property string $import_settings_set_id
 *
 * @property \execut\import\models\SettingsSet $importSettingsSet
 */
class SettingsValue extends base\SettingsValue
{
    public function rules()
    {
        $rules = parent::rules();

        return ArrayHelper::merge([
            [['number_delimiter'], 'default', 'value' => '.'],
        ], $rules); // TODO: Change the autogenerated stub
    }

    public function delete()
    {
        foreach ($this->importLogs as $importLog) {
            $importLog->delete();
        }

        return parent::delete(); // TODO: Change the autogenerated stub
    }
}
