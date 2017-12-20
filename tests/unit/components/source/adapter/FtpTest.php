<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/28/16
 * Time: 4:46 PM
 */

namespace execut\import\components\source\adapter;


use execut\import\components\source\adapter\Ftp;
use execut\import\tests\TestCase;
use yii2mod\ftp\FtpClient;

class FtpTest extends TestCase
{
    public function testGetFiles() {
        $client = $this->getMockBuilder(FtpClient::class)->setMethods([
            'connect', //($this->host, $this->ssl, $this->port, $this->timeout
            'login',
            'pasv',
            'get',
            'raw',
        ])->getMock();
        $client->expects($this->once())->method('connect')->with('host', false, 21, 10)->willReturn(null);
        $client->expects($this->once())->method('login')->with('login', 'password')->willReturn(null);
        $client->expects($this->once())->method('pasv')->with(true)->willReturn(null);
        $client->expects($this->once())->method('raw')->with('OPTS UTF8 ON')->willReturn(null);
        $client->expects($this->once())->method('get')->willReturn(null);

        $ftp = new Ftp([
            'client' => $client,
            'host' => 'host',
            'ssl' => false,
            'port' => 21,
            'timeout' => 10,
            'login' => 'login',
            'password' => 'password',
            'dir' => '/Срок1_1',
            'fileName' => '_All.zip',
        ]);
        $files = $ftp->getFiles();
        $this->assertCount(1, $files);
        $file = $files[0];
        $this->assertEquals('_All.zip', $file->fileName);
        $this->assertTrue(strpos($file->filePath, '_All.zip') !== false);
    }
}