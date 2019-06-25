<?php
/**
 * User: execut
 * Date: 29.07.16
 * Time: 17:06
 */

namespace execut\import\models\queries;


use yii\db\ActiveQuery;
use execut\import\models;

class SettingsSet extends ActiveQuery
{
    public function byImportFileId($id) {
        return $this->byImportSettingsSheetId(models\SettingsSheet::find()->byImportFileId($id)->select('id'));
    }

    public function byImportSettingsSheetId($id) {
        return $this->andWhere([
            'import_settings_sheet_id' => $id,
        ]);
    }
}