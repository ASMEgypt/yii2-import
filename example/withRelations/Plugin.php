<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/19/17
 * Time: 5:51 PM
 */

namespace execut\import\example\withRelations;


use execut\import\example\models\Article;
use execut\import\example\models\Brand;
use execut\import\example\models\Product;
use execut\import\models\File;
use execut\navigation\Component;

class Plugin implements \execut\import\Plugin
{
    public function getDictionaries() {
        return [
            'example_product_id' => Product::find(),
            'example_article_id' => Article::find(),
            'example_brand_id' => Brand::find(),
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
                'example_brand_id' => [
                    'isNoUpdate' => true,
                    'query' => $queries['example_brand_id'],
                    'attributes' => [
                        'name' => [
                            'isFind' => true,
                        ],
                    ],
                ],
                'example_article_id' => [
                    'isImport' => true,
                    'isNoUpdate' => true,
                    'query' => $queries['example_article_id'],
                    'attributes' => [
                        'article' => [
                            'key' => 'article',
                            'isFind' => true,
                        ],
                        'example_brand_id' => [
                            'isFind' => true,
                        ],
                    ],
                ],
                'example_product_id' => [
                    'isImport' => true,
                    'isDelete' => true,
                    'query' => $queries['example_product_id'],
                    'attributes' => [
                        'name' => [
                            'key' => 'name',
                            'isFind' => false,
                        ],
                        'price' => [
                            'key' => 'price',
                            'isFind' => false,
                        ],
                        'price' => [
                            'key' => 'price',
                            'isFind' => false,
                        ],
                        'example_article_id' => [
                            'isFind' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список искомых записей на одну строчку
     *
     * @return array
     */
    public function getAttributesSetsTypesList() {
        return [
            'example_product_id' => 'Product',
        ];
    }

    /**
     * Список возможных значений настроек
     *
     * @return array
     */
    public function getAttributesValuesTypesList() {
        return [
            'example_product_id.price' => 'Product price',
            'example_product_id.name' => 'Product name',
            'example_article_id.article' => 'Article',
            'example_brand_id.name' => 'Brand',
        ];
    }

    /**
     * Список обязательных значений настроек
     *
     * @return array
     */
    public function getRequiredAttributesByTypes() {
        return [
            'example_product_id' => [
                'example_product_id.name',
                'example_product_id.price',
                'example_article_id.article',
                'example_brand_id.name',
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

    /**
     * Удаляет привязанные к файлу импорта записи
     */
    public function getOldIdsByFile(File $importFile)
    {
        return Product::find()->select('id')->column();
    }
}