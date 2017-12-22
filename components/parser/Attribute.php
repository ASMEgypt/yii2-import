<?php
/**
 * User: execut
 * Date: 27.07.16
 * Time: 9:50
 */

namespace execut\import\components\parser;


use execut\import\components\parser\exception\ColumnIsEmpty;
use yii\base\Component;

class Attribute extends Component
{
    public $row = null;
    public $key = null;
    public $column = null;
    public $numberDelimiter = null;
    public $isRequired = true;
    public $isForSearchQuery = true;
    protected $value = null;
    public function getValue() {
        if ($this->value !== null) {
            return $this->value;
        }

        if (!empty($this->row[$this->column])) {
            $value = trim($this->row[$this->column]);
            if ($this->numberDelimiter !== null) {
                $value = str_replace($this->numberDelimiter, '.', $value);
            }

            if ($value) {
                return $value;
            }
        }
    }

    public function isValid() {
        return empty($this->getValue()) && !$this->isRequired || !empty($this->getValue()) && $this->isRequired;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->getValue();
    }
}