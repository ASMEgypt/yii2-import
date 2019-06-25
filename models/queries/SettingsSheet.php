<?php
/**
 * User: execut
 * Date: 29.07.16
 * Time: 17:06
 */

namespace execut\import\models\queries;


use yii\db\ActiveQuery;
use execut\import\models;

class SettingsSheet extends ActiveQuery
{
    public function byImportFileId($id) {
        return $this->byImportSettingId(models\Setting::find()->byImportFileId($id)->select('id'));
    }

    public function byImportSettingId($id) {
        return $this->andWhere([
            'import_setting_id' => $id,
        ]);
    }
}