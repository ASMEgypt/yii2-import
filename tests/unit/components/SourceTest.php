<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/26/16
 * Time: 11:37 AM
 */

namespace execut\import\components;


use execut\import\components\source\Adapter;
use execut\import\tests\TestCase;

class SourceTest extends TestCase
{
    public function testGetFiles() {
        $adapter = $this->getMockForAbstractClass(Adapter::class);
        $adapter->method('getFiles')->willReturn([]);
        $source = new Source([
            'adapter' => $adapter,
        ]);

        $this->assertEquals([], $source->getFiles());
    }
}