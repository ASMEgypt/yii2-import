<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/22/17
 * Time: 2:15 PM
 */

namespace execut\import\example\models\queries;


use execut\import\example\models\BrandsAlias;
use execut\import\Query;
use yii\db\ActiveQuery;

class Brand extends ActiveQuery implements Query
{
    public function byImportAttributes($attributes)
    {
        return $this
            ->with([
                'aliases' => function ($q) use ($attributes) {
                    return $q->andWhere($attributes);
                },
            ])
            ->andWhere([
            'OR',
            $attributes,
            [
                'id' => BrandsAlias::find()->select('id')->andWhere($attributes),
            ]
        ]);
    }
}