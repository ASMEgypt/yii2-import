<?php
/**
 * User: execut
 * Date: 26.07.16
 * Time: 15:28
 */

namespace execut\import\components\parser\exception;


abstract class Exception extends \Error
{
    public $columnNbr = null;
    abstract public function getLogMessage();
    abstract public function getLogCategory();
}