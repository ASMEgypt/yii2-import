<?php
namespace execut\import\components\parser\exception;


abstract class Exception extends \Error
{
    public $columnNbr = null;
    abstract public function getLogMessage();
    abstract public function getLogCategory();
}