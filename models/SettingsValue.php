<?php

namespace execut\import\models;
use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\fields\DropDown;
use execut\crudFields\fields\HasOneSelect2;
use execut\crudFields\fields\Hidden;
use execut\crudFields\fields\NumberField;
use execut\crudFields\ModelsHelperTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;

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
 * @property \execut\import\models\SettingsSet $settingsSet
 */
class SettingsValue extends base\SettingsValue
{
    use BehaviorStub, ModelsHelperTrait;

    /**
     * @return \execut\import\models\queries\SettingsValue
     */
    public static function find()
    {
        return new \execut\import\models\queries\SettingsValue(static::class);
    }

    public function behaviors()
    {
        return [
            'fields' => [
                'class' => Behavior::class,
                'module' => 'import',
                'fields' => [
                    'import_settings_set_id' => [
                        'class' => Hidden::class,
                        'attribute' => 'import_settings_set_id',
                    ],
                    'type' => [
                        'class' => DropDown::class,
                        'attribute' => 'type',
                        'data' => function () {
                            return self::getAttributesValuesTypesList();
                        },
                    ],
                    'column_nbr' => [
                        'class' => NumberField::class,
                        'attribute' => 'column_nbr',
                    ],
                    'number_delimiter' => [
                        'attribute' => 'number_delimiter',
                        'required' => true,
                        'defaultValue' => ',',
                    ],
                    'value_string' => [
                        'attribute' => 'value_string',
                    ],
                    'value_option' => [
                        'class' => HasOneSelect2::class,
                        'attribute' => 'value_option',
                        'nameParam' => 'name',
//                        'relation' => 'dictionariesData',
                        'data' => function () {
                            if (!$this->settingsSet || !$this->settingsSet->settingsSheet || !$this->settingsSet->settingsSheet->settingsSets) {
                                return [];
                            }
                            $dictionaryOptions = ['' => ''];
                            $types = SettingsSheet::getDictionaries();
//                            return [];
                            foreach ($this->settingsSet->settingsSheet->settingsSets as $settingsSet) {
                                foreach ($settingsSet->settingsValues as $value) {
                                    $type = explode('.', $value->type)[0];
                                    if (!empty($types[$type]) && !empty($value->value_option)) {
                                        if ($model = $types[$type]->byId($value->value_option)->one()) {
                                            $dictionaryOptions[$model->id] = $model->name;
                                        }
                                    }
                                }
                            }

                            return $dictionaryOptions;
                        },
                        'url' => ['get-dictionaries'],
                        'isNoRenderRelationLink' => true,
                        'widgetOptions' => [
                            'pluginOptions' => [
                                'ajax' => [
                                    'data' => new JsExpression(<<<JS
function(params) {
var currentType = $(this).parents('.multiple-input-list__item:first').find('.list-cell__type select').val();
return {
    name: params.term,
    type: currentType,
    page: params.page
};
}
JS
                                    ),
                                ],
                            ],
                        ],
//                        'data' => [],
//                        'options' => [
//                            'initValueText' => $dictionaryOptions,
//                            'options' => [
//                                'placeholder' => 'Dictionary'
//                            ],
//                            'pluginOptions' => [
//                                'allowClear' => true,
//                                'ajax' => [
//                                    'url' => $getDictionariesUrl,
//                                    'dataType' => 'json',
//                                    'data' => new JsExpression(<<<JS
//function(params) {
//    var currentType = $(this).parents('.multiple-input-list__item:first').find('.list-cell__type select').val();
//    return {
//        name: params.term,
//        type: currentType
//    };
//}
//JS
//                                    )
//                                ],
//                            ],
//                        ],
//                                        'enableError' => true,
                    ],
                ],
//                'plugins' => \yii::$app->getModule('import')->getSettingsSetsCrudFieldsPlugins(),
            ],
            'date' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public static function getAttributesValuesTypesList() {
        return \yii::$app->getModule('import')->getAttributesValuesTypesList();
    }

    public function delete()
    {
        foreach ($this->logs as $importLog) {
            $importLog->delete();
        }

        return parent::delete(); // TODO: Change the autogenerated stub
    }

    public function rules()
    {
        $rules = $this->getBehavior('fields')->rules();

        return ArrayHelper::merge([
            [['type'], 'validateValuesFields'],
        ], $rules);
    }

    public function validateValuesFields() {
        $variants = [
            'column_nbr',
            'value_option',
            'value_string'
        ];
        $notEmptyCount = 0;
        foreach ($variants as $variant) {
            if ($variant === 'column_nbr' && $this->$variant === '0' || !empty($this->$variant)) {
                $notEmptyCount++;
            }
        }

        if ($notEmptyCount != 1) {
            $this->addError('type', 'Enter one of fields: column nbr, text value or dictionary option for attribute value');
        }
    }
}
