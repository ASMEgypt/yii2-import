<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/19/17
 * Time: 5:51 PM
 */

namespace execut\import\example\simple;


use execut\import\example\models\Product;
use execut\import\models\File;
use execut\navigation\Component;

class Plugin implements \execut\import\Plugin
{
    public function getDictionaries() {
        return [
            'example_product_id' => Product::find()
        ];
    }

    /**
     * Возвращает настройки парсеров моделей
     *
     * @return array
     */
    public function getParsersByTypesSettings() {
        $queries = $this->getDictionaries();
        return [
            'example_product_id' => [
                'parsers' => [
                    'example_product_id' => [
                        'modelsFinder' => [
                            'isUpdateAlways' => true,
                            'isCreateNotExisted' => true,
                            'query' => $queries['example_product_id'],
                        ],
                        'attributes' => [
                            'name' => [
                                'key' => 'name',
                                'isForSearchQuery' => true,
                            ],
                            'price' => [
                                'key' => 'price',
                                'isForSearchQuery' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getAttributesSetsTypesList() {
        return [
            'example_product_id' => 'Product',
        ];
    }

    public function getAttributesValuesTypesList() {
        return [
            'example_product_id.price' => 'Product price',
            'example_product_id.name' => 'Product name',
        ];
    }

    public function getRequiredAttributesByTypes() {
        return [
            'example_product_id' => [
                'example_product_id.name',
                'example_product_id.price',
            ],
        ];
    }

    /**
     * Удаляет привязанные к файлу импорта записи
     */
    public function deleteRelatedRecords(File $importFile) {
    }

    public function getAllowedRoles() {
        return [];
    }

    public function getFilesCrudFieldsPlugins() {
    }

    public function getSettingsCrudFieldsPlugins() {
    }

    public function getSettingsSheetsCrudFieldsPlugins() {
    }

    public function bootstrapNavigation(Component $navigation) {
    }
}