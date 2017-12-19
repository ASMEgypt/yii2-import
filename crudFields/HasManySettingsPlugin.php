<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/18/17
 * Time: 6:00 PM
 */

namespace execut\import\crudFields;


use execut\crudFields\Plugin;
use execut\import\models\Setting;

class HasManySettingsPlugin extends Plugin
{
    public $attribute = null;
    public $relationName = null;
    public $relationModelClass = null;
    public function getRelations()
    {
        return [
            'importSettings' => [
                'class' => Setting::class,
                'link' => [
                    'id' => 'import_setting_id',
                ],
                'via' => $this->relationName,
                'name' => 'importSettings',
                'multiple' => true,
            ],
            $this->relationName => [
                'class' => $this->relationModelClass,
                'link' => [
                    $this->attribute => 'id',
                ],
                'name' => $this->relationName,
                'multiple' => true,
            ],
        ];
    }
}