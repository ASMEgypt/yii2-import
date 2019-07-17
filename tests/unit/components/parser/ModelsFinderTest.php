<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 10/4/16
 * Time: 4:00 PM
 */

namespace execut\import\tests\unit\components\parser;


use execut\import\components\parser\Attribute;
use execut\import\components\parser\exception\MoreThanOne;
use execut\import\components\parser\exception\NotFoundRecord;
use execut\import\components\parser\ModelsFinder;
use execut\import\components\parser\Result;
use execut\import\components\parser\Stack;
use execut\import\Query;
use execut\import\tests\TestCase;
use yii\db\ActiveRecord;

class ModelsFinderTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();
        ModelsFinder::$cache = [];
    }

    protected function tearDown(): void {
        parent::tearDown();
        ModelsFinder::$cache = [];
    }

    public function testSimple() {
        $finder = new ModelsFinder([
            'query' => ParserTestModel::find(),
            'isCreateAlways' => true,
            'asArray' => true,
            'attributes' => [
                new Attribute([
                    'key' => 'name',
                    'value' => 'test1',
                ]),
            ],
        ]);

        $models = $finder->findModel();
        $this->assertInstanceOf(Result::class, $models);
        $model = $models->getModel();

        $this->assertEquals(['name' => 'test1'], $model->attributes);
        $this->assertFalse($model->attributesScopesIsCalled);
    }

    public function testCreateNotExisted() {
        $finder = new ModelsFinder([
            'query' => ParserTestModel::find(),
            'attributes' => [
                new Attribute([
                    'key' => 'test1',
                    'value' => 'test',
                ]),
            ],
            'asArray' => true,
            'isCreateNotExisted' => true,
        ]);
        $result = $finder->findModel();
        $model = $result->getModel();
        $this->assertTrue($model->attributesScopesIsCalled);
    }

    public function testRelatedMoreWhanOneException() {
        $q = ParserTestModel::find();
        $q->count = 2;
        $finder = new ModelsFinder([
            'query' => $q,
            'attributes' => [
                new Attribute([
                    'key' => 'test1',
                    'value' => 'test',
                ]),
            ],
        ]);
        $this->expectException(MoreThanOne::class);
        $finder->findModel();
    }

    public function testNotFoundException() {
        $q = ParserTestModel::find();
        $q->count = 0;
        $finder = new ModelsFinder([
            'query' => $q,
            'attributes' => [
                new Attribute([
                    'key' => 'attribute',
                    'value' => 'findedValue',
                ]),
            ],
        ]);
        $this->expectException(NotFoundRecord::class);
        $finder->findModel();
    }

    protected $prepareQueryIsCalled = false;
    public function testPrepareQuery() {
        $q = ParserTestModel::find();
        $q->count = 1;
        $finder = new ModelsFinder([
            'stack' => new Stack(),
            'query' => $q,
            'prepareQuery' => function ($query, $result, $stack) {
                $this->assertInstanceOf(Result::class, $result);
                $this->assertInstanceOf(Stack::class, $stack);
                $this->prepareQueryIsCalled = true;
                return $query;
            },
            'attributes' => [
                new Attribute([
                    'key' => 'test1',
                    'value' => 'test',
                ]),
            ],
        ]);
        $finder->findModel();
        $this->assertTrue($this->prepareQueryIsCalled);
    }
}

class ParserTestModel extends ActiveRecord implements Query {
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
    public function byImportAttributes($attributes) {
        if (isset($attributes['moreThenOneAttribute'])) {
            $this->count = 2;
        }

        $this->attributesScopesIsCalled = true;
        $this->attributes = $attributes;

        return $this;
    }

    public function all() {
        if ($this->count == 2) {
            return [$this, $this];
        } else if ($this->count == 0) {
            return [];
        } else {
            return [$this];
        }
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (array_search('validatedAttribute', $attributeNames) !== false) {
            return false;
        }

        return true;
    }
}