<?php
namespace execut\import\components;
use execut\import\models\File;
use execut\import\models\Log;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

use execut\import\components\parser\exception\Exception;
use execut\import\components\parser\Stack;

class Importer extends Component {
    /**
     * @var File
     */
    public $file = null;

    public $settings = null;
    public $checkedFileStatusRows = 1000;
    protected $badRows = [];
    public $data = [];
    public $currentPart = 0;
    public $stackSize = 2000;
    protected $extractors = null;
    public function isBadRow($rowNbr) {
        $currentRowNbr = $this->getCurrentStackRowNbr() + $rowNbr;

        return !empty($this->badRows['_' . $currentRowNbr]);
    }

    public function setIsBadRow($rowNbr) {
        $currentRowNbr = $this->getCurrentStackRowNbr() + $rowNbr;

        echo $currentRowNbr . ' (' . $rowNbr . ') marked is bad ' . "\n";

        $this->badRows['_' . $currentRowNbr] = true;
        return $this;
    }

    public function logError($message, $rowNbr, $model, $columnNbr = null) {
        $currentRowNbr = $this->getCurrentStackRowNbr() + $rowNbr;
        $attributes = [
            'level' => Logger::LEVEL_ERROR,
            'category' => 'import.fatalError',
            'row_nbr' => $currentRowNbr,
            'column_nbr' => $columnNbr,
            'message' => $message,
        ];

        $this->file->logError($attributes);
        if ($model instanceof ActiveRecord) {
            $modelInfo = get_class($model) . ' #' . $model->primaryKey;
        } else {
            if (is_string($model)) {
                $modelInfo = $model;
            } else {
                $modelInfo = '';
            }
        }

        echo 'Row #' . $currentRowNbr . ': ' . $message . ' ' . $modelInfo . ' ' . var_export($this->data[$currentRowNbr], true) . "\n";
    }

    public function getRows() {
        $dataParts = $this->getRowsStacks();

        return $dataParts[$this->currentPart];
    }

    public function getStacksCount() {
        return count($this->getRowsStacks());
    }

    public function run() {
        $this->file->triggerLoading();
        $extractors = $this->getExtractors();
        for ($key = 0; $key < $this->getStacksCount(); $key++) {
            $this->currentPart = $key;
            foreach ($extractors as $extractor) {
                $extractor->reset();
            }

            foreach ($extractors as $extractor) {
                if ($extractor->isImport) {
                    $models = $extractor->getModels();
                }
            }

            $this->file->rows_errors = count($this->badRows);
            $this->file->rows_success = ($this->getCurrentStackRowNbr()) + count($this->getRows()) - $this->file->rows_errors + 1;
            $this->file->save(false, ['rows_errors', 'rows_success']);
            if ($this->file->isStop()) {
                $this->file->triggerStop();
                return;
            }
        }

        foreach ($extractors as $extractor) {
            $extractor->deleteOldRecords();
        }

        $this->file->triggerLoaded();
    }

    /**
     * @return ModelsExtractor
     */
    public function getExtractor($id)
    {
        if ($this->hasExtractor($id)) {
            return $this->getExtractors()[$id];
        }
    }

    public function hasExtractor($id) {
        $extractors = $this->getExtractors();
        if (!empty($extractors[$id])) {
            return true;
        }
    }

    /**
     * @return ModelsExtractor[]
     */
    public function getExtractors()
    {
        if ($this->extractors !== null) {
            return $this->extractors;
        }

        $attributes = $this->settings;

        $extractors = [];
        foreach ($attributes as $extractorId => $params) {
            $params['id'] = $extractorId;
            $params['importer'] = $this;
            if (!empty($params['attributes']['id'])) {
                $params['attributes'] = [
                    'id' => [
                        'isFind' => true,
                        'value' => (int) $params['attributes']['id'],
                    ],
                ];
            }

            $extractor = new ModelsExtractor($params);
            if ($extractor->isDelete) {
                $extractor->deletedIds = $this->getDeletedIds();
            }

            $extractors[$extractorId] = $extractor;
        }

        return $this->extractors = $extractors;
    }

    public function getDeletedIds() {
        $ids = \yii::$app->getModule('import')->getOldIdsByFile($this->file);
        $result = [];
        foreach ($ids as $id) {
            $result[$id] = $id;
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getCurrentStackRowNbr(): int
    {
        return $this->currentPart * $this->stackSize;
    }

    /**
     * @return array
     */
    protected function getRowsStacks()
    {
        $dataParts = array_chunk($this->data, $this->stackSize);
        return $dataParts;
    }
}