<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 2:06 PM
 */

namespace execut\import\components;


use execut\import\components\source\Adapter;
use yii\base\Component;

class Source extends Component
{
    /**
     * @var Adapter
     */
    public $adapter = null;
    public function getFiles() {
        return $this->adapter->getFiles();
    }
}