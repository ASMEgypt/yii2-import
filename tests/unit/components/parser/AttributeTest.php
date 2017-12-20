<?php
/**
 * User: execut
 * Date: 27.07.16
 * Time: 9:46
 */

namespace execut\import\components\parser;


use execut\import\components\parser\exception\ColumnIsEmpty;
use execut\import\tests\TestCase;

class AttributeTest extends TestCase
{
    public function testGetEmptyValue() {
        $attribute = new Attribute([
            'row' => ' ',
            'column' => 0,
            'isRequired' => false,
        ]);

        $this->assertNull($attribute->getValue());
    }

    public function testGetValue() {
        $attribute = new Attribute();
        $attribute->row = ['test'];
        $attribute->column = 0;
        $this->assertEquals('test', $attribute->getValue());
    }

    public function testGetRequiredValue() {
        $attribute = new Attribute([
            'key' => 'test',
            'row' => [],
            'column' => 0,
        ]);
        $this->setExpectedException(ColumnIsEmpty::class);
        $attribute->getValue();
    }

    public function testGetNumberValue() {
        $attribute = new Attribute([
            'key' => 'test',
            'row' => ['0,11'],
            'column' => 0,
            'numberDelimiter' => ','
        ]);
        $this->assertEquals('0.11', $attribute->getValue());
    }
}