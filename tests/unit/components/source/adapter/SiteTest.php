<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/29/16
 * Time: 12:55 PM
 */

namespace execut\import\components\source\adapter;


use execut\import\tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class SiteTest extends TestCase
{
    public function testGetFilesWithoutAuth()
    {
        $client = $this->getMockBuilder(Client::class)->setMethods(['request'])->getMock();

        $response = new Response(200, [], 'test');
        $client->expects($this->at(0))->method('request')->with('get', '/folder/filename.zip', [])->willReturn($response);
        $site = new Site([
            'site' => 'http://www.site.com/',
            'fileUrl' => '/folder/filename.zip',
            'client' => $client,
        ]);
        $files = $site->getFiles();
        $this->assertCount(1, $files);
    }

    public function testGetFiles()
    {
        $client = $this->getMockBuilder(Client::class)->setMethods(['request'])->getMock();
        $client->expects($this->at(0))->method('request')->with('post', '/auth', [
            'headers' => Site::STD_HEADERS,
            'form_params' => [
                'login' => 'loginValue',
                'password' => 'passwordValue',
                'otherField' => 'test',
            ],
        ])->willReturn(null);
        $response = new Response(200, [], 'test');
        $client->expects($this->at(1))->method('request')->with('get', '/folder/filename.zip', [])->willReturn($response);
        $site = new Site([
            'site' => 'http://www.site.com/',
            'authUrl' => '/auth',
            'method' => 'post',
            'login' => 'loginValue',
            'loginField' => 'login',
            'password' => 'passwordValue',
            'passwordField' => 'password',
            'otherFields' => [
                'otherField' => 'test',
            ],
            'fileUrl' => '/folder/filename.zip',
            'client' => $client,
        ]);
        $files = $site->getFiles();
        $this->assertCount(1, $files);
        $file = $files[0];
        $this->assertTrue($file->fileName === 'filename.zip');
        $this->assertNotEmpty($file->content);
    }
}