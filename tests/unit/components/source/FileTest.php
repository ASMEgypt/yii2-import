<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 3:17 PM
 */

namespace execut\import\components\source;

use execut\import\tests\TestCase;

class FileTest extends TestCase
{
    protected $filePath = null;
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->filePath = tempnam('/tmp', 'test_');
        file_put_contents($this->filePath, 'test');
    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }

    public function testGetContent() {
        $attachment = new File([
            'filePath' => $this->filePath,
        ]);
        $this->assertEquals('test', $attachment->getContent());
    }

    public function testSetContent() {
        $content = 'test2';
        $attachment = new File([
            'content' => $content,
        ]);
        $this->assertEquals($content, $attachment->getContent());
        $attachment->filePath = $this->filePath;
        $attachment->content = $content;

        $this->assertEquals($content, file_get_contents($this->filePath));
    }

    public function testUnlinkFile() {
        $this->assertFileExists($this->filePath);
        $attachment = new File([
            'filePath' => $this->filePath,
        ]);
        unset($attachment);
        $this->assertFileNotExists($this->filePath);
    }
}