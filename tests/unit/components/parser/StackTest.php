<?php
/**
 * User: execut
 * Date: 26.07.16
 * Time: 15:45
 */

namespace execut\import\tests\unit\components\parser;


use execut\import\components\Parser;
use execut\import\components\parser\Result;
use execut\import\components\parser\Stack;
use execut\import\tests\TestCase;
use yii\db\ActiveRecord;

class StackTest extends TestCase
{
    public function testSetRow() {
        $parser = $this->getMockBuilder(Parser::className())->setMethods(['parse'])->getMock();
        $stack = new Stack([
            'parsers' => [
                $parser,
            ]
        ]);

        $stack->row = [
            'test'
        ];
        $this->assertEquals([
            'test'
        ], $parser->row);
    }

    public function testParse() {
        $testModel1 = new StackTestModel();
        $parser1 = $this->getMockBuilder(Parser::className())->setMethods(['parse'])->getMock();
        $parser1->method('parse')->willReturn(new Result([
            'models' => [$testModel1]
        ]));

        $testModel2 = new StackTestModel();
        $parser2 = $this->getMockBuilder(Parser::className())->setMethods(['parse'])->getMock();
        $parser2->method('parse')->willReturn(new Result([
            'models' => [$testModel2]
        ]));

        $stack = new Stack([
            'parsers' => [
                'parser1' => $parser1,
                'parser2' => $parser2,
            ],
            'relations' => [
                'parser1' => [
                    'relation_id' => 'parser2',
                ],
            ],
        ]);

        $result = $stack->parse();
        $result = array_values($result);
        $this->assertCount(2, $result);
        $this->assertNull($result[0]->relation_id);
        $this->assertFalse($result[0]->runWithValidation);
        $this->assertEquals(2, $result[1]->relation_id);

        $this->assertEquals($parser1->stack, $stack);
    }
}

class StackTestModel extends ActiveRecord {
    public $id = 2;
    public $relation_id;
    public $runWithValidation = null;
    public $isNewRecord = true;
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->runWithValidation = $runValidation;
//        $this->relation_id = $this->id;

        return true;
    }
}