<?php
/**
 * User: execut
 * Date: 18.07.16
 * Time: 10:14
 */

namespace execut\import\controllers;


use execut\crudFields\fields\Field;
use execut\import\components\Importer;
use execut\import\components\parser\exception\Exception;
use execut\import\components\parser\Stack;
use execut\import\models\FilesSource;
use execut\import\models\FilesStatuse;
use execut\import\models\File;
use execut\import\models\Log;
use execut\import\models\Setting;

use yii\console\Controller;
use yii\log\Logger;
use yii\mutex\Mutex;

class ConsoleController extends Controller
{
    public $loadsLimit = 20;
    public $stackSize = 650;
    public $fileId = null;
    protected $lastCheckedRow = 0;

    public function options($actionID)
    {
        if ($actionID === 'index') {
            return [
                'fileId',
                'stackSize',
            ];
        }
        // $actionId might be used in subclasses to provide options specific to action id
        return ['color', 'interactive', 'help'];
    }

    public function actionIndex() {
        ini_set('memory_limit', -1);
        $id = $this->fileId;
        $this->clearOldFiles();
        $q = File::find();
        $this->markErrorFiles();
        if ($id === null) {
            $q->byHostName(gethostname());
            $q->isForImport()->isOnlyFresh()->orderBy('created ASC');
            $currentFilesCount = File::find()->byHostName(gethostname())->isLoading()->count();
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

    protected function deleteOldFilesBySetting($file) {
        $q = File::find()->byImportSettingId($file->import_setting_id)->andWhere([
            '<>',
            'id',
            $file->id,
        ])->select('id');
        $this->waitForRelease();
        File::updateAll([
            'import_files_statuse_id' => FilesStatuse::find()->byKey(FilesStatuse::DELETE)->one()->id,
        ], ['id' => $q->column()]);

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

    protected function markErrorFiles()
    {
        $this->waitForRelease();
        $this->stdout('Start check failed files' . "\n");
        /**
         * @var File $file
         */
        $files = File::find()->byHostName(gethostname())->isWithoutProcess()->isInProgress()->all();
        foreach ($files as $file) {
            $attributes = [
                'level' => Logger::LEVEL_ERROR,
                'category' => 'import.notFoundProcess',
                'message' => 'The process ' . $file->process_id . ' to import the file was not found',
            ];
            $file->logError($attributes);
            $file->triggerException();
            $this->stdout('File ' . $file->id . ' is marked as errored' . "\n");
        }

        $this->release();
        $this->stdout('End check failed files' . "\n");
    }

    protected function clearOldFiles()
    {
        while (true) {
            $this->waitForRelease();
            /**
             * @var File $file
             */
            $file = File::find()->byHostName(gethostname())->isForClean()->one();
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
        $data = $file->getRows();
//        $data = array_splice($data, 23400, 1000000);
        $file->scenario = 'import';
        try {
            ini_set('error_reporting', E_ERROR);
            $file->rows_count = count($data);
            $file->save();
            ini_set('error_reporting', E_ALL);
        } catch (\Exception $e) {
            $attributes = [
                'level' => Logger::LEVEL_ERROR,
                'category' => 'import.fatalError',
                'message' => $e->getMessage() . "\n" . $e->getTraceAsString(),
            ];
            $file->logError($attributes);
            $file->triggerException();
            throw $e;
        }

        $stacksSettings = $file->getSettings();
        //        \yii::$app->db->close();
        $importer = new Importer([
            'file' => $file,
            'settings' => $stacksSettings,
            'data' => $data,
            'stackSize' => $this->stackSize,
        ]);
        $importer->run();
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

        $this->stdout('Checking source type ' . $type . "\n");
        $q = Setting::find();
        if ($id !== null) {
            $q->andWhere(['id' => $id]);
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
                    if ($importFile = File::find()->byMd5($md5)->select(['id', 'updated'])->one()) {
                        $importFile->updated = date('Y-m-d H:i:s');
                        /**
                         * @var File $file
                         */
                        $importFile->save(false, [
                            'updated'
                        ]);
                        echo 'File with md5 ' . $md5 . ' is already exists' . "\n";
                    } else {
                        $importFile = new File();
                        $importFile->scenario = Field::SCENARIO_FORM;
                        $fileInfo = pathinfo($file->filePath);
                        $importFile->attributes = [
                            'name' => $file->fileName,
                            'extension' => $fileInfo['extension'],
                            'mime_type' => mime_content_type($file->filePath),
                            'import_setting_id' => $setting->id,
                            'import_files_source_id' => $setting->filesSource->id,
                            'content' => $file->content,
                        ];

                        $this->saveModel($importFile);
                    }
                }
            }
        }

        $mutex->release($mutexKey);
    }

    public function actionCheckSourceDaemon($type = 'email') {
        while (true) {
            $this->actionCheckSource($type);
            sleep(60 * 5);
        }
    }

    protected function saveModel($model) {
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