<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/29/16
 * Time: 1:02 PM
 */

namespace execut\import\components\source\adapter;


use execut\import\components\source\Adapter;
use execut\import\components\source\File;
use GuzzleHttp\Client;

class Site extends Adapter
{
    public $site = null;
    public $authUrl = null;
    public $method = 'post';
    public $login = null;
    public $loginField = null;
    public $password = null;
    public $passwordField = null;
    public $fileUrl = null;
    public $otherFields = [];
    protected $client = null;
    const STD_HEADERS = [
        'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:48.0) Gecko/20100101 Firefox/48.0',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-US,en;q=0.5',
        'Accept-Encoding' => 'gzip, deflate',
        'Connection' => 'keep-alive',
        'Upgrade-Insecure-Requests' => '1'
    ];
    public function getFiles() {
        if ($this->authUrl) {
            $formParams = [
                $this->loginField => $this->login,
                $this->passwordField => $this->password,
            ];
            $formParams = array_merge($formParams, $this->otherFields);

            $this->doRequest($this->method, $this->authUrl, [
                'form_params' => $formParams,
                'headers' => self::STD_HEADERS,
            ]);
        }

        $cacheKey = __CLASS__ . $this->site;
//        if (!($body = \yii::$app->cache->get($cacheKey))) {
            $fileResponse = $this->doRequest('get', $this->fileUrl);
            $body = $fileResponse->getBody()->getContents();
//            \yii::$app->cache->set($cacheKey, $body);
//        }

        $fileNameParts = explode('/', $this->fileUrl);
        $fileName = $fileNameParts[count($fileNameParts) - 1];
        $file = $this->createFile($fileName);
        $file->content = $body;

        return [
            $file,
        ];
    }

    protected function doRequest($method, $url, $params = []) {
        return $this->getClient()->request($method, $url, $params);
    }

    protected function decode($content) {
        return iconv('cp1251', 'utf8', $content);
    }

    public function setClient($client) {
        $this->client = $client;

        return $this;
    }

    public function getClient() {
        if ($this->client === null) {
            $client = new Client([
                'base_uri' => $this->site,
                'cookie' => true,
                'cookies' => true,
            ]);

            $this->client = $client;
        }

        return $this->client;
    }
}