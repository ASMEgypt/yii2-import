<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/28/16
 * Time: 4:45 PM
 */

namespace execut\import\components\source\adapter;


use execut\import\components\source\Adapter;
use execut\import\components\source\File;
use yii\base\Component;
use yii2mod\ftp\FtpClient;

class Ftp extends Adapter
{
    public $host = null;
    public $ssl = false;
    public $port = 21;
    public $timeout = 90;
    public $login = null;
    public $password = null;
    public $dir = null;
    public $fileName = null;
    protected $client = null;
    public function getFiles() {
        $client = $this->getClient();
        $file = $this->createFile($this->fileName);

        $client->connect($this->host, $this->ssl, $this->port, $this->timeout);
        $client->login($this->login, $this->password);
        $client->raw('OPTS UTF8 ON');
        $client->pasv(true);
        $client->get($file->filePath, trim($this->dir, '/') . '/' . $this->fileName, FTP_BINARY);

        return [
            $file,
        ];
    }

    public function setClient($client) {
        $this->client = $client;
        return $this;
    }

    public function getClient() {
        if ($this->client !== null) {
            return $this->client;
        }

        $client = new FtpClient();

        return $this->client = $client;
    }
}