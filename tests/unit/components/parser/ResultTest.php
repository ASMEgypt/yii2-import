<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 3/14/17
 * Time: 11:49 AM
 */

namespace execut\import\components\parser;


use execut\TestCase;

class ResultTest extends TestCase
{
    public function testAddModel() {
        $result = new Result();
        $model = new \stdClass();
        $result->addModel($model);
        $this->assertCount(1, $result->models);
    }

    public function testSetModel() {
        $model = new \stdClass();
        $models = [
            $model
        ];
        $result = new Result([
            'models' => $models,
        ]);
        $this->assertCount(1, $result->models);
    }

    public function testGetModel() {
        $model = new \stdClass();
        $models = [
            $model
        ];
        $result = new Result([
            'models' => $models,
        ]);
        $this->assertEquals($model, $result->getModel());

        $this->assertEquals(false, $result->getModel(1));
    }
}