<?php
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