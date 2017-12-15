<?php
/**
 * User: execut
 * Date: 26.07.16
 * Time: 16:03
 */

namespace execut\import\components\parser\exception;


class Validate extends Exception
{
    public $errors;
    public $attribute = null;
    public function getLogMessage() {
        $errors = [];
        foreach ($this->errors as $value) {
            $errors[] = implode(', ', $value);
        }

        return  \Yii::t('app', 'Column {column} attribute {attribute} validation errors: {errors}', [
            'attribute' => $this->attribute,
            'column' => $this->columnNbr,
            'errors' => implode(', ', $errors),
        ]);
    }
    public function getLogCategory() {
        return 'import.validate.' . $this->columnNbr . '.' . $this->attribute;
    }
}