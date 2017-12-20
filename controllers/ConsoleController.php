<?php
/**
 * User: execut
 * Date: 18.07.16
 * Time: 10:14
 */

namespace execut\import\controllers;


use execut\import\components\parser\exception\Exception;
use execut\import\components\parser\Stack;
use execut\import\models\FilesStatuse;
use execut\import\models\Log;
use execut\import\models\File;
use execut\import\models\Setting;
use execut\multiprocess\Wrapper;
use yii\console\Controller;
use yii\log\Logger;
use yii\mutex\Mutex;

class ConsoleController extends Controller
{
    public $loadsLimit = 20;
    public $checkedFileStatusRows = 1000;
    protected $lastCheckedRow = 0;
    public function actionIndex($id = null) {
        ini_set('memory_limit', -1);
        $this->clearOldFiles();
        $q = File::find()->isOnlyFresh()->orderBy('created ASC');
        if ($id === null) {
            $q->isForImport();
            $currentFilesCount = File::find()->isLoading()->count();
            if ($currentFilesCount >= $this->loadsLimit) {
                echo 'Files limit reached ' . $this->loadsLimit . '. Now loaded ' . $currentFilesCount . ' files';
                return;
            }
        } else {
            $q->byId($id);
        }

        while (true) {
            if ($id === null) {
                $this->waitForRelease();
            }

            /**
             * @var File $file
             */
            $file = $q->one();
            if ($id === null && !$file) {
                $this->release();
                break;
            }

            $file->triggerLoading();
            if ($id === null) {
                $this->release();
            }

            $this->deleteOldFilesBySetting($file);

            $this->parseFile($file);
            if ($id !== null) {
                break;
            }
        }
    }

    public function actionReleaseTrigger() {
        $this->release();
    }

    public function deleteOldFilesBySetting($file) {
        $q = File::find()->byImportSettingId($file->import_setting_id)->andWhere([
            '<>',
            'id',
            $file->id,
        ])->select('id');
        $this->waitForRelease();
        File::updateAll([
            'import_files_statuse_id' => FilesStatuse::find()->byKey(FilesStatuse::DELETE)->one()->id,
        ], ['id' => $q]);

        $c = $q->count();

        $this->release();

        while ($c) {
            $this->waitForRelease();
            $c = $q->count();
            $this->release();
            echo 'Waiting while deleted file' . "\n";
            sleep(1);
        }
    }

    protected function clearOldFiles()
    {
        while (true) {
            $this->waitForRelease();
            /**
             * @var File $file
             */
            $file = File::find()->isForClean()->one();
            if (!$file) {
                $this->release();
                break;
            }

            $file->triggerDeleting();
            $file->delete();
            $this->release();
        }
    }

    protected function parseFile(File $file) {
        $this->stdout('Start parse file #' . $file->id . ' ' . $file->name . "\n");
        $file->scenario = 'import';
        try {
            ini_set('error_reporting', E_ERROR);
            $file->rows_count = $file->calculatedSetsCount;
            $file->save();
            $data = $file->getRows();
            ini_set('error_reporting', E_ALL);
        } catch (\Exception $e) {
            $attributes = [
                'level' => Logger::LEVEL_ERROR,
                'category' => 'import.fatalError',
                'message' => $e->getMessage() . "\n" . $e->getTraceAsString(),
                'import_file_id' => $file->id,
            ];
            $this->logError($attributes);
            $file->triggerException();
            throw $e;
        }

        $stacksSettings = $file->getSettings();
        \yii::$app->db->close();
        $multiWrapper = new Wrapper([
            'threadsCount' => 1,
            'callback' => function ($row, $rowKey) use ($stacksSettings, $file) {
                try {
                    if ($this->lastCheckedRow >= $this->checkedFileStatusRows) {
                        if ($file->isStop()) {
                            $file->triggerStop();
                            return;
                        }

                        if ($file->isCancelLoading()) {
                            $this->stdout('Cancel file loading ' . $file . "\n");
                            return;
                        }

                        $this->lastCheckedRow = 0;
                    } else {
                        $this->lastCheckedRow++;
                    }

                    /**
                     * @var Stack[] $stacks
                     */
                    $stacks = [];
                    foreach ($stacksSettings as $key => $stacksSetting) {
                        $stacks[] = new Stack($stacksSetting);
                    }

                    foreach ($stacks as $stack) {
                        $stack->setRow($row);
                        $result = $stack->parse();
                        $file->triggerSuccessRow();
                    }
                } catch (Exception $e) {
                    $attributes = [
                        'level' => Logger::LEVEL_WARNING,
                        'category' => $e->getLogCategory(),
                        'message' => $e->getLogMessage(),
                        'import_file_id' => $file->id,
                        'row_nbr' => $rowKey + $file->setting->ignored_lines + 1,
                        'column_nbr' => $e->columnNbr
                    ];
                    $this->logError($attributes);
                    $file->triggerErrorRow();
                } catch (\Exception $e) {
                    $attributes = [
                        'level' => Logger::LEVEL_ERROR,
                        'category' => 'import.error',
                        'message' => $e->getMessage() . "\n" . $e->getTraceAsString(),
                        'import_file_id' => $file->id,
                        'row_nbr' => $rowKey + $file->setting->ignored_lines + 1,
                    ];
                    $this->logError($attributes);

                    $file->triggerException();
                    throw $e;
                }
            },
            'data' => $data,
        ]);
        $multiWrapper->run();

        $file->triggerLoaded();

        $this->stdout('Complete parse file #' . $file->id . ' ' . $file->name . "\n");
    }

    /**
     * @param $attributes
     */
    protected function logError($attributes)
    {
        $log = new Log($attributes);
        if (!$log->save()) {
            var_dump($log->errors);
            exit;
        }
    }

    public function actionCheckSource($type = 'email', $id = null) {
        /**
         * @var Mutex $mutex
         */
        $mutex = \yii::$app->mutex;
        $mutexKey = self::class . '_' . $type;
        while (!$mutex->acquire($mutexKey)) {
            sleep(1);
        }

        $q = Setting::find();
        if ($id !== null) {
            $q->byId($id);
        } else {
            $q->byImportFilesSource_key($type);
        }

        /**
         * @var Setting[] $importSettings
         */
        $importSettings = $q->all();
        foreach ($importSettings as $setting) {
            $source = $setting->getSource();
            $files = $source->getFiles();
            if (!empty($files)) {
                foreach ($files as $file) {
                    $md5 = md5_file($file->filePath);
                    if (File::find()->byMd5($md5)->count()) {
                        echo 'File with md5 ' . $md5 . ' is already exists' . "\n";
                    } else {
                        $importFile = new File();
                        $fileInfo = pathinfo($file->filePath);
                        $importFile->attributes = [
                            'name' => $file->fileName,
                            'extension' => $fileInfo['extension'],
                            'mime_type' => mime_content_type($file->filePath),
                            'import_setting_id' => $setting->id,
                            'content' => $file->content,
                        ];

                        $this->saveModel($importFile);
                    }
                }
            }
        }

        $mutex->release($mutexKey);
    }

    public function saveModel($model) {
        if ($model->save()) {
            $this->stdout('Model ' . $model . ' is saved' . "\n");
        } else {
            $this->stderr('Model ' . $model . ' is errors: ' . var_export($model->errors, true) . "\n");
        }
    }

    /**
     * @return Mutex
     */
    protected function waitForRelease(): Mutex
    {
        /**
         * @var Mutex $mutex
         */
        $mutex = \yii::$app->mutex;
        while (!$mutex->acquire(__CLASS__)) {
            echo 'Wait for release' . "\n";
            sleep(1);
        }
        return $mutex;
    }

    protected function release(): void
    {
        /**
         * @var Mutex $mutex
         */
        $mutex = \yii::$app->mutex;
        $mutex->release(__CLASS__);
    }
}