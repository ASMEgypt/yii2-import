<?php
/**
 * User: execut
 * Date: 28.07.16
 * Time: 9:28
 */

namespace execut\import\components\parser\exception;


use execut\TestCase;

class ColumnIsEmptyTest extends TestCase
{
    public function testGetLogMessage() {
        $exception = new ColumnIsEmpty();
        $exception->columnNbr = 2;
        $exception->attribute = 'test';
        $this->assertEquals('Column 3 for attribute test is empty', $exception->getLogMessage());
    }

    public function testGetLogCategory() {
        $exception = new ColumnIsEmpty();
        $exception->columnNbr = 2;
        $exception->attribute = 'test';
        $this->assertEquals('import.columnNotFound.test.3', $exception->getLogCategory());
    }
}