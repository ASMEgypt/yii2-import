<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 2:34 PM
 */

namespace execut\import\components\source\adapter\email;


use yii\base\Component;

class Mail extends Component
{
    public $id = null;
    public $sender = null;
    public $subject = null;
    public $attachments = null;
}