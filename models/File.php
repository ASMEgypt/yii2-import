<?php

namespace execut\import\models;

use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\fields\Date;
use execut\crudFields\fields\Field;
use execut\crudFields\fields\HasOneSelect2;
use execut\crudFields\ModelsHelperTrait;
use execut\import\components\ToArrayConverter;
use execut\crudFields\fields\File as FileField;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
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
class File extends base\File
{
    use ModelsHelperTrait, BehaviorStub;

    const MODEL_NAME = '{n,plural,=0{Files} =1{File} other{Files}}';
    public $eventsCount = null;
    protected $rows = null;
    public $contentFile = null;
    public $preview = null;
    public $progress = null;


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
                        'required' => true,
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
                        'class' => HasOneSelect2::class,
                        'attribute' => 'import_files_statuse_id',
                        'relation' => 'statuse',
                        'required' => true,
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
                        'displayOnly' => true,
                    ],
                    'end_date' => [
                        'class' => Date::class,
                        'attribute' => 'end_date',
                        'displayOnly' => true,
                    ],
                ]),
                'plugins' => \yii::$app->getModule('import')->getFilesCrudFieldsPlugins(),
            ],
            'date' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
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
        $this->deleteRelatedRecords();
        $this->save();
    }

    public function triggerErrorRow() {
        $this->rows_errors += count($this->getSettings());
        $this->triggerCompleteRow();
    }

    public function triggerException() {
        $this->triggerErrorRow();
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
        ]); // TODO: Change the autogenerated stub
    }

    protected $currentStep = 0;
    protected $saveStep = 10;
    public function triggerCompleteRow() {
        if ($this->currentStep === $this->saveStep || $this->completeRows == $this->calculatedRowsCount) {
            $this->currentStep = 0;
            $this->save(false);
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

    public function deleteLogs() {
        return Log::deleteAll(['import_file_id' => $this->id]);
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

    public function deleteRelatedRecords() {
        return \yii::$app->getModule('import')->deleteRelatedRecords($this);
    }

    public function getLogsGrouped()
    {
        $result = parent::getLogs(); // TODO: Change the autogenerated stub
        $result->modelClass = LogGrouped::className();

        return $result;
    }

    public function getLogs()
    {
        $result = parent::getLogs(); // TODO: Change the autogenerated stub
        $result->modelClass = Log::className();

        return $result;
    }

//    public function getDataProvider() {
//        $attributes = $this->attributes();
//        unset($attributes[array_search('content', $attributes)]);
//        unset($attributes[array_search('id', $attributes)]);
//        unset($attributes[array_search('created', $attributes)]);
//        unset($attributes[array_search('updated', $attributes)]);
//        unset($attributes[array_search('name', $attributes)]);
//        unset($attributes[array_search('import_files_source_id', $attributes)]);
//        unset($attributes[array_search('import_setting_id', $attributes)]);
//        unset($attributes[array_search('start_date', $attributes)]);
//        unset($attributes[array_search('end_date', $attributes)]);
//        $attributes[] = 'import_files.import_setting_id';
//        $attributes[] = 'import_files.start_date';
//        $attributes[] = 'import_files.end_date';
//        $attributes[] = 'import_files.id';
//        $attributes[] = 'import_files.created';
//        $attributes[] = 'import_files.updated';
//        $attributes[] = 'import_files.name';
//        $attributes[] = 'import_files.import_files_source_id';
//        /**
//         * @var \execut\import\models\queries\File $q
//         */
//        $q = self::find()->select($attributes)->withErrorsPercent()->withProgress();
//        $q->with([
//            'importFilesStatuse',
//            'importSetting.schedulerEvents'
//        ]);
//        $q->joinWith('importSetting');
//        $provider = new ActiveDataProvider([
//            'query' => $q,
//        ]);
//
//        $sort = $provider->sort;
//        $sort->attributes['import_setting_id'] = [
//            'asc' => ['import_settings.name' => SORT_ASC],
//            'desc' => ['import_settings.name' => SORT_DESC],
//        ];
//
//        $sort->attributes['eventsCount'] = [
//            'asc' => ['eventsCount' => SORT_ASC],
//            'desc' => ['eventsCount' => SORT_DESC],
//        ];
//
//        $sort->attributes['progress'] = [
//            'asc' => ['progress' => SORT_ASC],
//            'desc' => ['progress' => SORT_DESC],
//        ];
//
//        $sort->attributes['errorsPercent'] = [
//            'asc' => ['errorsPercent' => SORT_ASC],
//            'desc' => ['errorsPercent' => SORT_DESC],
//        ];
//
//        $vsSql = '(SELECT count(*) FROM import_settings_vs_scheduler_events WHERE import_settings_vs_scheduler_events.import_setting_id=import_files.import_setting_id)';
//
//        $q->select['eventsCount'] = $vsSql;
//
//        if ($this->eventsCount === '1') {
//            $q->andWhere('(SELECT count(*) FROM import_settings_vs_scheduler_events WHERE import_settings_vs_scheduler_events.import_setting_id=import_files.import_setting_id) > 0');
//        } else if ($this->eventsCount === '0') {
//            $q->andWhere('(SELECT count(*) FROM import_settings_vs_scheduler_events WHERE import_settings_vs_scheduler_events.import_setting_id=import_files.import_setting_id) = 0');
//        }
//
//        $equalsAttributes = ['import_setting_id', 'import_files_statuse_id'];
//        foreach ($equalsAttributes as $attribute) {
//            $q->andFilterWhere([
//                $attribute => $this->$attribute,
//            ]);
//        }
//
//        $likeAttributes = ['name', 'extension', 'mime_type', 'md5'];
//        if ($this->end_date) {
//            $parts = explode(' - ', $this->end_date);
//            if (!empty($parts[0])) {
//                $q->andFilterWhere([
//                    '>=',
//                    'end_date',
//                    $parts[0] . ' 0:00:00'
//                ]);
//            }
//
//            if (!empty($parts[1])) {
//                $q->andFilterWhere([
//                    '<=',
//                    'end_date',
//                    $parts[1] . ' 23:59:59'
//                ]);
//            }
//        }
//
//        foreach ($likeAttributes as $attribute) {
//            $q->andFilterWhere([
//                'ILIKE',
//                $attribute,
//                $this->$attribute,
//            ]);
//        }
//
//        return $provider;
//    }

//    public function attributeLabels()
//    {
//        return [
//            'import_setting_id' => 'Настройки',
//            'import_files_statuse_id' => 'Статус',
//            'name' => 'Название',
//            'extension' => 'Расширение',
//            'mime_type' => 'Тип',
//            'created' => 'Создано',
//            'updated' => 'Обновлено',
//            'rows_count' => 'Всего',
//            'rows_errors' => 'Ошибок',
//            'rows_success' => 'Успешно',
//            'start_date' => 'Начало',
//            'end_date' => 'Конец',
//            'progress' => 'Прогресс',
//            'errorsPercent' => '% ошибок',
//            'eventsCount' => 'События',
//        ];
//    }
//
//    public function getFormFields() {
//        $config = [
//            [
//                'type' => DetailView::INPUT_SELECT2,
//                'attribute' => 'import_setting_id',
//                'value' => function ($row, $detailView) {
//                    $row = $detailView->model;
//                    if ($row->import_setting_id) {
//                        return Html::a($row->importSetting->name, [
//                            '/catalog/import/import-settings/update',
//                            'id' => $row->import_setting_id,
//                        ]);
//                    }
//                },
//                'widgetOptions' => [
//                    'pluginOptions' => [
//                        'allowClear' => true,
//                    ],
//                    'data' => ArrayHelper::merge(['' => ''], ArrayHelper::map(Setting::find()->orderBy('name')->all(), 'id', 'name')),
//                ],
//                'format' => 'html',
//            ],
//            [
//                'type' => DetailView::INPUT_SELECT2,
//                'attribute' => 'import_files_statuse_id',
//                'value' => function ($row, $detailView) {
//                    $row = $detailView->model;
//                    if ($row->importFilesStatuse) {
//                        return $row->importFilesStatuse->name;
//                    }
//                },
//                'widgetOptions' => [
//                    'pluginOptions' => [
//                        'allowClear' => true,
//                    ],
//                    'data' => ArrayHelper::merge(['' => ''], ArrayHelper::map(FilesStatuse::find()->all(), 'id', 'name')),
//                ],
//                'format' => 'html',
//            ],
//            [
//                'type' => DetailView::INPUT_FILE,
//                'attribute' => 'contentFile',
//            ],
//        ];
//
//        if (!$this->isNewRecord) {
//            $config = ArrayHelper::merge($config, [
//                [
//                    'attribute' => 'name',
//                    'value' => function () {
//                        return Html::a($this->name, [
//                            'download',
//                            'id' => $this->id,
//                        ]);
//                    },
//                    'format' => 'raw',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'extension',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'mime_type',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'md5',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'created',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'updated',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'rows_count',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'rows_errors',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'rows_success',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'progress',
//                    'format' => ['percent', 2],
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'errorsPercent',
//                    'format' => ['percent', 2],
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'start_date',
//                    'displayOnly' => true,
//                ],
//                [
//                    'attribute' => 'end_date',
//                    'displayOnly' => true,
//                ],
////                [
////                    'type' => DetailView::INPUT_TEXT,
////                    'attribute' => 'preview',
////                    'value' => function ($row, $detailView) {
////                        $row = $detailView->model;
////                        if ($row->importSetting) {
////                            $data = $row->getRows();
////                            $data = array_splice($data, 0, 10);
////                            $dataProvider = new ArrayDataProvider([
////                                'allModels' => $data,
////                            ]);
////                            return GridView::widget([
////                                'dataProvider' => $dataProvider,
////                            ]);
////                        }
////                    },
////                    'format' => 'html',
////                    'displayOnly' => true,
////                ],
//            ]);
//        }
//
//        return $config;
//    }
//
//    public function getGridColumns() {
//        return [
//            'id',
//            [
//                'attribute' => 'import_setting_id',
//                'format' => 'raw',
//                'value' => function ($row) {
//                    if ($row->import_setting_id) {
//                        return Html::a($row->importSetting->name, [
//                            '/catalog/import/import-settings/update',
//                            'id' => $row->import_setting_id,
//                        ]);
//                    }
//                },
//                'filter' => Setting::find()->forSelect(),
//            ],
//            [
//                'attribute' => 'import_files_statuse_id',
//                'value' => 'importFilesStatuse.name',
//                'filter' => FilesStatuse::find()->forSelect(),
//            ],
//            [
//                'attribute' => 'name',
//                'format' => 'raw',
//                'value' => function ($row) {
//                    return Html::a($row->name, [
//                        'download',
//                        'id' => $row->id,
//                    ]);
//                }
//            ],
//            'extension',
//            'mime_type',
//            'md5',
//            'created',
//            'updated',
//            'rows_count',
//            'rows_errors',
//            'rows_success',
//            [
//                'attribute' => 'eventsCount',
//                'filter' => [
//                    '0' => '0',
//                    '1' => '>0',
//                ],
//            ],
//            [
//                'attribute' => 'progress',
//                'format' => ['percent', 2],
//            ],
//            [
//                'attribute' => 'errorsPercent',
//                'format' => ['percent', 2],
//            ],
//            'start_date',
//            [
//                'attribute' => 'end_date',
//                'filter' => DateRangePicker::widget([
//                    'attribute' => 'end_date',
//                    'model' => $this,
//                    'convertFormat'=>true,
//                    'pluginOptions'=>[
//                        'timePicker'=>true,
//                        'timePickerIncrement'=>15,
//                        'locale'=>['format'=>'Y-m-d']
//                    ]
//                ]),
//            ],
//            'eventStatus',
//            [
//                'class' => ActionColumn::className(),
//                'buttons' => [
////                    'update' => function () {},
//                    'view' => function () {},
//                    'delete' => function () {},
//                ],
//            ],
//        ];
//    }
//
//    public function search() {
//        return $this->getDataProvider();
//    }

    public function __toString()
    {
        return '#' . $this->id . ' ' . $this->name;
    }
}
