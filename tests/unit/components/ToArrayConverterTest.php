<?php
/**
 * User: execut
 * Date: 19.07.16
 * Time: 11:46
 */

namespace execut\import\components;

use execut\import\tests\TestCase;

class ToArrayConverterTest extends TestCase
{
    protected $file = null;
    protected function setUp(): void {
        parent::setUp();
        $this->file = tempnam('/tmp', 'test_') . '.csv';
        file_put_contents($this->file, '"test, test",test');
    }

    protected function tearDown(): void {
        parent::tearDown();
        unlink($this->file);
    }

    public function testConvertFromPath() {
        $toArrayConverter = new ToArrayConverter();
        $toArrayConverter->file = $this->file;
        $this->assertEquals([['test, test', 'test']], $toArrayConverter->convert());
    }

    public function testConvertHandle() {
        $toArrayConverter = new ToArrayConverter();
        $toArrayConverter->file = fopen($this->file, 'r');
        $this->assertEquals([['test, test', 'test']], $toArrayConverter->convert());
    }

//    public function testCache() {
//        $toArrayConverter = new ToArrayConverter();
//        $toArrayConverter->file = $this->file;
//        $cache = $this->getMockBuilder(FileCache::class)->setMethods(['get'])->getMock();
//        $cache->expects($this->once())->method('get')->will($this->returnValue([['test']]));
//        $toArrayConverter->cache = $cache;
//        $this->assertEquals([['test']], $toArrayConverter->convert());
//    }

    public function testConvertEncoding() {
        $result = \mb_convert_encoding('тест,тест', 'UTF-16', 'UTF-8');
        file_put_contents($this->file, $result);

        $toArrayConverter = new ToArrayConverter([
            'file' => $this->file,
            'encoding' => 'UTF-16'
        ]);
        $this->assertEquals([['тест', 'тест']], $toArrayConverter->convert());
    }

    public function testConvertBadEncoding() {
        $result = 'Àðòèêóë,"Íàèìåíîâàíèå"';
//        var_dump(\mb_convert_encoding(mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8'), 'UTF-8', 'cp1251'));
//        exit;
        $file = tempnam('/tmp', 'test_');
        file_put_contents($this->file, $result);

        $toArrayConverter = new ToArrayConverter([
            'file' => $this->file,
            'encoding' => 'ISO-8859-1'
        ]);

        $this->assertEquals([['Артикул', 'Наименование']], $toArrayConverter->convert());
    }

    public function testConvertWithMiltiline() {
        $result = 'test' . "\n" .
            'test';
        $file = tempnam('/tmp', 'test_');
        file_put_contents($this->file, $result);

        $toArrayConverter = new ToArrayConverter([
            'file' => $this->file,
        ]);

        $this->assertEquals([['test'], ['test']], $toArrayConverter->convert());
    }

    public function testConvertWithDelimiter() {
        $result = 'test;\'test';
        $file = tempnam('/tmp', 'test_');
        file_put_contents($this->file, $result);

        $toArrayConverter = new ToArrayConverter([
            'file' => $this->file,
            'delimiter' => ';\'',
        ]);

        $this->assertEquals([['test', 'test']], $toArrayConverter->convert());
    }

    public function testConvertWithTrimValues() {
        $result = 'test,\'test';
        $file = tempnam('/tmp', 'test_');
        file_put_contents($this->file, $result);

        $toArrayConverter = new ToArrayConverter([
            'file' => $this->file,
            'trim' => '\'',
        ]);

        $this->assertEquals([['test', 'test']], $toArrayConverter->convert());
    }

    public function testConvertRar() {
        $filePath = __DIR__ . '/test.csv.rar';
        $toArrayConverter = new ToArrayConverter([
            'file' => fopen($filePath, 'r'),
            'mimeType' => 'application/x-rar',
        ]);

        $this->assertEquals([['1']], $toArrayConverter->convert());
    }

    public function testConvertZip() {
        $filePath = __DIR__ . '/test.csv.zip';
        $toArrayConverter = new ToArrayConverter([
            'file' => fopen($filePath, 'r'),
        ]);

        $this->assertEquals([['1']], $toArrayConverter->convert());
    }

    public function testConvertXls() {
        $filePath = __DIR__ . '/test.xls';
        $toArrayConverter = new ToArrayConverter([
            'file' => fopen($filePath, 'r'),
        ]);

        $result = $toArrayConverter->convert();
        $this->assertEquals([['test1', 'test2'], [''], ['']], $result);
    }
}