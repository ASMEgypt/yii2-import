<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/29/16
 * Time: 2:46 PM
 */

namespace execut\import\components\source;


use execut\TestCase;

class AdapterTest extends TestCase
{
    public function actionTestCreateFile() {
        $adapter = $this->getMockForAbstractClass(Adapter::class);
        $file = $adapter->createFile('test');
        $this->assertEquals('test', $file->fileName);
        $this->assertTrue(preg_match('/\/tmp\/\d+_test/', $file->fileName) === 1);
    }
}