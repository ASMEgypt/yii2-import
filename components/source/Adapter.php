<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/26/16
 * Time: 11:41 AM
 */

namespace execut\import\components\source;


use yii\base\Component;

abstract class Adapter extends Component
{
    abstract public function getFiles();
    public function createFile($fileName) {
        $file = new File([
            'filePath' => '/tmp/' . time() . '_' . $fileName,
            'fileName' => $fileName,
        ]);
        return $file;
    }
}