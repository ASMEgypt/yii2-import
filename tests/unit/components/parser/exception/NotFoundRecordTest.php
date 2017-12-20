<?php
/**
 * User: execut
 * Date: 27.07.16
 * Time: 17:35
 */

namespace execut\import\components\parser\exception;

use execut\import\tests\TestCase;
use yii\db\ActiveRecord;

class NotFoundRecordTest extends TestCase
{
    public function testGetLogMessage() {
        $exception = new NotFoundRecord();
        $exception->attributes = [
            'name' => 'value',
            'otherName' => 'value2'
        ];

        $exception->modelClass = NotFoundRecordTestModel::className();
        $this->assertEquals(NotFoundRecordTestModel::class . ' with attributes name=value, otherName=value2 not found', $exception->getLogMessage());
    }

    public function testGetLogCategory() {
        $exception = new NotFoundRecord();

        $exception->modelClass = NotFoundRecordTestModel::className();
        $this->assertEquals('import.recordNotFound.' . NotFoundRecordTestModel::class, $exception->getLogCategory());
    }
}

class NotFoundRecordTestModel extends ActiveRecord {
}