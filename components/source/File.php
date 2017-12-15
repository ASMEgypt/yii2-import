<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 2:36 PM
 */

namespace execut\import\components\source;


use yii\base\Component;

class File extends Component
{
    public $fileName = null;
    public $filePath = null;
    protected $content = null;
    public function getContent() {
        if ($this->filePath !== null && $this->content === null) {
            return file_get_contents($this->filePath);
        }

        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
        if ($this->filePath !== null) {
            file_put_contents($this->filePath, $this->content);
        }
    }

    public function __destruct()
    {
        if ($this->filePath !== null && file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}