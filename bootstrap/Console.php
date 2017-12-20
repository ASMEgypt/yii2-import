<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 7/31/17
 * Time: 3:28 PM
 */

namespace execut\import\bootstrap;
use yii\helpers\ArrayHelper;
use yii\mutex\FileMutex;

class Console extends Common
{
    public function getDefaultDepends()
    {
        return ArrayHelper::merge(parent::getDefaultDepends(), [
            'components' => [
                'mutex' => [
                    'class' => FileMutex::class,
                ],
            ],
        ]);
    }

    public function bootstrap($app)
    {
        parent::bootstrap($app);
        if (empty($app->controllerMap['import'])) {
            $app->controllerMap['import'] = [
                'class' => \execut\import\controllers\ConsoleController::class,
            ];
        }
    }
}