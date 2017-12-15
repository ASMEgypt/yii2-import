<?php

namespace execut\import\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "import_files_sources".
 *
 * @property integer $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property string $key
 *
 * @property \execut\import\models\File[] $importFiles
 * @property \execut\import\models\Setting[] $importSettings
 */
class FilesSource extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'import_files_sources';
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
            'id' => Yii::t('execut.import.models.base.FilesSource', 'ID'),
            'created' => Yii::t('execut.import.models.base.FilesSource', 'Created'),
            'updated' => Yii::t('execut.import.models.base.FilesSource', 'Updated'),
            'name' => Yii::t('execut.import.models.base.FilesSource', 'Name'),
            'key' => Yii::t('execut.import.models.base.FilesSource', 'Key'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImportFiles()
    {
        return $this->hasMany(\execut\import\models\File::className(), ['import_files_source_id' => 'id'])->inverseOf('importFilesSource');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImportSettings()
    {
        return $this->hasMany(\execut\import\models\Setting::className(), ['import_files_source_id' => 'id'])->inverseOf('importFilesSource');
    }
}
