<?php

namespace execut\import\models;

use execut\import\components\source\adapter\Email;
use execut\import\components\source\adapter\email\Filter;
use execut\import\components\source\adapter\email\Receiver;
use execut\import\components\source\adapter\Ftp;
use execut\import\components\source\adapter\Site;
use execut\yii\base\Exception;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_files_sources".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property string $key
 *
 * @property \execut\import\models\File[] $importFiles
 */
class FilesSource extends base\FilesSource
{
    protected static $emailAdapter = null;
    public function getAdapterForSetting(Setting $setting) {
        switch ($this->key) {
            case 'email':
                if (self::$emailAdapter === null) {
                    self::$emailAdapter = new Email([
                        'receiver' => new Receiver([
                            'imap' => \yii::$app->imap->connection,
                            //                    'searchCriteria' => 'FROM "info@avto-zapad.ru" SINCE "' . date('d F Y') . '"',
                            'filter' => new Filter(),
                        ]),
                    ]);
                }

                $adapter = self::$emailAdapter;
                $filter = $adapter->receiver->filter;
                $filter->subject = $setting->email_title_match;
                $filter->sender = $setting->email;
            break;
            case 'ftp':
                $adapter = new Ftp([
                    'host' => $setting->ftp_host,
                    'ssl' => $setting->ftp_ssl,
                    'port' => $setting->ftp_port,
                    'timeout' => $setting->ftp_timeout,
                    'login' => $setting->ftp_login,
                    'password' => $setting->ftp_password,
                    'dir' => $setting->ftp_dir,
                    'fileName' => $setting->ftp_file_name,
                ]);
            break;
            case 'site':
                $adapter = new Site([
                    'site' => $setting->site_host,
                    'authUrl' => $setting->site_auth_url,
                    'method' => $setting->site_auth_method,
                    'loginField' => $setting->site_login_field,
                    'passwordField' => $setting->site_password_field,
                    'otherFields' => $setting->siteOtherFields,
                    'login' => $setting->site_login,
                    'password' => $setting->site_password,
                    'fileUrl' => $setting->site_file_url,
                ]);
            break;
            default:
                throw new Exception('Adapter for source type ' . $this->key . ' is not supported');
        }

        return $adapter;
    }
}