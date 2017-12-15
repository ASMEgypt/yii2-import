<?php

namespace execut\import\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_files_encodings".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property string $key
 *
 * @property \execut\import\models\Setting[] $importSettings
 */
class FilesEncoding extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_files_encodings';
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
            'id' => Yii::t('execut.import.models.base.FilesEncoding', 'ID'),
            'created' => Yii::t('execut.import.models.base.FilesEncoding', 'Created'),
            'updated' => Yii::t('execut.import.models.base.FilesEncoding', 'Updated'),
            'name' => Yii::t('execut.import.models.base.FilesEncoding', 'Name'),
            'key' => Yii::t('execut.import.models.base.FilesEncoding', 'Key'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImportSettings()
    {
        return $this->hasMany(\execut\import\models\Setting::className(), ['import_files_encoding_id' => 'id'])->inverseOf('importFilesEncoding');
    }
}
