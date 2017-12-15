<?php
/**
 * User: execut
 * Date: 29.07.16
 * Time: 17:06
 */

namespace execut\import\models\queries;

use execut\import\models;
use yii\db\ActiveQuery;

class SettingsValue extends ActiveQuery
{
    public function byImportFileId($id) {
        return $this->byImportSettingsSetId(models\SettingsSet::find()->byImportFileId($id)->select('id'));
    }
}