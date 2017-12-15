<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 7/31/17
 * Time: 3:28 PM
 */

namespace execut\import\bootstrap;
use execut\actions\Bootstrap;
use execut\crud\navigation\Configurator;
use execut\import\models\File;
use execut\import\models\Setting;
use execut\import\Module;
use execut\navigation\Component;
use execut\yii\Bootstrap as BaseBootstrap;
use yii\i18n\PhpMessageSource;

class Backend extends BaseBootstrap
{
    protected $_defaultDepends = [
        'modules' => [
            'import' => [
                'class' => Module::class,
            ],
        ],
        'components' => [
            'navigation' => [
                'class' => Component::class,
            ],
        ],
        'bootstrap' => [
            'actions' => [
                'class' => Bootstrap::class,
            ],
        ],
    ];

    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);
        $this->bootstrapNavigation($app);
        $this->initI18n();
    }

    public function initI18n() {
        \yii::$app->i18n->translations['execut/import'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/execut/yii2-import/messages',
            'fileMap' => [
                'execut/import' => 'import.php',
            ],
        ];
    }

    /**
     * @param $app
     */
    protected function bootstrapNavigation($app)
    {
        if (!$app->getModule('import')->isHasAccess()) {
            return;
        }

        /**
         * @var Component $navigation
         */
        $navigation = $app->navigation;
        $navigation->addConfigurator([
            'class' => Configurator::class,
            'module' => 'import',
            'moduleName' => 'Import',
            'modelName' => File::MODEL_NAME,
            'controller' => 'files',
        ]);

        $navigation->addConfigurator([
            'class' => Configurator::class,
            'module' => 'import',
            'moduleName' => 'Import',
            'modelName' => Setting::MODEL_NAME,
            'controller' => 'settings',
        ]);
    }
}