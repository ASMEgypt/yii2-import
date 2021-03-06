<?php
namespace execut\import\components\parser\exception;


class ColumnIsEmpty extends Exception
{
    public $attribute = null;
    public function getLogMessage() {
        return 'Column ' . ($this->columnNbr + 1) . ' for attribute ' . $this->attribute . ' is empty';
    }

    public function getLogCategory()
    {
        return 'import.columnNotFound.' . $this->attribute . '.' . ($this->columnNbr + 1);
    }
}