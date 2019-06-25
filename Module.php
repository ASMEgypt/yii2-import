<?php
/**
 * User: execut
 * Date: 13.07.16
 * Time: 14:27
 */

namespace execut\import;


use execut\dependencies\PluginBehavior;
use execut\import\models\File;
use execut\navigation\Component;
use kartik\base\TranslationTrait;

class Module extends \yii\base\Module implements Plugin
{
    public $controllerNamespace = 'execut\import\controllers';
    public function behaviors()
    {
        return [
            [
                'class' => PluginBehavior::class,
                'pluginInterface' => Plugin::class,
            ],
        ];
    }

    public function getDictionaries() {
        return $this->getPluginsResults(__FUNCTION__);
    }

    public function getParsersByTypesSettings() {
        return $this->getPluginsResults(__FUNCTION__);
    }

    public function getOldIdsByFile(File $importFile) {
        return $this->getPluginsResults(__FUNCTION__, false, func_get_args());
    }

    public function getAllowedRoles() {
        $results = $this->getPluginsResults(__FUNCTION__);
        if ($results === null) {
            return [];
        }

        return $results;
    }

    public function isHasAccess() {
        $roles = $this->getAllowedRoles();
        if ($roles === []) {
            return true;
        }

        $user = \yii::$app->user;
        foreach ($roles as $role) {
            if ($user->can($role)) {
                return true;
            }
        }
    }

    public function getFilesCrudFieldsPlugins() {
        $results = $this->getPluginsResults(__FUNCTION__);
        if ($results === null) {
            return [];
        }

        return $results;
    }

    public function getAttributesSetsTypesList() {
        $results = $this->getPluginsResults(__FUNCTION__);
        if ($results === null) {
            return [];
        }

        return $results;
    }

    public function getAttributesValuesTypesList() {
        $results = $this->getPluginsResults(__FUNCTION__);
        if ($results === null) {
            return [];
        }

        return $results;
    }

    public function getSettingsCrudFieldsPlugins() {
        $results = $this->getPluginsResults(__FUNCTION__);
        if ($results === null) {
            return [];
        }

        return $results;
    }

    public function getSettingsSheetsCrudFieldsPlugins() {
        $results = $this->getPluginsResults(__FUNCTION__);
        if ($results === null) {
            return [];
        }

        return $results;
    }

    public function getRequiredAttributesByTypes() {
        $results = $this->getPluginsResults(__FUNCTION__);
        if ($results === null) {
            return [];
        }

        return $results;
    }

    public function bootstrapNavigation(Component $navigation) {
        $this->getPluginsResults(__FUNCTION__, false, [$navigation]);
    }
}