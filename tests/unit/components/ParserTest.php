<?php
/**
 * User: execut
 * Date: 19.07.16
 * Time: 12:55
 */

namespace execut\import\components;


use execut\import\components\parser\Attribute;
use execut\import\components\parser\exception\MoreThanOne;
use execut\import\components\parser\exception\Validate;
use execut\import\components\parser\ModelsFinder;
use execut\import\components\parser\Result;
use execut\import\components\parser\Stack;
use execut\import\tests\TestCase;
use yii\db\ActiveRecord;

class ParserTest extends TestCase
{
    public function testSetAttributesWithConfigsArray() {
        $parser = new Parser([
            'attributes' => [
                'name' => []
            ],
        ]);
        $attributes = $parser->attributes;
        $this->assertArrayHasKey('name', $attributes);
        $attribute = $attributes['name'];
        $this->assertInstanceOf(Attribute::className(), $attribute);
        $this->assertEquals('name', $attribute->key);
    }

    public function testSetAttributesWithObjects() {
        $attribute = new Attribute();
        $parser = new Parser([
            'attributes' => [
                'name' => $attribute
            ],
        ]);
        $attributes = $parser->attributes;
        $this->assertArrayHasKey('name', $attributes);
        $this->assertEquals($attribute, $attributes['name']);
    }


    public function testParse() {
        $modelsFinder = $this->getMockBuilder(ModelsFinder::class)
            ->setMethods(['findModel'])
            ->getMock();
        $modelsFinder->method('findModel')->willReturn(new Result());
        $attribute = new Attribute([
            'column' => 0,
        ]);
        $parser = new Parser([
            'modelsFinder' => $modelsFinder,
            'data' => [
                [
                    'value',
                ],
            ],
            'attributes' => [
                'name' => $attribute
            ],
        ]);

        $result = $parser->parse();
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testSetStack() {
        $stack = new Stack();
        $parser = new Parser([
            'modelsFinder' => new ModelsFinder(),
            'stack' => $stack,
        ]);
        $this->assertEquals($stack, $parser->modelsFinder->stack);
    }

    public function testValidateAttribute() {
        $q = ParserTestModel::find();
        $parser = new Parser([
            'modelsFinder' => [
                'query' => $q,
                'isCreateAlways' => true,
            ],
            'isValidate' => true,
            'row' => [
                'wrongValue',
            ],
            'attributes' => [
                'validatedAttribute' => [
                    'column' => 0,
                ],
            ],
        ]);
        $this->setExpectedException(Validate::class);
        $parser->parse();
    }
}

class ParserTestModel extends ActiveRecord {
    public $modelClass;
    public static function find() {
        $result = new self;
        $result->modelClass = __CLASS__;

        return $result;
    }

    public function rules() {
        return [
            [['validatedAttribute'], 'number', 'skipOnEmpty' => true]
        ];
    }

    public $id = 1;
    public $attributes = [];
    public $attributesScopesIsCalled = false;
    public $errors;
    public $count = 1;
    public $orderBy = null;
    public $isNewRecord = true;
    public function byAttributesScopes($attributes) {
        if (isset($attributes['moreThenOneAttribute'])) {
            $this->count = 2;
        }

        $this->attributesScopesIsCalled = true;
        $this->attributes = $attributes;

        return $this;
    }

    public function count() {
        return $this->count;
    }

    public function one() {
        return $this;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($attributeNames !== null && array_search('validatedAttribute', $attributeNames) !== false) {
            return false;
        }

        return true;
    }
}