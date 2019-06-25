<?php
namespace execut\import\components\parser\exception;


class MoreThanOne extends Exception
{
    public $attributes = null;
    public $modelClass = null;
    public function getLogMessage() {
        $label = $this->getModelLabel();
        $attributes = [];
        foreach ($this->attributes as $key => $attribute) {
            $attributes[] = $key . '=' . $attribute;
        }

        return $label . ' with attributes ' . implode(', ', $attributes) . ' founded more whan one record';
    }

    public function getLogCategory() {
        return 'import.moreThanOne.' . str_replace(' ', '', $this->getModelLabel());
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