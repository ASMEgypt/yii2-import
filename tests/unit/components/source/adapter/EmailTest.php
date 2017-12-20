<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 2:11 PM
 */

namespace execut\import\components\source\adapter;


use execut\import\components\source\adapter\email\Mail;
use execut\import\components\source\adapter\email\Receiver;
use execut\actions\action\adapter\File;
use execut\import\tests\TestCase;

class EmailTest extends TestCase
{
    public function testGetFiles() {
        $file = new File();
        $emails = [
            new Mail([
                'subject' => 'Test',
                'attachments' => [
                    $file,
                ]
            ]),
        ];

        $emailAdapter = $this->getMockBuilder(Receiver::class)->setMethods(['_getMails'])->getMock();
        $emailAdapter->method('_getMails')->willReturn($emails);
        $adapter = new Email([
            'receiver' => $emailAdapter,
        ]);

        $this->assertEquals([$file], $adapter->getFiles());
    }
}