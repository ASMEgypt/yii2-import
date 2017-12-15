<?php

class m160805_062054_addErrorStatus extends \execut\yii\migration\Migration
{
    public function initInverter(\execut\yii\migration\Inverter $i)
    {
        $i->table('import_files_statuses')->batchInsert([
            'id',
            'key',
            'name'
        ], [
            [
                9,
                'error',
                'Ошибка',
            ]
        ]);
    }
}
