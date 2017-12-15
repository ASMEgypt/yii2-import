<?php
/**
 * User: execut
 * Date: 25.07.16
 * Time: 17:58
 */

namespace execut\import\components;


use yii\base\Component;

class SettingsValueExtractor extends Component
{
    public $model = null;
    public function extract() {
        $result = [];

        $model = $this->model;
        $type = $model->type;
        $parts = explode('.', $type);
        if ($model->value_option) {
            $result['id'] = $model->value_option;
        } else if ($model->column_nbr !== null && $model->column_nbr !== '') {
            $columnResult = [
                'column' => $model->column_nbr - 1
            ];

            if (!empty($model->number_delimiter)) {
                $columnResult['numberDelimiter'] = $model->number_delimiter;
            }

            $result[$parts[1]] = $columnResult;
        } else if ($model->value_string) {
            $result[$parts[1]] = [
                'value' => $model->value_string,
            ];
        }

        return [
            $parts[0] => [
                'attributes' => $result
            ],
        ];
    }
}