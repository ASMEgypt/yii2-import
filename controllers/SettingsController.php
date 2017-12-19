<?php
/**
 * Date: 12.07.16
 * Time: 14:47
 */

namespace execut\import\controllers;


use execut\actions\Action;
use execut\actions\action\adapter\GridView;
use execut\crud\params\Crud;
use execut\import\components\WebController;
use execut\import\models\Dictionary;
use execut\import\models\Setting;
use execut\navigation\behaviors\Navigation;
use yii\helpers\ArrayHelper;

class SettingsController extends WebController
{
    public function actions()
    {
        $crud = new Crud([
            'modelClass' => Setting::class,
            'module' => 'import',
            'moduleName' => 'Import',
            'modelName' => Setting::MODEL_NAME,
        ]);

        return ArrayHelper::merge($crud->actions(), [
            'get-dictionaries' => [
                'class' => Action::className(),
                'adapter' => [
                    'class' => GridView::className(),
                    'model' => Dictionary::className(),
                    'view' => null,
                ],
            ],
        ], parent::actions()); // TODO: Change the autogenerated stub
    }
}