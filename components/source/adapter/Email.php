<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 2:24 PM
 */

namespace execut\import\components\source\adapter;


use execut\import\components\source\Adapter;
use execut\import\components\source\adapter\email\Receiver;

class Email extends Adapter
{
    /**
     * @var Receiver
     */
    public $receiver = null;
    public function getFiles() {
        $mails = $this->receiver->getMails();
        $files = [];
        foreach ($mails as $mail) {
            $files = array_merge($files, $mail->attachments);
        }

        return $files;
    }
}