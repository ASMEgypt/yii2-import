<?php
/**
 * User: execut
 * Date: 25.07.16
 * Time: 14:55
 */

namespace execut\import\models\forms;


use execut\yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class Dictionaries extends Model
{
    public $type = null;
    public $name = null;

    public function rules()
    {
        return [
            [['type', 'name'], 'safe'],
        ];
    }

    public function attributes()
    {
        return [
            'name',
            'type',
        ];
    }
    
    public static function primaryKey() {
        return ['id'];
    }

    public function search() {
        $types = ImportSettingsSheets::getDictionaries();
        $type = explode('.', $this->type)[0];
        /**
         * @var ActiveQuery $query
         */
        if (isset($types[$type])) {
            $query = $types[$type];
        } else {
            $query = current($types);
            $query->where('0=1');
        }

        if ($this->name) {
            $query->andWhere([
                'ILIKE',
                'name',
                $this->name
            ]);
        }

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    public function formName()
    {
        return '';
    }
}