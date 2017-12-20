<?php
/**
 * Date: 12.07.16
 * Time: 14:47
 */

namespace execut\import\controllers;

use execut\actions\action\adapter\EditWithRelations;
use execut\crud\params\Crud;

use execut\import\models\File;
use execut\actions\Action;
use execut\actions\action\adapter\File as FileAdapter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class FilesController extends Controller
{
    public function actions()
    {
        $crud = new Crud([
            'modelClass' => File::class,
            'module' => 'import',
            'moduleName' => 'Import',
            'modelName' => File::MODEL_NAME,
            'relations' => [
                'logs' => [],
                'logsGrouped' => [],
            ],
        ]);
        ini_set('max_execution_time', 0);
        return ArrayHelper::merge($crud->actions(), [
            'update' => [
                'adapter' => [
                    'class' => EditWithRelations::class,
                    'editAdapterConfig' => [
                        'filesAttributes' => [
                            'content' => 'contentFile'
                        ],
                    ],
//                    'relationAdapterConfig' => [
//                        'importLogsFormGroupedByCategory' => [
//                            'view' => [
//                                'refreshAttributes' => [
//                                    'id',
//                                ],
//                                'isAllowedAdding' => false,
//                            ],
//                        ],
//                        'importLogsForm' => [
//                            'view' => [
//                                'refreshAttributes' => [
//                                    'id',
//                                ],
//                                'isAllowedAdding' => false,
//                            ],
//                        ],
//                    ],
                ],
            ],
            'download' => [
                'class' => Action::className(),
                'adapter' => [
                    'class' => FileAdapter::className(),
                    'modelClass' => File::class,
                    'extensionIsRequired' => false,
                    'dataAttribute' => 'content',
                ],
            ],
        ]);
    }
}