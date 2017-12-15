<?php
/**
 * User: execut
 * Date: 26.07.16
 * Time: 15:28
 */

namespace execut\import\components\parser\exception;


class NotFoundRecord extends Exception
{
    public $attributes = null;
    public $modelClass = null;
    public function getLogMessage() {
        $label = $this->getModelLabel();
        $attributes = [];
        foreach ($this->attributes as $key => $attribute) {
            $attributes[] = $key . '=' . $attribute;
        }

        return $label . ' with attributes ' . implode(', ', $attributes) . ' not found';
    }

    public function getLogCategory() {
        return 'import.recordNotFound.' . str_replace(' ', '', $this->getModelLabel());
    }

    /**
     * @return mixed
     */
    protected function getModelLabel()
    {
        $class = $this->modelClass;
        return $class;
    }
}