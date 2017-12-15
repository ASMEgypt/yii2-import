<?php

namespace execut\import\models\forms;

use execut\import\models\FilesStatuse;
use execut\import\models\Setting;
use kartik\daterange\DateRangePicker;
use kartik\grid\ActionColumn;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

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
 *
 * @property \execut\import\models\FilesSource $importFilesSource
 * @property \execut\import\models\User $use
 */
class File extends \execut\import\models\File
{
    public function rules()
    {
        return [
            [['import_setting_id', 'import_files_statuse_id', 'name', 'extension', 'mime_type', 'md5', 'eventsCount', 'end_date'], 'safe']
        ];
    }
}