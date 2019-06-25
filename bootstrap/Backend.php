<?php
namespace execut\import\bootstrap;
use execut\actions\Bootstrap;
use execut\crud\navigation\Configurator;
use execut\import\models\File;
use execut\import\models\Setting;
use execut\import\Module;
use execut\navigation\Component;
use execut\navigation\configurator\HomePage;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;

class Backend extends Common
{
    public function getDefaultDepends()
    {
        return ArrayHelper::merge(parent::getDefaultDepends(), [
            'components' => [
                'navigation' => [
                    'class' => Component::class,
                ],
            ],
            'bootstrap' => [
                'navigation' => [
                    'class' => \execut\navigation\Bootstrap::class,
                ],
                'actions' => [
                    'class' => Bootstrap::class,
                ],
                'crud' => [
                    'class' => \execut\crud\Bootstrap::class,
                ],
            ],
        ]);
    }

    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);
        $this->bootstrapNavigation($app);
    }

    /**
     * @param $app
     */
    protected function bootstrapNavigation($app)
    {
        $importModule = $app->getModule('import');
        if (!$importModule->isHasAccess()) {
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

        $importModule->bootstrapNavigation($navigation);
    }
}