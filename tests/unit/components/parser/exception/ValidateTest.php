<?php
/**
 * User: execut
 * Date: 27.07.16
 * Time: 17:35
 */

namespace execut\import\components\parser\exception;


use execut\import\tests\TestCase;

class ValidateTest extends TestCase
{
    public function testGetLogMessage() {
        $exception = new Validate();
        $exception->errors = [
            'test' => [
                'Error message',
                'Other error message',
            ],
        ];

        $exception->columnNbr = 0;
        $exception->attribute = 'test';
        $this->assertEquals('Column 0 attribute test validation errors: Error message, Other error message', $exception->getLogMessage());
        $this->assertEquals('import.validate.0.test', $exception->getLogCategory());
    }
}