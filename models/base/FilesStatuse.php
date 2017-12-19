<?php

namespace execut\import\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_files_statuses".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property string $key
 *
 * @property \execut\import\models\File[] $importFiles
 */
class FilesStatuse extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_files_statuses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created', 'updated'], 'safe'],
            [['name', 'key'], 'required'],
            [['name', 'key'], 'string', 'max' => 255],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('execut.import.models.base.FilesStatuse', 'ID'),
            'created' => Yii::t('execut.import.models.base.FilesStatuse', 'Created'),
            'updated' => Yii::t('execut.import.models.base.FilesStatuse', 'Updated'),
            'name' => Yii::t('execut.import.models.base.FilesStatuse', 'Name'),
            'key' => Yii::t('execut.import.models.base.FilesStatuse', 'Key'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(\execut\import\models\File::className(), ['import_files_statuse_id' => 'id'])->inverseOf('filesStatuse');
    }
}
