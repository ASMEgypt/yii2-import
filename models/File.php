<?php

namespace execut\import\models;

use execut\actions\action\adapter\viewRenderer\DynaGridRow;
use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\fields\Date;
use execut\crudFields\fields\Field;
use execut\crudFields\fields\HasManyMultipleInput;
use execut\crudFields\fields\HasOneSelect2;
use execut\crudFields\fields\RadiobuttonGroup;
use execut\crudFields\ModelsHelperTrait;
use execut\import\components\ToArrayConverter;
use execut\crudFields\fields\File as FileField;

use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\mysql\Schema;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "import_files".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property resource $content
 * @property string $md5
 * @property string $import_files_source_id
 * @property string $use_id
 * @property UploadedFile $contentFile
 *
 * @property \execut\import\models\FilesSource $importFilesSource
 * @property \execut\import\models\User $use
 */
class File extends base\File implements DynaGridRow
{
    use ModelsHelperTrait, BehaviorStub;

    const MODEL_NAME = '{n,plural,=0{Files} =1{File} other{Files}}';
    public $eventsCount = null;
    protected $rows = null;
    public $contentFile = null;
    public $preview = null;
    public $progress = null;

    public function setTime_complete_calculated() {
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->getBehavior('fields')->rules();
    }

    public function behaviors()
    {
        return [
            'fields' => [
                'class' => Behavior::class,
                'module' => 'import',
                'fields' => $this->getStandardFields(['visible', 'name'], [
                    'contentFile' => [
                        'class' => FileField::class,
                        'attribute' => 'contentFile',
                        'md5Attribute' => 'md5',
                        'dataAttribute' => 'content',
                        'downloadUrl' => [
                            '/import/files/download'
                        ],
                        'allowedExtensions' => $this->getAllowedExtensions(),
                    ],
                    'import_setting_id' => [
                        'class' => HasOneSelect2::class,
                        'required' => true,
                        'attribute' => 'import_setting_id',
                        'relation' => 'setting',
                        'url' => [
                            '/import/settings'
                        ],
                    ],
                    'import_files_statuse_id' => [
                        'class' => RadiobuttonGroup::class,
                        'attribute' => 'import_files_statuse_id',
                        'relation' => 'statuse',
                        'required' => true,
                        'defaultValue' => FilesStatuse::getIdByKey(FilesStatuse::NEW),
                        'data' => function () {
                            return $this->getAllowedStatusesList();
                        },
                        'rules' => [
                            'validateStatus' => ['import_files_statuse_id', 'in', 'range' => function () {
                                return array_keys($this->getAllowedStatusesList());
                            }],
                        ],
//                        'rules' => [
//                            'defaultValueOnForm' => [
//                                'import_files_statuse_id',
//                                'default',
//                                'value' => FilesStatuse::getIdByKey(FilesStatuse::NEW),
//                                'on' => Field::SCENARIO_FORM,
//                            ],
//                        ],
                    ],
                    'import_files_source_id' => [
                        'class' => HasOneSelect2::class,
                        'attribute' => 'import_files_source_id',
                        'required' => true,
                        'relation' => 'source',
//                        'defaultValue' => FilesStatuse::getIdByKey('new'),
                    ],
                    'rows_count' => [
                        'attribute' => 'rows_count',
                        'displayOnly' => true,
                    ],
                    'rows_errors' => [
                        'attribute' => 'rows_errors',
                        'displayOnly' => true,
                    ],
                    'rows_success' => [
                        'attribute' => 'rows_success',
                        'displayOnly' => true,
                    ],
                    'time_complete_calculated' => [
                        'attribute' => 'time_complete_calculated',
                        'displayOnly' => true,
                        'scope' => false,
                        'column' => [
                            'filter' => false,
//                            'value' => function () {
//                                return $this->getTime_complete_calculated();
//                            }
                        ],
                    ],
//                    'eventsCount' => [
//                        'attribute' => 'eventsCount',
//                        'column' => [
//                            'filter' => [
//                                '0' => '0',
//                                '1' => '>0',
//                            ],
//                        ],
//                        'scope' => function ($q, $model) {
//                            $q->byEventsCount($model->eventsCount)
//                                ->withEventsCount();
//                        },
//                        'displayOnly' => true,
//                    ],
                    'progress' => [
                        'attribute' => 'progress',
                        'field' => [
                            'format' => ['percent', 2],
                        ],
                        'column' => [
                            'format' => ['percent', 2],
                        ],
                        'displayOnly' => true,
                        'rules' => false,
                    ],
                    'errorsPercent' => [
                        'attribute' => 'errorsPercent',
                        'field' => [
                            'format' => ['percent', 2],
                        ],
                        'column' => [
                            'format' => ['percent', 2],
                        ],
                        'displayOnly' => true,
                        'rules' => false,
                    ],
                    'start_date' => [
                        'class' => Date::class,
                        'attribute' => 'start_date',
                        'isTime' => true,
                        'displayOnly' => true,
                    ],
                    'end_date' => [
                        'class' => Date::class,
                        'attribute' => 'end_date',
                        'isTime' => true,
                        'displayOnly' => true,
                    ],
                    'logsGrouped' => [
                        'class' => HasManyMultipleInput::class,
                        'order' => 115,
                        'attribute' => 'logsGrouped',
                        'relation' => 'logsGrouped',
                        'isGridForOldRecords' => true,
                        'scope' => false,
                        'column' => false,
                        'field' => false,
                        'gridOptions' => [
                            'responsiveWrap' => false,
                            'showPageSummary' => true,
                        ],
                    ],
                    'logs' => [
                        'class' => HasManyMultipleInput::class,
                        'order' => 115,
                        'attribute' => 'logs',
                        'relation' => 'logs',
                        'isGridForOldRecords' => true,
                        'scope' => false,
                        'column' => false,
                        'field' => false,
                        'gridOptions' => [
                            'responsiveWrap' => false,
                            'showPageSummary' => true,
                        ],
                    ],
                    'process_id' => [
                        'attribute' => 'process_id',
                        'displayOnly' => true
                    ],
                ]),
                'plugins' => \yii::$app->getModule('import')->getFilesCrudFieldsPlugins(),
            ],
            'date' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function getOldStatuse() {
        if ($attributes = $this->getDirtyAttributes(['import_files_statuse_id'])) {
            if (empty($this->oldAttributes['import_files_statuse_id'])) {
                return;
            }

            $id = $this->oldAttributes['import_files_statuse_id'];
        } else {
            $id = $this->import_files_statuse_id;
        }

        if ($id) {
            return FilesStatuse::findOne($id);
        }
    }

    public function getAllowedStatusesList() {
        $q = FilesStatuse::find();
        if ($this->scenario === 'form') {
            if ($statuse = $this->getOldStatuse()) {
                $q->isAllowedForKey($statuse->key);
            } else {
                $q->byKey(FilesStatuse::NEW);
            }
        }

        return $q->forSelect();
    }

    public function getTime_complete_calculated() {
        $secondsElapse = false;
        if (!$this->isLoading()) {
            $startTime = strtotime($this->start_date);
            $endTime = strtotime($this->end_date);

            $secondsElapse = $endTime - $startTime;
        } else {

            $startTime = strtotime($this->start_date);
            $currentTime = time();

            $totalRows = ($this->rows_success + $this->rows_errors);
            if ($totalRows > 0) {
                $secondsElapse = ($currentTime - $startTime) / $totalRows * $this->rows_count;
            }

        }

        if ($secondsElapse) {
            return date('H:i:s', $startTime + $secondsElapse) . ' (' . gmdate("H:i:s", $secondsElapse) . ')';
        }
    }

    public function search() {
        $dp = $this->getBehavior('fields')->search();
        $q = $dp->query;

        $select = $this->attributes();
        unset($select[array_search('content', $select)]);

        $sort = $dp->sort;
//        $sort->attributes['eventsCount'] = [
//            'asc' => ['eventsCount' => SORT_ASC],
//            'desc' => ['eventsCount' => SORT_DESC],
//        ];

        $sort->attributes['progress'] = [
            'asc' => ['progress' => SORT_ASC],
            'desc' => ['progress' => SORT_DESC],
        ];

        $sort->attributes['errorsPercent'] = [
            'asc' => ['errorsPercent' => SORT_ASC],
            'desc' => ['errorsPercent' => SORT_DESC],
        ];

        unset($q->select[array_search('*', $q->select)]);
        $q->select = array_merge($q->select, $select);

        return $dp;
    }


    public function beforeValidate()
    {
        if (!empty($this->contentFile)) {
            $this->name = $this->md5 = $this->extension = $this->mime_type = null;
        }

        return parent::beforeValidate(); // TODO: Change the autogenerated stub
    }

    public function isLoading() {
        return $this->import_files_statuse_id === FilesStatuse::getIdByKey(FilesStatuse::LOADING);
    }

    public function isError() {
        return $this->import_files_statuse_id === FilesStatuse::getIdByKey(FilesStatuse::ERROR);
    }

    public function isComplete() {
        return $this->import_files_statuse_id === FilesStatuse::getIdByKey(FilesStatuse::LOADED);
    }


    public function getAllowedExtensions() {
        return [
            'rar',
            'zip',
            'xls',
            'xlt',
            'xlsx',
            'xlsm',
            'xltx',
            'xltm',
            'ods',
            'ots',
            'slk',
            'xml',
            'csv',
            'txt',
            'gnumeric',
        ];
    }

    /**
     * @return \execut\import\models\queries\File
     */
    public static function find()
    {
        $q = new \execut\import\models\queries\File(__CLASS__);
        return $q->withErrorsPercent()->withProgress();
    }

    public function isStop() {
        return File::find()->isStop()->byId($this->id)->count() > 0;
    }

    public function isCancelLoading() {
        return File::find()->isCancelLoading()->byId($this->id)->count() > 0;
    }

    public function triggerStop() {
        $this->setStatus(FilesStatuse::STOPED);
        $this->save(false);
    }

    public function triggerDeleting() {
        $this->setStatus(FilesStatuse::DELETING);
        $this->save();
    }

    public function triggerLoading() {
        $this->deleteLogs();
        $this->process_id = getmypid();
        $this->start_date = date('Y-m-d H:i:s');
        $this->rows_errors = 0;
        $this->rows_success = 0;
        $this->setStatus(FilesStatuse::LOADING);
        $this->save();
    }

    public function getCalculatedSetsCount() {
        return count($this->getSettings()) * $this->getCalculatedRowsCount();
    }

    public function getErrorsPercent() {
        $percent = 0;
        if (($this->rows_success || $this->rows_errors) && ($this->rows_success + $this->rows_errors) > 0) {
            $percent = $this->rows_errors / ($this->rows_success + $this->rows_errors);
        }

        return $percent;
    }

    public function triggerLoaded() {
        $this->end_date = date('Y-m-d H:i:s');
        $this->setStatus(FilesStatuse::LOADED);
        $this->save(false, ['end_date', 'import_files_statuse_id']);
    }

    public function triggerErrorRow() {
        $this->rows_errors += count($this->getSettings());
        $this->triggerCompleteRow();
    }

    public function triggerException() {
        $this->end_date = date('Y-m-d H:i:s');
        $this->setStatus(FilesStatuse::ERROR);
        $this->save();
    }

    public function triggerSuccessRow() {
        $this->rows_success++;
        $this->triggerCompleteRow();
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            'import' => [
                'import_files_statuse_id',
                'start_date',
                'rows_count',
                'rows_errors',
                'rows_success',
            ],
        ]);
    }

    protected $currentStep = 0;
    protected $saveStep = 10;
    public function triggerCompleteRow() {
        if ($this->currentStep === $this->saveStep || $this->completeRows == $this->calculatedRowsCount) {
            $this->currentStep = 0;
            $this->save(false, ['rows_errors', 'rows_success']);
        } else {
            $this->currentStep++;
        }
    }

    public function getCompleteRows() {
        return $this->rows_errors + $this->rows_success;
    }

    public function getCalculatedRowsCount() {
        return count($this->getRows());
    }

    public function getRows() {
        if ($this->rows !== null) {
            return $this->rows;
        }

        $fileHandler = $this->content;
        $isUnlink = false;
        if (self::getDb()->schema instanceof Schema) {
            $file = tempnam(sys_get_temp_dir(), 'import_');
            file_put_contents($file, $fileHandler);
            $fileHandler = $file;
            $isUnlink = true;
        }

        $setting = $this->setting;
        $mimeType = $this->detectMimeType();
        $converter = new ToArrayConverter([
            'file' => $fileHandler,
            'trim' => '\'',
            'encoding' => $setting->filesEncoding->key,
            'mimeType' => $mimeType,
        ]);
        if (!empty($setting->csv_delimiter)) {
            $converter->delimiter = $setting->csv_delimiter;
        }

        if (!empty($setting->csv_enclosure)) {
            $converter->enclosure = $setting->csv_enclosure;
        }

        $data = $converter->convert();
        $startFrom = $setting->ignored_lines;

        $data = array_splice($data, $startFrom);
        if ($isUnlink) {
            unlink($file);
        }

        return $this->rows = $data;
    }

    protected function detectMimeType() {
        if ($this->setting->is_check_mime_type) {
            return;
        }

        $mimeType = FileHelper::getMimeTypeByExtension($this->name);

        return $mimeType;
    }

    public function isCheckExtension() {
        if (!$this->setting->is_check_mime_type) {
            return true;
        }

        if (empty($this->mime_type)) {
            return false;
        }

        $extensionsByMimeType = FileHelper::getExtensionsByMimeType($this->mime_type);

        if (!in_array($this->extension, $extensionsByMimeType, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param $attributes
     */
    public function logError($attributes)
    {
        $attributes['import_file_id'] = $this->id;
        $log = new Log($attributes);
        if (!$log->save()) {
//            var_dump($attributes);
//            exit;
        }
    }

    public function deleteLogs() {
        for ($tryCount = 0; $tryCount < 4; $tryCount++) {
            try {
                Log::deleteAll(['import_file_id' => $this->id]);

                return true;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Deadlock detected') !== false && $tryCount == 3) {
                    throw $e;
                }

                sleep(10);
            }
        }
    }

    public function checkWhatSheetsIsNotEmpty() {
        if ($this->setting) {
            if (empty($this->setting->settingsSheets)) {
                $this->addError('import_setting_id', 'You must add at least one sheet to selected setting');
            }
        }
    }

    public function beforeDelete()
    {
        $this->deleteLogs();
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    /**
     * @param $key
     */
    protected function setStatus($key)
    {
        $this->import_files_statuse_id = FilesStatuse::getIdByKey($key);
    }

    public function getSettings() {
        return $this->setting->settingsSheets[0]->getSettings();
    }

    public function getLogsGrouped()
    {
        $result = parent::getLogs(); // TODO: Change the autogenerated stub
        $result->modelClass = LogGrouped::class;

        return $result->select([
            'message',
            'category',
            'logsCount' => new Expression('count(category)'),
            ])->groupBy([
                'message',
                'category',
            ])->orderBy('logsCount DESC');
    }

    public function getLogs()
    {
        $result = parent::getLogs(); // TODO: Change the autogenerated stub
        $result->modelClass = Log::class;

        return $result;
    }

    public function __toString()
    {
        return '#' . $this->id . ' ' . $this->name;
    }
}
