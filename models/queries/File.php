<?php

namespace execut\import\models\queries;

use execut\import\models;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\mysql\Schema;
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
class File extends ActiveQuery
{
    public function isNew() {
        return $this->byImportFilesStatuseId(models\FilesStatuse::find()->isNew()->select('id'));
    }

    public function isDelete() {
        return $this->byImportFilesStatuseId(models\FilesStatuse::find()->isDelete()->select('id'));
    }

    public function isLoading() {
        return $this->byImportFilesStatuseId(models\FilesStatuse::find()->isLoading()->select('id'));
    }

    public function isReloading() {
        return $this->byImportFilesStatuseId(models\FilesStatuse::find()->isReloading()->select('id'));
    }

    public function isForClean() {
        $keys = [models\FilesStatuse::DELETE];

        return $this->byImportFilesStatuseId(models\FilesStatuse::find()->byKey($keys)->select('id'));
    }

    public function isForImport() {
        $keys = [models\FilesStatuse::RELOAD, models\FilesStatuse::NEW];

        return $this->byImportFilesStatuseId(models\FilesStatuse::find()->byKey($keys)->select('id'));
    }

    public function byMd5($md5) {
        return $this->andWhere([
            'md5' => $md5,
        ]);
    }

    public function isStop() {
        return $this->byImportFilesStatuse_Key([models\FilesStatuse::STOP]);
    }

    public function isCancelLoading() {
        return $this->byImportFilesStatuseId(models\FilesStatuse::find()->isNotLoading()->select('id'));
    }

    public function byImportFilesStatuse_Key($key) {
        return $this->andWhere([
            'import_files_statuse_id' => models\FilesStatuse::find()->byKey($key)->select('id'),
        ]);
    }

    public function byImportFilesStatuseId($id) {
        return $this->andWhere([
            'import_files_statuse_id' => $id,
        ]);
    }

    public function withErrorsPercent() {
        if ($this->select === null) {
            $this->select = ['*'];
        }

        $typeCast = $this->getTypeCastFunction();
        $this->select['errorsPercent'] = new Expression('CASE WHEN (rows_success is not null OR rows_errors is not null) AND (rows_success + rows_errors) > 0 THEN rows_errors' . $typeCast . '  / (rows_success + rows_errors) ELSE 0 END');

        return $this;
    }

    public function withProgress() {
        if ($this->select === null) {
            $this->select = ['*'];
        }

        $typeCast = $this->getTypeCastFunction();
        $this->select['progress'] = new Expression('CASE WHEN rows_count > 0 THEN (rows_success + rows_errors)' . $typeCast . ' / rows_count ELSE 0 END');

        return $this;
    }

    public function isOnlyFresh() {
        $modelClass = $this->modelClass;
        if ($modelClass::getDb()->schema instanceof Schema) {
            $subQuery = 'SELECT id FROM ' . $this->getPrimaryTableName() . ' GROUP BY import_setting_id ORDER BY import_setting_id, created DESC';
        } else {
            $subQuery = 'SELECT DISTINCT ON (import_setting_id) id FROM ' . $this->getPrimaryTableName() . ' ORDER BY import_setting_id, created DESC';
        }

        return $this->andWhere($this->getPrimaryTableName() . '.id IN (' . $subQuery . ')');
    }

    public function byEventsCount($count) {
        if ($count === '1') {
            $this->andWhere('(SELECT count(*) FROM import_settings_vs_scheduler_events WHERE import_settings_vs_scheduler_events.import_setting_id=import_files.import_setting_id) > 0');
        } else if ($count === '0') {
            $this->andWhere('(SELECT count(*) FROM import_settings_vs_scheduler_events WHERE import_settings_vs_scheduler_events.import_setting_id=import_files.import_setting_id) = 0');
        }

        return $this;
    }

    public function withEventsCount() {
        $vsSql = '(SELECT count(*) FROM import_settings_vs_scheduler_events WHERE import_settings_vs_scheduler_events.import_setting_id=import_files.import_setting_id)';
        $this->select['eventsCount'] = $vsSql;

        return $this;
    }

    public function byImportSettingId($id) {
        return $this->andWhere([
            'import_setting_id' => $id,
        ]);
    }

    public function byId($id) {
        return $this->andWhere([
            'id' => $id,
        ]);
    }

    public function byHostName($name) {
        return $this;
    }

    protected function getPhpProcessesIds() {
        exec('pidof php', $output);
        if (empty($output[0])) {
            return [];
        }

        return explode(' ', $output[0]);
    }

    public function isWithoutProcess() {
        $processesIds = $this->getPhpProcessesIds();
        return $this->andWhere([
            'NOT IN',
            'process_id',
            $processesIds
        ]);
    }

    public function isInProgress() {
        return $this->andWhere([
            'import_files_statuse_id' => models\FilesStatuse::find()->byKey([
                models\FilesStatuse::DELETING,
                models\FilesStatuse::LOADING,
                models\FilesStatuse::STOP,
            ])->select('id')
        ]);
    }

    /**
     * @return string
     */
    protected function getTypeCastFunction()
    {
        $modelClass = $this->modelClass;
        if ($modelClass::getDb()->schema instanceof Schema) {
            $typeCast = '';
        } else {
            $typeCast = '::float';
        }

        return $typeCast;
    }
}
