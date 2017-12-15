<?php

namespace execut\import\models\queries;

use execut\import\models;
use yii\db\ActiveQuery;
use yii\db\Expression;
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

        $this->select['errorsPercent'] = new Expression('CASE WHEN (rows_success is not null OR rows_errors is not null) AND (rows_success + rows_errors) > 0 THEN rows_errors::float / (rows_success + rows_errors) ELSE 0 END');

        return $this;
    }

    public function withProgress() {
        if ($this->select === null) {
            $this->select = ['*'];
        }

        $this->select['progress'] = new Expression('CASE WHEN rows_count > 0 THEN (rows_success + rows_errors)::float / rows_count ELSE 0 END');

        return $this;
    }

    public function isOnlyFresh() {
        $class = $this->modelClass;

        return $this->andWhere($class::tableName() . '.id IN (SELECT DISTINCT ON (import_setting_id) id FROM import_files ORDER BY import_setting_id, created DESC)');
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
}
