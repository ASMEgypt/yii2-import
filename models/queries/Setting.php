<?php
/**
 * User: execut
 * Date: 29.07.16
 * Time: 17:06
 */

namespace execut\import\models\queries;


use yii\db\ActiveQuery;
use execut\import\models;
use yii\helpers\ArrayHelper;

class Setting extends ActiveQuery
{
    public function byImportFileId($id) {
        return $this->andWhere([
            'id' => models\File::find()->byId($id)->select('import_setting_id')
        ]);
    }

    public function forSelect() {
        return ArrayHelper::map($this->select(['id', 'name'])->asArray()->all(), 'id', 'name');
    }

    public function orderByName() {
        return $this->addOrderBy('name');
    }
}