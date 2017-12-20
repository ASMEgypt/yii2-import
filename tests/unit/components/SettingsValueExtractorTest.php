<?php
/**
 * User: execut
 * Date: 25.07.16
 * Time: 17:54
 */

namespace execut\import\components;


use execut\import\models\SettingsValue;
use execut\import\tests\TestCase;

class SettingsValueExtractorTest extends TestCase
{
    public function testExtractWithColumn() {
        $settingsValue = new ImportSettingsValuesTest();
        $settingsValue->type = 'test_id.attributeKey';
        $settingsValue->number_delimiter = '.';
        $settingsValue->column_nbr = 1;

        $extractor = new SettingsValueExtractor([
            'model' => $settingsValue,
        ]);
        $this->assertEquals([
            'test_id' => [
                'attributes' => [
                    'attributeKey' => [
                        'column' => 0,
                        'numberDelimiter' => '.',
                    ],
                ],
            ]
        ], $extractor->extract());
    }

    public function testExtractWithDictionary() {
        $settingsValue = new ImportSettingsValuesTest();
        $settingsValue->type = 'test_id.attributeKey';
        $settingsValue->value_option = 1;
        $extractor = new SettingsValueExtractor([
            'model' => $settingsValue,
        ]);
        $this->assertEquals([
            'test_id' => [
                'attributes' => [
                    'id' => 1,
                ],
            ],
        ], $extractor->extract());
    }

    public function testExtractWithAttribute() {
        $settingsValue = new ImportSettingsValuesTest();
        $settingsValue->type = 'test_id.attributeKey';
        $settingsValue->value_string = 'attributeValue';
        $extractor = new SettingsValueExtractor([
            'model' => $settingsValue,
        ]);
        $this->assertEquals([
            'test_id' => [
                'attributes' => [
                    'attributeKey' => [
                        'value' => 'attributeValue',
                    ],
                ],
            ],
        ], $extractor->extract());
    }
}

class ImportSettingsValuesTest extends SettingsValue {
    public $column_nbr = '';
    public function attributes()
    {
        return [
            'column_nbr',
            'type',
            'value_option',
            'value_string',
            'number_delimiter',
        ];
    }
}